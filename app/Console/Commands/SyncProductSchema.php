<?php

namespace App\Console\Commands;

use App\Services\ProductSchemaService;
use Illuminate\Console\Command;

class SyncProductSchema extends Command
{
    protected $signature = 'schema:sync {site?}';

    protected $description = 'Sync product attribute definitions from site config into the database';

    public function handle(ProductSchemaService $schemaService): int
    {
        $site = $this->argument('site');

        if ($site) {
            $this->info("Syncing schema for site: {$site}");
            $schemaService->syncSchemaToDatabase($site);
            $this->info('Done.');
            return self::SUCCESS;
        }

        // Sync all sites
        $sites = glob(base_path('sites/*/site.php'));

        foreach ($sites as $path) {
            $slug = basename(dirname($path));

            if ($slug === '_template') {
                continue;
            }

            $this->info("Syncing schema for: {$slug}");
            $schemaService->syncSchemaToDatabase($slug);
        }

        $this->info('All sites synced.');

        return self::SUCCESS;
    }
}
