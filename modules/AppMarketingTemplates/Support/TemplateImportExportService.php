<?php

namespace Modules\AppMarketingTemplates\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\AdminUser\Models\User;
use Modules\AppMarketingTemplates\Models\MarketingTemplate;

class TemplateImportExportService
{
    /**
     * @return array<string, mixed>
     */
    public function exportPayload(MarketingTemplate $template): array
    {
        return [
            'format' => 'mlhub-template',
            'version' => (string) ($template->version ?: '1.0.0'),
            'exported_at' => now()->toIso8601String(),
            'name' => $template->name,
            'slug' => $template->slug,
            'type' => $template->type,
            'category' => $template->category,
            'goal' => $template->goal,
            'description' => $template->description,
            'icon' => $template->icon,
            'settings' => $template->settings ?: [],
            'content' => $template->content ?: [],
            'design' => $template->design ?: [],
            'builder_schema' => $template->builder_schema ?: [],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $typeOptions
     * @param  callable(string): string  $uniqueSlug
     * @return array{imported:int, failed:int, pack_id:int|null}
     */
    public function import(array $payload, User $user, array $typeOptions, callable $uniqueSlug): array
    {
        $templates = isset($payload['templates']) && is_array($payload['templates'])
            ? $payload['templates']
            : [$payload];

        $imported = 0;
        $errors = [];
        $createdTemplateIds = [];

        foreach ($templates as $index => $item) {
            if (! is_array($item) || empty($item['name']) || empty($item['type'])) {
                $errors[] = __('Template row :row is missing name or type.', ['row' => $index + 1]);

                continue;
            }

            if (! array_key_exists((string) $item['type'], $typeOptions)) {
                $errors[] = __('Template row :row has an unsupported type.', ['row' => $index + 1]);

                continue;
            }

            $template = MarketingTemplate::query()->create([
                'user_id' => $user->id,
                'created_by' => $user->id,
                'name' => (string) $item['name'],
                'slug' => $uniqueSlug((string) ($item['slug'] ?? $item['name'])),
                'type' => (string) $item['type'],
                'category' => (string) ($item['category'] ?? 'general'),
                'goal' => (string) ($item['goal'] ?? 'lead'),
                'description' => (string) ($item['description'] ?? ''),
                'icon' => (string) ($item['icon'] ?? 'fa-light fa-grid-2'),
                'is_system' => false,
                'source' => 'imported',
                'visibility' => 'private',
                'status' => 'draft',
                'version' => (string) ($item['version'] ?? '1.0.0'),
                'usage_count' => 0,
                'settings' => is_array($item['settings'] ?? null) ? $item['settings'] : [],
                'content' => is_array($item['content'] ?? null) ? $item['content'] : [],
                'design' => is_array($item['design'] ?? null) ? $item['design'] : [],
                'builder_schema' => is_array($item['builder_schema'] ?? null) ? $item['builder_schema'] : [],
            ]);

            $createdTemplateIds[] = $template->id;
            $imported++;
        }

        $packId = $this->createImportedPackIfNeeded($payload, $createdTemplateIds, $user);

        if (Schema::hasTable('lb_template_imports')) {
            if (! Schema::hasColumn('lb_template_imports', 'user_id')) {
                throw new \RuntimeException(
                    'Template imports require the user ownership migration.'
                );
            }

            $importLog = [
                'team_id' => null,
                'file_name' => (string) ($payload['name'] ?? 'template-import.json'),
                'status' => $errors === [] ? 'completed' : 'completed_with_errors',
                'imported_count' => $imported,
                'failed_count' => count($errors),
                'error_log' => $errors === [] ? null : json_encode($errors),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $importLog['user_id'] = $user->id;

            DB::table('lb_template_imports')->insert($importLog);
        }

        return ['imported' => $imported, 'failed' => count($errors), 'pack_id' => $packId];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int>  $templateIds
     */
    protected function createImportedPackIfNeeded(array $payload, array $templateIds, User $user): ?int
    {
        if (
            $templateIds === []
            || ! isset($payload['templates'])
            || ! is_array($payload['templates'])
            || ! Schema::hasTable('lb_template_packs')
            || ! Schema::hasTable('lb_template_pack_items')
        ) {
            return null;
        }

        if (! Schema::hasColumn('lb_template_packs', 'created_by_user_id')) {
            throw new \RuntimeException(
                'Private template packs require the user ownership migration.'
            );
        }

        $name = (string) ($payload['name'] ?? 'Imported Template Pack');
        $slug = $this->uniquePackSlug((string) ($payload['slug'] ?? $name));

        $pack = [
            'team_id' => null,
            'name' => $name,
            'slug' => $slug,
            'category' => (string) ($payload['category'] ?? 'general'),
            'description' => (string) ($payload['description'] ?? ''),
            'preview_image' => $payload['preview_image'] ?? null,
            'source' => 'imported',
            'visibility' => 'private',
            'status' => 'active',
            'version' => (string) ($payload['version'] ?? '1.0.0'),
            'install_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $pack['created_by_user_id'] = $user->id;

        $packId = DB::table('lb_template_packs')->insertGetId($pack);

        foreach ($templateIds as $sort => $templateId) {
            DB::table('lb_template_pack_items')->insert([
                'pack_id' => $packId,
                'template_id' => $templateId,
                'sort_order' => $sort + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $packId;
    }

    protected function uniquePackSlug(string $name): string
    {
        $base = str($name)->slug()->toString() ?: 'template-pack';
        $slug = $base;
        $counter = 2;

        while (DB::table('lb_template_packs')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
