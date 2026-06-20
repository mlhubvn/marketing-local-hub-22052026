<?php

namespace Modules\AdminBlogs\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\AdminBlogs\Models\BlogRssSource;
use Modules\AdminBlogs\Support\RssImportService;

class ImportRssBlogsCommand extends Command
{
    protected $signature = 'blogs:rss-import {--source=} {--force}';

    protected $description = 'Import blog posts from configured RSS sources';

    public function handle(RssImportService $service): int
    {
        if (! Schema::hasTable('migrations')) {
            Log::warning('blogs:rss-import skipped', [
                'reason' => 'migrations_table_not_ready',
                'has_migrations_table' => false,
            ]);

            return self::SUCCESS;
        }

        $hasBlogRssSources = Schema::hasTable('blog_rss_sources');
        $hasBlogRssImports = Schema::hasTable('blog_rss_imports');
        $ensureMigrationRan = DB::table('migrations')
            ->where('migration', '2026_06_06_120000_ensure_blog_rss_tables')
            ->exists();
        $repairMigrationRan = DB::table('migrations')
            ->where('migration', '2026_06_08_160000_repair_missing_blog_rss_tables')
            ->exists();

        if (! $hasBlogRssSources) {
            $diagnostics = [
                'hasBlogRssSources' => $hasBlogRssSources,
                'hasBlogRssImports' => $hasBlogRssImports,
                'ensureMigrationRan' => $ensureMigrationRan,
                'repairMigrationRan' => $repairMigrationRan,
                'appEnv' => config('app.env'),
            ];

            $this->warn('Table blog_rss_sources is missing — run php artisan migrate --force then retry.');
            Log::warning('blogs:rss-import skipped: blog_rss_sources table missing', $diagnostics);

            return self::SUCCESS;
        }

        $query = BlogRssSource::query()->where('status', true);

        if ($source = $this->option('source')) {
            $query->where(function ($builder) use ($source): void {
                $builder->where('id', $source)->orWhere('id_secure', $source);
            });
        }

        $sources = $query->get();

        if ($sources->isEmpty()) {
            $this->warn('No active RSS sources found.');

            return self::SUCCESS;
        }

        foreach ($sources as $rssSource) {
            $result = $service->importSource($rssSource, (bool) $this->option('force'));
            $this->line(sprintf(
                '%s: created %d, skipped %d. %s',
                $rssSource->name,
                $result['created'] ?? 0,
                $result['skipped'] ?? 0,
                $result['message'] ?? ''
            ));
        }

        return self::SUCCESS;
    }
}
