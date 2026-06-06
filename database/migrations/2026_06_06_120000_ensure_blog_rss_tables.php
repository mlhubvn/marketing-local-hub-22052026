<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('blog_rss_sources')) {
            Schema::create('blog_rss_sources', function (Blueprint $table): void {
                $table->id();
                $table->string('id_secure', 64);
                $table->string('name', 255);
                $table->text('feed_url');
                $table->unsignedBigInteger('blog_category_id')->nullable();
                $table->json('tag_ids')->nullable();
                $table->boolean('status')->default(1);
                $table->boolean('auto_publish')->default(1);
                $table->boolean('ai_improve')->default(0);
                $table->boolean('ai_auto_translate')->default(0);
                $table->text('ai_prompt')->nullable();
                $table->unsignedInteger('sync_interval_minutes')->default(60);
                $table->unsignedInteger('max_items_per_run')->default(5);
                $table->unsignedBigInteger('last_checked_at')->nullable();
                $table->unsignedBigInteger('last_imported_at')->nullable();
                $table->text('last_error')->nullable();
                $table->unsignedBigInteger('changed')->default(0);
                $table->unsignedBigInteger('created')->default(0);
                $table->unique('id_secure', 'blog_rss_sources_id_secure_unique');
                $table->index('blog_category_id', 'blog_rss_sources_blog_category_id_foreign');
            });
        }

        if (! Schema::hasTable('blog_rss_imports')) {
            Schema::create('blog_rss_imports', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('blog_rss_source_id');
                $table->unsignedBigInteger('blog_id')->nullable();
                $table->string('external_guid', 255)->nullable();
                $table->text('external_url')->nullable();
                $table->string('content_hash', 64);
                $table->string('title', 255)->nullable();
                $table->unsignedBigInteger('source_published_at')->nullable();
                $table->unsignedBigInteger('changed')->default(0);
                $table->unsignedBigInteger('created')->default(0);
                $table->unique(['blog_rss_source_id', 'content_hash'], 'blog_rss_imports_blog_rss_source_id_content_hash_unique');
                $table->index('blog_id', 'blog_rss_imports_blog_id_foreign');
            });
        }

        $migration = $this;

        if (Schema::hasTable('blog_rss_sources') && Schema::hasTable('blog_categories')) {
            Schema::table('blog_rss_sources', function (Blueprint $table) use ($migration): void {
                if (! $migration->foreignKeyExists('blog_rss_sources', 'blog_rss_sources_blog_category_id_foreign')) {
                    $table->foreign('blog_category_id', 'blog_rss_sources_blog_category_id_foreign')
                        ->references('id')
                        ->on('blog_categories')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('blog_rss_imports') && Schema::hasTable('blogs') && Schema::hasTable('blog_rss_sources')) {
            Schema::table('blog_rss_imports', function (Blueprint $table) use ($migration): void {
                if (! $migration->foreignKeyExists('blog_rss_imports', 'blog_rss_imports_blog_id_foreign')) {
                    $table->foreign('blog_id', 'blog_rss_imports_blog_id_foreign')
                        ->references('id')
                        ->on('blogs')
                        ->nullOnDelete();
                }

                if (! $migration->foreignKeyExists('blog_rss_imports', 'blog_rss_imports_blog_rss_source_id_foreign')) {
                    $table->foreign('blog_rss_source_id', 'blog_rss_imports_blog_rss_source_id_foreign')
                        ->references('id')
                        ->on('blog_rss_sources')
                        ->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('blog_rss_imports');
        Schema::dropIfExists('blog_rss_sources');

        Schema::enableForeignKeyConstraints();
    }

    protected function foreignKeyExists(string $table, string $foreignKeyName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$database, $table, $foreignKeyName, 'FOREIGN KEY']
        );

        return $result !== null;
    }
};
