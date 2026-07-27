<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ProductSchemaService;
use App\Services\SiteRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ThemeController extends Controller
{
    public function __construct(
        protected SiteRegistry $siteRegistry,
        protected ProductSchemaService $schemaService
    ) {}

    public function index()
    {
        Setting::ensureTable();

        $themes = config('themes.themes', []);

        // Read active theme from master state file first
        $activeTheme = config('themes.active', 'badlab');
        $stateFile = storage_path('app/theme.json');
        if (file_exists($stateFile)) {
            $state = json_decode(file_get_contents($stateFile), true);
            if (isset($state['active_theme']) && array_key_exists($state['active_theme'], $themes)) {
                $activeTheme = $state['active_theme'];
            }
        } else {
            $dbTheme = Setting::get('active_theme');
            if ($dbTheme && array_key_exists($dbTheme, $themes)) {
                $activeTheme = $dbTheme;
            }
        }

        // Gather site directory info
        $siteInfo = [];
        foreach ($themes as $slug => $config) {
            $siteInfo[$slug] = [
                'directory' => base_path("sites/{$slug}"),
                'database' => $config['database'] ?? "sites/{$slug}/database/site.sqlite",
                'view_count' => $this->siteRegistry->getViewCount($slug),
                'has_css' => file_exists(base_path("sites/{$slug}/css/site.css")),
                'has_env' => file_exists(base_path("sites/{$slug}/.env")),
                'has_seeders' => is_dir(base_path("sites/{$slug}/seeders")),
            ];
        }

        return view('superadmin.sites.index', compact('themes', 'activeTheme', 'siteInfo'));
    }

    public function activate(string $slug)
    {
        $themes = config('themes.themes', []);

        if (!array_key_exists($slug, $themes)) {
            return back()->with('error', 'Site not found.');
        }

        // Determine database path for this theme from config
        $dbDatabase = $themes[$slug]['database'] ?? "database/{$slug}.sqlite";
        $dbPath = database_path(basename($dbDatabase));

        // 1. Write master state file (source of truth before DB is available)
        $stateFile = storage_path('app/theme.json');
        file_put_contents($stateFile, json_encode([
            'active_theme' => $slug,
            'db_database' => $dbDatabase,
        ], JSON_PRETTY_PRINT));

        // 2. Reconnect to the new database
        config(['database.connections.sqlite.database' => $dbPath]);
        DB::purge('sqlite');

        // 3. Ensure settings table exists in the new database
        Setting::resetTableCheck();
        Setting::ensureTable();

        // 4. Also write to DB for consistency
        Setting::set('active_theme', $slug);

        // 5. Sync the site's product attribute schema into its database
        try {
            $this->schemaService->syncSchemaToDatabase($slug);
        } catch (\Throwable $e) {
            Log::error("Schema sync failed for site '{$slug}': " . $e->getMessage());
        }

        // 6. Clear caches
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return back()->with('success', "Site activated: {$themes[$slug]['name']} (DB: {$dbDatabase})");
    }

    public function edit(string $slug)
    {
        Setting::ensureTable();

        $themes = config('themes.themes', []);
        $theme = $themes[$slug] ?? null;

        if (!$theme) {
            return back()->with('error', 'Site not found.');
        }

        // Check for DB overrides
        $overrides = [];
        foreach ($theme as $key => $value) {
            $dbValue = Setting::get("theme.{$slug}.{$key}");
            if ($dbValue !== null) {
                $overrides[$key] = $dbValue;
            }
        }

        return view('superadmin.sites.edit', compact('slug', 'theme', 'overrides'));
    }

    public function update(Request $request, string $slug)
    {
        Setting::ensureTable();

        $themes = config('themes.themes', []);

        if (!array_key_exists($slug, $themes)) {
            return back()->with('error', 'Site not found.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'tagline' => 'sometimes|string|max:255',
            'colors.primary' => 'sometimes|string',
            'colors.accent' => 'sometimes|string',
            'colors.secondary' => 'sometimes|string',
            'colors.bg_primary' => 'sometimes|string',
            'colors.bg_secondary' => 'sometimes|string',
        ]);

        foreach ($validated as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    Setting::set("theme.{$slug}.{$key}.{$subKey}", $subValue);
                }
            } else {
                Setting::set("theme.{$slug}.{$key}", $value);
            }
        }

        Artisan::call('config:clear');
        Artisan::call('view:clear');

        return back()->with('success', 'Site settings saved.');
    }
}
