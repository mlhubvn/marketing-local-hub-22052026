<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="rounded-[1.35rem] border px-5 py-6 sm:px-6 xl:px-7" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.12), transparent 40%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <a href="{{ route('portal.businesses') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-muted-text-color);">
            <i class="fa-light fa-arrow-left"></i>{{ __('Businesses') }}
        </a>
        <h1 class="mt-4 text-[2rem] font-semibold tracking-[-0.04em] sm:text-[2.45rem]" style="color: var(--theme-header-text-color);">{{ __('Create business') }}</h1>
        <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Start with a clean profile. You can add locations, customers, campaigns, and templates after saving.') }}</p>
    </section>

    @include('appbusinessprofiles::partials.form')
</div>
