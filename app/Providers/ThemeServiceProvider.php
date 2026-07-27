<?php

namespace App\Providers;

use App\Services\ThemeService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register ThemeService as singleton so it's only instantiated once per request
        $this->app->singleton(ThemeService::class, function ($app) {
            return new ThemeService();
        });
    }

    public function boot(): void
    {
        $theme = $this->app->make(ThemeService::class);
        $themeSlug = $theme->slug();

        // Override database path for the active theme based on master state file.
        // Skipped in the testing environment so tests use the phpunit.xml database
        // (in-memory SQLite) instead of the active site's database file.
        $stateFile = storage_path('app/theme.json');
        if (!$this->app->environment('testing') && file_exists($stateFile)) {
            $state = json_decode(file_get_contents($stateFile), true);
            if (isset($state['db_database'])) {
                $dbPath = base_path($state['db_database']);
                config(['database.connections.sqlite.database' => $dbPath]);

                // Purge existing connection and reconnect with new database
                try {
                    $this->app['db']->purge('sqlite');
                } catch (\Throwable $e) {
                    // Connection may not have been established yet
                }
            }
        }

        // Share the theme service with all views as $theme
        View::share('theme', $theme);

        // Register theme-specific view namespace with fallback
        // Views can use theme::partial._hero to get a theme-specific version
        $themeViewPath = base_path("sites/{$themeSlug}/views");
        if (is_dir($themeViewPath)) {
            View::addNamespace('theme', $themeViewPath);
        } else {
            Log::warning("Theme view directory not found: {$themeViewPath}");
        }

        // Shared namespace for theme partials
        $themePartialsPath = base_path("sites/{$themeSlug}/views/partials");
        if (is_dir($themePartialsPath)) {
            View::addNamespace('theme_partials', $themePartialsPath);
        }

        // Publish site CSS to public directory for web access
        $siteCssSource = base_path("sites/{$themeSlug}/css/site.css");
        $siteCssTarget = public_path("css/sites/{$themeSlug}.css");
        if (file_exists($siteCssSource)) {
            $targetDir = dirname($siteCssTarget);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            if (!file_exists($siteCssTarget) || filemtime($siteCssSource) > filemtime($siteCssTarget)) {
                copy($siteCssSource, $siteCssTarget);
            }
        }
    }
}
