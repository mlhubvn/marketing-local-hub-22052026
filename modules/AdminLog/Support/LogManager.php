<?php

namespace Modules\AdminLog\Support;

use Illuminate\Support\Carbon;
use RuntimeException;

class LogManager
{
    protected int $maxReadBytes = 2097152;

    public function directory(): string
    {
        return storage_path('logs');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function files(): array
    {
        $directory = $this->directory();

        if (! is_dir($directory)) {
            return [];
        }

        $paths = glob($directory.DIRECTORY_SEPARATOR.'*.log') ?: [];

        return collect($paths)
            ->filter(fn (string $path): bool => is_file($path))
            ->map(fn (string $path): array => [
                'name' => basename($path),
                'size' => (int) filesize($path),
                'size_human' => $this->humanSize((int) filesize($path)),
                'mtime' => (int) filemtime($path),
                'modified' => Carbon::createFromTimestamp((int) filemtime($path))->diffForHumans(),
            ])
            ->sortByDesc('mtime')
            ->values()
            ->all();
    }

    public function defaultFile(): ?string
    {
        $files = $this->files();

        if ($files === []) {
            return null;
        }

        $names = array_column($files, 'name');

        if (in_array('laravel.log', $names, true)) {
            return 'laravel.log';
        }

        return $names[0];
    }

    public function tail(string $file, int $lines = 200): string
    {
        $path = $this->resolve($file);
        $size = (int) filesize($path);

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return '';
        }

        if ($size > $this->maxReadBytes) {
            fseek($handle, -$this->maxReadBytes, SEEK_END);
        }

        $contents = (string) stream_get_contents($handle);
        fclose($handle);

        $rows = preg_split("/\r\n|\n|\r/", $contents) ?: [];
        $rows = array_slice($rows, -max(1, $lines));

        $output = trim(implode("\n", $rows));

        if ($size > $this->maxReadBytes) {
            return __('… (showing the last :size of a larger file — download for the full log)', ['size' => $this->humanSize($this->maxReadBytes)])."\n\n".$output;
        }

        return $output;
    }

    public function clear(string $file): string
    {
        $path = $this->resolve($file);

        file_put_contents($path, '');

        return __('Log file :name cleared successfully.', ['name' => basename($path)]);
    }

    public function delete(string $file): string
    {
        $path = $this->resolve($file);

        @unlink($path);

        return __('Log file :name deleted successfully.', ['name' => basename($file)]);
    }

    public function resolve(string $file): string
    {
        $name = basename(trim($file));

        if ($name === '' || ! str_ends_with(strtolower($name), '.log')) {
            throw new RuntimeException(__('Invalid log file.'));
        }

        $path = $this->directory().DIRECTORY_SEPARATOR.$name;
        $real = realpath($path);
        $base = realpath($this->directory());

        if ($real === false || $base === false || ! str_starts_with($real, $base.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException(__('Invalid log file.'));
        }

        return $real;
    }

    public function downloadFilename(string $file): string
    {
        $name = basename($this->resolve($file));
        $stem = pathinfo($name, PATHINFO_FILENAME);

        return sprintf('%s-%s.log', $stem, now()->format('d-m-Y-H-i'));
    }

    protected function humanSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return round($bytes / (1024 ** $power), 2).' '.$units[$power];
    }
}
