<?php

namespace Modules\AdminPlans\Support;

class PlanPermissionSchema
{
    public function definitions(): array
    {
        $definitions = collect(plan_permissions())
            ->reject(fn (array $definition): bool => (string) ($definition['key'] ?? '') === 'credits_usage')
            ->values();

        $definitions = $this->applyLabelOverrides($this->groupRelatedPermissions($definitions));

        $creditFields = collect(credit_actions())
            ->map(fn (array $action): array => [
                'key' => (string) $action['plan_key'],
                'label' => (string) $action['label'],
                'type' => 'number',
                'default' => (int) ($action['default_cost'] ?? 1),
                'description' => $action['description'] ?? __('Credits deducted each time this action runs.'),
            ])
            ->values()
            ->all();

        array_unshift($creditFields, [
            'key' => 'credits_usage_limit',
            'label' => __('Plan Credits'),
            'type' => 'number',
            'default' => -1,
            'description' => __('Total credits available for the assigned plan period. Use -1 for unlimited credits.'),
        ]);

        return $definitions
            ->prepend([
                'key' => 'credits_usage',
                'label' => __('Credits Usage'),
                'type' => 'config',
                'toggleable' => false,
                'order' => 10,
                'source' => 'default',
                'placement' => 'top',
                'fields' => $creditFields,
            ])
            ->all();
    }

    protected function groupRelatedPermissions(\Illuminate\Support\Collection $definitions): \Illuminate\Support\Collection
    {
        $groups = [];

        foreach ($groups as $parentKey => $childKeys) {
            $parentIndex = $definitions->search(fn (array $definition): bool => ($definition['key'] ?? null) === $parentKey);

            if ($parentIndex === false) {
                continue;
            }

            $parent = $definitions->get($parentIndex);
            $fields = collect($parent['fields'] ?? []);

            foreach ($childKeys as $childKey) {
                $child = $definitions->firstWhere('key', $childKey);

                if (! $child) {
                    continue;
                }

                $fields->push([
                    'key' => $childKey,
                    'label' => $child['label'] ?? $childKey,
                    'type' => 'boolean',
                    'default' => (bool) ($child['default'] ?? false),
                    'description' => $child['description'] ?? null,
                ]);

                foreach ($child['fields'] ?? [] as $childField) {
                    if (! $fields->contains(fn (array $field): bool => ($field['key'] ?? null) === ($childField['key'] ?? null))) {
                        $fields->push($childField);
                    }
                }
            }

            $parent['fields'] = $fields->values()->all();
            $definitions->put($parentIndex, $parent);
        }

        return $definitions
            ->reject(fn (array $definition): bool => in_array((string) ($definition['key'] ?? ''), array_merge(...array_values($groups)), true))
            ->values();
    }

    protected function applyLabelOverrides(\Illuminate\Support\Collection $definitions): \Illuminate\Support\Collection
    {
        $fieldLabels = [];

        return $definitions
            ->map(function (array $definition) use ($fieldLabels): array {
                $definition['fields'] = collect($definition['fields'] ?? [])
                    ->map(function (array $field) use ($fieldLabels): array {
                        $key = (string) ($field['key'] ?? '');

                        if (isset($fieldLabels[$key])) {
                            $field['label'] = $fieldLabels[$key];
                        }

                        return $field;
                    })
                    ->values()
                    ->all();

                return $definition;
            })
            ->values();
    }

    public function mapToState(array $savedPermissions): array
    {
        $state = [];

        foreach ($this->definitions() as $definition) {
            $key = $definition['key'];
            $type = $definition['type'] ?? 'toggle';

            if ($type === 'toggle') {
                $default = (bool) ($definition['default'] ?? false);
                $state[$key] = (bool) ($savedPermissions[$key] ?? $default);

                continue;
            }

            $toggleable = $definition['toggleable'] ?? true;
            $state[$key] = [
                'enabled' => $toggleable ? (bool) ($savedPermissions[$key] ?? false) : true,
            ];

            foreach ($definition['fields'] ?? [] as $field) {
                $fieldKey = $field['key'];
                $fieldType = $field['type'] ?? 'boolean';
                $default = $field['default'] ?? null;

                if ($fieldType === 'checkbox_list') {
                    $state[$key][$fieldKey] = collect($field['options'] ?? [])
                        ->filter(fn (array $option): bool => (bool) ($savedPermissions[$option['key']] ?? false))
                        ->pluck('key')
                        ->map(fn ($value) => (string) $value)
                        ->values()
                        ->all();

                    continue;
                }

                if ($fieldType === 'choice') {
                    $state[$key][$fieldKey] = (string) ($savedPermissions[$fieldKey] ?? $default ?? '');

                    continue;
                }

                if ($fieldType === 'number') {
                    $state[$key][$fieldKey] = $savedPermissions[$fieldKey] ?? $default ?? 0;

                    continue;
                }

                $state[$key][$fieldKey] = (bool) ($savedPermissions[$fieldKey] ?? $default ?? false);
            }
        }

        return $state;
    }

    public function normalize(array $input): array
    {
        $normalized = [];

        foreach ($this->definitions() as $definition) {
            $key = $definition['key'];
            $type = $definition['type'] ?? 'toggle';

            if ($type === 'toggle') {
                $normalized[$key] = (string) ($input[$key] ?? ($definition['default'] ?? '0')) === '1';

                continue;
            }

            $itemInput = $input[$key] ?? [];
            $normalized[$key] = ($definition['toggleable'] ?? true) ? ! empty($itemInput['enabled']) : true;

            foreach ($definition['fields'] ?? [] as $field) {
                $fieldKey = $field['key'];
                $fieldType = $field['type'] ?? 'boolean';

                if ($fieldType === 'checkbox_list') {
                    foreach ($field['options'] ?? [] as $option) {
                        $optionKey = $option['key'];
                        $normalized[$optionKey] = in_array($optionKey, $itemInput[$fieldKey] ?? [], true);
                    }

                    continue;
                }

                if ($fieldType === 'number') {
                    $raw = $itemInput[$fieldKey] ?? $field['default'] ?? 0;
                    $normalized[$fieldKey] = is_numeric($raw) ? (float) $raw : ($field['default'] ?? 0);

                    continue;
                }

                if ($fieldType === 'choice') {
                    $normalized[$fieldKey] = (string) ($itemInput[$fieldKey] ?? ($field['default'] ?? ''));

                    continue;
                }

                $raw = $itemInput[$fieldKey] ?? ($field['default'] ?? false);
                $normalized[$fieldKey] = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? in_array($raw, ['1', 1], true);
            }
        }

        return $normalized;
    }
}
