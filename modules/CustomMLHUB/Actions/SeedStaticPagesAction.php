<?php

namespace Modules\CustomMLHUB\Actions;

use Illuminate\Support\Facades\Schema;
use Modules\AdminSettings\Support\OptionStore;
use RuntimeException;

class SeedStaticPagesAction
{
    public const PLACEHOLDER_TEXT = 'Nội dung đang cập nhật...';

    /** @var list<string> */
    public const OPTION_KEYS = [
        'privacy_policy_title',
        'privacy_policy_content',
        'terms_of_use_title',
        'terms_of_use_content',
    ];

    public function __construct(
        protected OptionStore $options,
    ) {}

    /**
     * @return array{updated: list<string>, preserved: list<string>}
     */
    public function handle(bool $force = false): array
    {
        if (! Schema::hasTable('options')) {
            throw new RuntimeException('Cannot seed MLHUB static pages: the options table does not exist.');
        }

        $defaults = $this->defaults();
        $updated = [];
        $preserved = [];

        foreach (self::OPTION_KEYS as $key) {
            if (! array_key_exists($key, $defaults)) {
                throw new RuntimeException("MLHUB static page data is missing required key [{$key}].");
            }

            $approved = $defaults[$key];
            $current = $this->options->get($key);

            if (! $force && ! $this->shouldReplace($current)) {
                $preserved[] = $key;

                continue;
            }

            $this->options->set($key, $approved);

            $stored = $this->options->get($key);

            if ((string) $stored !== (string) $approved) {
                throw new RuntimeException("Failed to persist MLHUB static page option [{$key}].");
            }

            $updated[] = $key;
        }

        return [
            'updated' => $updated,
            'preserved' => $preserved,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function defaults(): array
    {
        $path = (string) config(
            'custommlhub.static_pages_file',
            __DIR__.'/../Database/data/mlhub_static_pages.php'
        );

        if (! is_file($path)) {
            throw new RuntimeException("MLHUB static page data file not found: {$path}");
        }

        /** @var array<string, string> $data */
        $data = (array) require $path;

        return $data;
    }

    protected function shouldReplace(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        $normalized = $this->normalize((string) $value);

        if ($normalized === '') {
            return true;
        }

        return $normalized === self::PLACEHOLDER_TEXT;
    }

    protected function normalize(string $value): string
    {
        return trim(strip_tags(html_entity_decode($value)));
    }
}
