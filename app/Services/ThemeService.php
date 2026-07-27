<?php

namespace App\Services;

use Illuminate\Support\Arr;

class ThemeService
{
    protected string $activeTheme;
    protected array $config;

    public function __construct()
    {
        $this->activeTheme = $this->resolveActiveTheme();
        $this->config = config("themes.themes.{$this->activeTheme}", []);
    }

    /**
     * Resolve the active theme using file-based master state first,
     * then DB setting fallback, then config/env fallback.
     */
    protected function resolveActiveTheme(): string
    {
        // 1. Check file-based master state (works before DB is available)
        $stateFile = storage_path('app/theme.json');
        if (file_exists($stateFile)) {
            $state = json_decode(file_get_contents($stateFile), true);
            if (isset($state['active_theme']) && array_key_exists($state['active_theme'], config('themes.themes', []))) {
                return $state['active_theme'];
            }
        }

        // 2. Check database setting (fallback)
        try {
            $dbTheme = \App\Models\Setting::get('active_theme');
            if ($dbTheme && array_key_exists($dbTheme, config('themes.themes', []))) {
                return $dbTheme;
            }
        } catch (\Throwable $e) {
            // DB not available
        }

        // 3. Fall back to config/env
        return config('themes.active', 'badlab');
    }

    /**
     * Get the active theme slug (static helper).
     * Also uses file-based master state first.
     */
    public static function getActiveThemeSlug(): string
    {
        // 1. Check file-based master state
        $stateFile = storage_path('app/theme.json');
        if (file_exists($stateFile)) {
            $state = json_decode(file_get_contents($stateFile), true);
            if (isset($state['active_theme']) && array_key_exists($state['active_theme'], config('themes.themes', []))) {
                return $state['active_theme'];
            }
        }

        // 2. Check database setting
        try {
            $dbTheme = \App\Models\Setting::get('active_theme');
            if ($dbTheme && array_key_exists($dbTheme, config('themes.themes', []))) {
                return $dbTheme;
            }
        } catch (\Throwable $e) {
            // Database not available yet
        }

        return config('themes.active', 'badlab');
    }

    /**
     * Get the database path for the active theme from master state or config.
     */
    public function databasePath(): string
    {
        // Check master state file
        $stateFile = storage_path('app/theme.json');
        if (file_exists($stateFile)) {
            $state = json_decode(file_get_contents($stateFile), true);
            if (isset($state['db_database'])) {
                return base_path($state['db_database']);
            }
        }

        // Fall back to theme config
        $db = $this->get('database', "database/{$this->activeTheme}.sqlite");
        return base_path($db);
    }

    /**
     * Get a theme config value using dot notation.
     * Example: theme('colors.primary') returns '#c8a45c'
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }

    /** Get the active theme slug */
    public function slug(): string
    {
        return $this->activeTheme;
    }

    /** Get all colors for the active theme */
    public function colors(): array
    {
        return $this->get('colors', []);
    }

    /** Get font configuration */
    public function fonts(): array
    {
        return $this->get('fonts', []);
    }

    /** Check if a specific theme is active */
    public function isActive(string $theme): bool
    {
        return $this->activeTheme === $theme;
    }

    /** Check if a feature is enabled for the active theme */
    public function hasFeature(string $feature): bool
    {
        return (bool) $this->get("features.{$feature}", false);
    }

    /** Get theme-specific asset path */
    public function asset(string $path): string
    {
        return asset($path);
    }

    /** Get theme images base path */
    public function imagePath(string $file = ''): string
    {
        $base = $this->get('images_path', 'images');
        return $file ? "{$base}/{$file}" : $base;
    }

    /** Get all homepage section identifiers in order */
    public function homepageSections(): array
    {
        return $this->get('homepage_sections', []);
    }

    /** Get the body CSS class for the active theme */
    public function bodyClass(): string
    {
        return $this->get('body_class', 'bg-white text-gray-900');
    }

    /** Get currency symbol */
    public function currency(): string
    {
        return $this->get('currency.symbol', '৳');
    }

    /** Get all config for the active theme */
    public function all(): array
    {
        return $this->config;
    }
}
