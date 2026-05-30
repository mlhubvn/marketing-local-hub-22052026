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
    }

    public function deleteFile(string $file): void
    {
        $this->runSafely(fn (): string => $this->logs->delete($file));

        if ($this->statusVariant === 'success' && basename($file) === $this->selectedFile) {
            $this->selectedFile = $this->logs->defaultFile();
        }
    }

    public function refresh(): void
    {
        $this->statusMessage = null;
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

    public function render(): View
    {
        $files = $this->logs->files();

        $content = null;

        if ($this->selectedFile) {
            try {
                $content = $this->logs->tail($this->selectedFile, $this->lines);
            } catch (Throwable $exception) {
                $content = null;
                $this->selectedFile = null;
            }
        }

        $activeFile = collect($files)->firstWhere('name', $this->selectedFile) ?? ($files[0] ?? null);

        return view('adminlog::index', [
            'files' => $files,
            'activeFile' => $activeFile,
            'content' => $content,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Logs'),
        ]);
    }
}
