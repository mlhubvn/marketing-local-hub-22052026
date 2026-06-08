@props([
    'feature' => [],
    'tone' => 'portal',
])

@if (\Modules\AdminPlans\Support\PlanFeatureOrder::isMlhubAiFeature($feature))
    <div x-data="{ open: false }" class="relative shrink-0">
        <button
            type="button"
            x-on:click="open = !open"
            x-on:mouseenter="open = true"
            x-on:mouseleave="open = false"
            class="inline-flex h-7 w-7 items-center justify-center rounded-full transition {{ $tone === 'guest' ? '' : '' }}"
            style="{{ $tone === 'guest' ? 'background: color-mix(in srgb, var(--lb-lime) 28%, #fff); color: #ff5f5f;' : 'background-color: rgba(var(--theme-border-color-rgb),0.12); color: var(--theme-muted-text-color);' }}"
            aria-label="{{ __('About MLHUB AI') }}"
        >
            <i class="fa-light fa-circle-exclamation text-[11px]"></i>
        </button>
        <div
            x-show="open"
            x-transition.opacity.duration.150ms
            x-on:mouseenter="open = true"
            x-on:mouseleave="open = false"
            class="absolute right-0 top-full z-30 mt-3 w-[18rem] max-w-[calc(100vw-2rem)] rounded-[1.15rem] border p-4 shadow-[0_30px_80px_-40px_rgba(15,23,42,0.45)]"
            style="display: none; {{ $tone === 'guest' ? 'border-color: var(--lb-line); background-color: rgba(255,255,255,0.98);' : 'border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-overlay);' }}"
        >
            <p class="text-xs font-bold leading-6" style="{{ $tone === 'guest' ? 'color: var(--lb-ink);' : 'color: var(--theme-header-text-color);' }}">
                {{ __('MLHUB AI assistant (MCP-style chat) on Portal Dashboard lets local businesses ask in natural language. Examples:') }}
            </p>
            <ul class="mt-3 space-y-2 text-xs leading-5" style="{{ $tone === 'guest' ? 'color: var(--lb-muted);' : 'color: var(--theme-muted-text-color);' }}">
                <li>• {{ __('Any new customers this week?') }}</li>
                <li>• {{ __('Summarize running campaigns') }}</li>
                <li>• {{ __('Are this week\'s reviews good?') }}</li>
                <li>• {{ __('What should I do next? / Suggest a new campaign.') }}</li>
            </ul>
        </div>
    </div>
@endif
