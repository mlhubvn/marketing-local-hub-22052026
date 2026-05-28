<?php

namespace Modules\CustomAdminCache\Livewire;

use Modules\AdminCache\Livewire\CacheIndex;
use Throwable;

class SafeCacheIndex extends CacheIndex
{
    public function mount(): void
    {
        if (! session()->has('cache_action_notice')) {
            return;
        }

        $notice = session()->pull('cache_action_notice');

        $this->statusVariant = (string) ($notice['variant'] ?? 'success');
        $this->statusMessage = (string) ($notice['message'] ?? '');
    }

    public function runAction(string $action): void
    {
        if ($action !== 'session') {
            parent::runAction($action);

            return;
        }

        try {
            $message = $this->actions->run($action);

            session()->flash('cache_action_notice', [
                'variant' => 'success',
                'message' => $message,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('cache_action_notice', [
                'variant' => 'danger',
                'message' => $exception->getMessage(),
            ]);
        }

        $this->redirectRoute('admin-cache.index', navigate: false);
    }
}
