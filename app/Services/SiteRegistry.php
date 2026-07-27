<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SiteRegistry
{
    protected array $sites = [];
    protected string $sitesDir;

    public function __construct()
    {
        $this->sitesDir = base_path('sites');
        $this->loadSites();
    }

    /**
     * Scan sites/ directory and load all site configs.
     */
    protected function loadSites(): void
    {
        if (!is_dir($this->sitesDir)) {
            return;
        }

        foreach (glob("{$this->sitesDir}/*/site.php") as $configFile) {
            $slug = basename(dirname($configFile));
            if ($slug === '_template') {
                continue;
            }
            $this->sites[$slug] = require $configFile;
        }
    }

    /**
     * Get all available site slugs.
     */
    public function availableSites(): array
    {
        return array_keys($this->sites);
    }

    /**
     * Get config for a specific site.
     */
    public function getSiteConfig(string $slug): ?array
    {
        return $this->sites[$slug] ?? null;
    }

    /**
     * Get the views directory path for a site.
     */
    public function getViewPath(string $slug): string
    {
        return "{$this->sitesDir}/{$slug}/views";
    }

    /**
     * Get the database file path for a site.
     */
    public function getDatabasePath(string $slug): string
    {
        $config = $this->getSiteConfig($slug);
        $db = $config['database'] ?? "sites/{$slug}/database/site.sqlite";
        return base_path($db);
    }

    /**
     * Get the CSS directory path for a site.
     */
    public function getCssPath(string $slug): string
    {
        return "{$this->sitesDir}/{$slug}/css";
    }

    /**
     * Get the seeders directory path for a site.
     */
    public function getSeedersPath(string $slug): string
    {
        return "{$this->sitesDir}/{$slug}/seeders";
    }

    /**
     * Get the .env file path for a site.
     */
    public function getEnvPath(string $slug): string
    {
        return "{$this->sitesDir}/{$slug}/.env";
    }

    /**
     * Check if a site directory exists.
     */
    public function siteExists(string $slug): bool
    {
        return is_dir("{$this->sitesDir}/{$slug}") && $slug !== '_template';
    }

    /**
     * Get all site configs (for superadmin listing).
     */
    public function allSites(): array
    {
        return $this->sites;
    }

    /**
     * Get the template directory path.
     */
    public function getTemplatePath(): string
    {
        return "{$this->sitesDir}/_template";
    }

    /**
     * Count files in a site's views directory.
     */
    public function getViewCount(string $slug): int
    {
        $path = $this->getViewPath($slug);
        if (!is_dir($path)) {
            return 0;
        }
        return count(glob("{$path}/partials/*.blade.php") ?: []);
    }
}
