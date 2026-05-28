<?php

namespace Modules\CustomAdminCache\Livewire;

use Livewire\ComponentHook;
use Modules\AdminCache\Livewire\CacheIndex;

class ReloadAfterCacheActionHook extends ComponentHook
{
    public function mount($params, $parent, $attributes): void
    {
        if (! $this->component instanceof CacheIndex) {
            return;
        }

        if (! session()->has('cache_action_notice')) {
            return;
        }

        $notice = session()->pull('cache_action_notice');

        $this->component->statusVariant = (string) ($notice['variant'] ?? 'success');
        $this->component->statusMessage = (string) ($notice['message'] ?? '');
    }

    public function call($method, $params, $returnEarly, $metadata, $componentContext)
    {
        if ($method !== 'runAction' || ! $this->component instanceof CacheIndex) {
            return;
        }

        return function (): void {
            /** @var CacheIndex $component */
            $component = $this->component;

            if ($component->statusMessage) {
                session()->flash('cache_action_notice', [
                    'variant' => $component->statusVariant,
                    'message' => $component->statusMessage,
                ]);
            }

            $component->skipRender();
            $component->js('window.location.reload()');
        };
    }
}
