<?php

namespace Modules\AdminLog\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class LogManager
{
    protected int $maxTailScanBytes = 524288;

    protected int $maxOutputChars = 131072;

    protected int $maxLineLength = 4000;

    protected int $maxFilesListed = 100;

    public function directory(): string
    {
        return storage_path('logs');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function files(): array
    {
        return Cache::remember('mlhub.admin.log.files.v1', now()->addSeconds(60), fn (): array => $this->scanFiles());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function scanFiles(): array
    {
        $directory = $this->directory();

        if (! is_dir($directory)) {
            return [];
        }

        $paths = glob($directory.DIRECTORY_SEPARATOR.'*.log') ?: [];

        return collect($paths)
            ->filter(fn (string $path): bool => is_file($path))
            ->map(function (string $path): array {
                $size = (int) filesize($path);
                $mtime = (int) filemtime($path);

                return [
                    'name' => basename($path),
                    'size' => $size,
                    'size_human' => $this->humanSize($size),
                    'mtime' => $mtime,
                    'modified' => Carbon::createFromTimestamp($mtime)->diffForHumans(),
                ];
            })
            ->sortByDesc('mtime')
            ->take($this->maxFilesListed)
            ->values()
            ->all();
    }

    public function forgetFilesCache(): void
    {
        Cache::forget('mlhub.admin.log.files.v1');
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
        $lines = max(1, min($lines, 500));
        $size = (int) filesize($path);

        if ($size === 0) {
            return '';
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return '';
        }

        $chunkSize = 8192;
        $buffer = '';
        $lineCount = 0;
        $position = $size;
        $bytesRead = 0;

        while ($position > 0 && $lineCount <= $lines && $bytesRead < $this->maxTailScanBytes) {
            $readSize = (int) min($chunkSize, $position);
            $position -= $readSize;
            fseek($handle, $position);
            $chunk = (string) fread($handle, $readSize);
            $buffer = $chunk.$buffer;
            $bytesRead += $readSize;
            $lineCount = substr_count($buffer, "\n");
        }

        fclose($handle);

        $rows = preg_split("/\r\n|\n|\r/", $buffer) ?: [];
        $rows = array_slice($rows, -$lines);
        $output = trim(implode("\n", array_map(fn (string $row): string => $this->truncateLine($row), $rows)));
        $output = $this->capOutput($output);

        if ($size > $this->maxTailScanBytes || $bytesRead >= $this->maxTailScanBytes) {
            return __('… (showing the last :lines lines — download for the full log)', ['lines' => $lines])."\n\n".$output;
        }

        return $output;
    }

    public function clear(string $file): string
    {
        $path = $this->resolve($file);

        file_put_contents($path, '');
        $this->forgetFilesCache();

        return __('Log file :name cleared successfully.', ['name' => basename($path)]);
    }

    public function delete(string $file): string
    {
        $path = $this->resolve($file);

        @unlink($path);
        $this->forgetFilesCache();

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

    protected function truncateLine(string $line): string
    {
        if (strlen($line) <= $this->maxLineLength) {
            return $line;
        }

        return substr($line, 0, $this->maxLineLength).'…';
    }

    protected function capOutput(string $output): string
    {
        if (strlen($output) <= $this->maxOutputChars) {
            return $output;
        }

        return substr($output, -$this->maxOutputChars);
    }
}
