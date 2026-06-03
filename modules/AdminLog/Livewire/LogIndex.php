<?php

namespace Modules\AdminLog\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminLog\Support\LogManager;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

#[Title('Logs')]
class LogIndex extends Component
{
    protected LogManager $logs;

    public ?string $selectedFile = null;

    public int $lines = 200;

    public ?string $statusMessage = null;

    public string $statusVariant = 'success';

    public function boot(LogManager $logs): void
    {
        $this->logs = $logs;
    }

    public function mount(): void
    {
        $this->selectedFile = $this->logs->defaultFile();
    }

    public function selectFile(string $file): void
    {
        try {
            $this->logs->resolve($file);

            $this->selectedFile = basename($file);
            $this->statusMessage = null;
            $this->dispatchPreviewReload();
        } catch (Throwable $exception) {
            report($exception);

            $this->statusVariant = 'danger';
            $this->statusMessage = $exception->getMessage();
        }
    }

    public function download(string $file): ?StreamedResponse
    {
        try {
            $path = $this->logs->resolve($file);
            $downloadName = $this->logs->downloadFilename($file);
        } catch (Throwable $exception) {
            report($exception);

            $this->statusVariant = 'danger';
            $this->statusMessage = $exception->getMessage();

            return null;
        }

        return response()->streamDownload(function () use ($path): void {
            $handle = fopen($path, 'rb');

            if ($handle === false) {
                return;
            }

            while (! feof($handle)) {
                echo fread($handle, 8192);
            }

            fclose($handle);
        }, $downloadName, ['Content-Type' => 'text/plain']);
    }

    public function clearFile(string $file): void
    {
        $this->runSafely(fn (): string => $this->logs->clear($file));

        if ($this->statusVariant === 'success') {
            $this->dispatchPreviewReload();
        }
    }

    public function deleteFile(string $file): void
    {
        $this->runSafely(fn (): string => $this->logs->delete($file));

        if ($this->statusVariant !== 'success') {
            return;
        }

        if (basename($file) === $this->selectedFile) {
            $this->selectedFile = $this->logs->defaultFile();
        }

        $this->dispatchPreviewReload();
    }

    public function refresh(): void
    {
        $this->statusMessage = null;
        $this->logs->forgetFilesCache();
        $this->dispatchPreviewReload();
    }

    protected function runSafely(callable $callback): void
    {
        try {
            $message = $callback();

            $this->statusVariant = 'success';
            $this->statusMessage = $message;
        } catch (Throwable $exception) {
            report($exception);

            $this->statusVariant = 'danger';
            $this->statusMessage = $exception->getMessage();
        }
    }

    protected function dispatchPreviewReload(): void
    {
        if (! $this->selectedFile) {
            return;
        }

        $this->dispatch('log-preview-reload', file: $this->selectedFile, lines: $this->lines);
    }

    public function render(): View
    {
        $files = $this->logs->files();

        $activeFile = collect($files)->firstWhere('name', $this->selectedFile) ?? ($files[0] ?? null);

        return view('adminlog::index', [
            'files' => $files,
            'activeFile' => $activeFile,
            'previewUrl' => route('admin-log.preview'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Logs'),
        ]);
    }
}
