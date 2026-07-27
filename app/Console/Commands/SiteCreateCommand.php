<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SiteCreateCommand extends Command
{
    protected $signature = 'site:create {slug : The unique slug for the new site (e.g., goodvirtue)}';
    protected $description = 'Create a new site from the template scaffold';

    public function handle(): int
    {
        $slug = strtolower(trim($this->argument('slug')));

        // Validate slug (alphanumeric + hyphens only)
        if (!preg_match('/^[a-z][a-z0-9-]*$/', $slug)) {
            $this->error('Slug must start with a lowercase letter and contain only lowercase letters, numbers, and hyphens.');
            return 1;
        }

        $sitesDir = base_path('sites');
        $siteDir = "{$sitesDir}/{$slug}";
        $templateDir = "{$sitesDir}/_template";

        // Check template exists
        if (!is_dir($templateDir)) {
            $this->error('Template directory not found: sites/_template/');
            return 1;
        }

        // Check site doesn't already exist
        if (is_dir($siteDir)) {
            $this->error("Site directory already exists: sites/{$slug}/");
            return 1;
        }

        // Prompt for site details
        $name = $this->ask('Site name', ucfirst($slug));
        $tagline = $this->ask('Tagline', "Welcome to {$name}");
        $primaryColor = $this->ask('Primary color (hex)', '#000000');
        $accentColor = $this->ask('Accent color (hex)', $primaryColor);
        $bgColor = $this->ask('Background color (hex)', '#ffffff');
        $email = $this->ask('Contact email', "info@{$slug}.com");

        $this->info("Creating site: {$name} ({$slug})...");

        // Copy template directory
        $this->copyDirectory($templateDir, $siteDir);

        // Update site.php with provided values
        $siteConfigPath = "{$siteDir}/site.php";
        $content = file_get_contents($siteConfigPath);
        $content = str_replace('{SITE_NAME}', $name, $content);
        $content = str_replace('{slug}', $slug, $content);
        $content = str_replace("'#000000'", "'{$primaryColor}'", $content); // primary
        $content = str_replace("'#ffffff'", "'{$bgColor}'", $content); // bg
        $content = str_replace('Your tagline here', $tagline, $content);
        $content = str_replace('info@example.com', $email, $content);
        file_put_contents($siteConfigPath, $content);

        // Update .env
        $envPath = "{$siteDir}/.env";
        $envContent = file_get_contents($envPath);
        $envContent = str_replace('{SITE_NAME}', $name, $envContent);
        $envContent = str_replace('{slug}', $slug, $envContent);
        file_put_contents($envPath, $envContent);

        // Create fresh SQLite database
        $dbDir = "{$siteDir}/database";
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        $dbPath = "{$dbDir}/site.sqlite";
        touch($dbPath);

        $this->info("Site directory created at: sites/{$slug}/");
        $this->info("Database created at: sites/{$slug}/database/site.sqlite");

        // Ask if user wants to run migrations
        if ($this->confirm('Run migrations on the new database?', true)) {
            $this->info('Running migrations...');

            // Temporarily switch DB connection
            $originalDb = config('database.connections.sqlite.database');
            config(['database.connections.sqlite.database' => $dbPath]);
            DB::purge('sqlite');

            try {
                Artisan::call('migrate', ['--force' => true], $this->output);
                $this->info('Migrations complete.');
            } catch (\Throwable $e) {
                $this->error("Migration failed: {$e->getMessage()}");
            }

            // Restore original DB connection
            config(['database.connections.sqlite.database' => $originalDb]);
            DB::purge('sqlite');
        }

        // Ask if user wants to seed admin
        if ($this->confirm('Seed admin user? (admin@example.com / password)', true)) {
            $originalDb = config('database.connections.sqlite.database');
            config(['database.connections.sqlite.database' => $dbPath]);
            DB::purge('sqlite');

            try {
                Artisan::call('db:seed', ['--class' => 'AdminUserSeeder', '--force' => true], $this->output);
                $this->info('Admin user seeded.');
            } catch (\Throwable $e) {
                $this->error("Seeding failed: {$e->getMessage()}");
            }

            config(['database.connections.sqlite.database' => $originalDb]);
            DB::purge('sqlite');
        }

        // Clear config cache so new site is discovered
        Artisan::call('config:clear');

        $this->newLine();
        $this->info('═══════════════════════════════════════════');
        $this->info(" Site '{$name}' created successfully!");
        $this->info('═══════════════════════════════════════════');
        $this->newLine();
        $this->line(" Directory: sites/{$slug}/");
        $this->line(" Config:    sites/{$slug}/site.php");
        $this->line(" Database:  sites/{$slug}/database/site.sqlite");
        $this->line(" Env:       sites/{$slug}/.env");
        $this->newLine();
        $this->line('To activate this site:');
        $this->line("  1. Visit /superadmin/sites and click 'Activate' on {$name}");
        $this->line("  2. Or copy sites/{$slug}/.env to .env and run: php artisan config:clear");
        $this->newLine();

        return 0;
    }

    /**
     * Recursively copy a directory.
     */
    protected function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $items = scandir($source);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $srcPath = "{$source}/{$item}";
            $dstPath = "{$destination}/{$item}";

            if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }
    }
}
