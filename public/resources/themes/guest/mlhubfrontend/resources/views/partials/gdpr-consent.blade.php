<div
    x-data="{
        open: false,
        key: 'localboost-gdpr-consent-v1',
        init() {
            this.open = localStorage.getItem(this.key) !== 'accepted' && localStorage.getItem(this.key) !== 'declined';
        },
        accept() {
            localStorage.setItem(this.key, 'accepted');
            this.open = false;
            window.dispatchEvent(new CustomEvent('gdpr-consent-updated', { detail: { consent: 'accepted' } }));
        },
        decline() {
            localStorage.setItem(this.key, 'declined');
            this.open = false;
            window.dispatchEvent(new CustomEvent('gdpr-consent-updated', { detail: { consent: 'declined' } }));
        }
    }"
    x-cloak
    x-show="open"
    x-transition.opacity
    class="fixed inset-x-0 bottom-0 z-[70] px-4 pb-4 sm:px-6 sm:pb-6"
>
    <div class="localboost-cookie-banner mx-auto max-w-5xl rounded-[1.1rem] border p-4 shadow-[0_24px_80px_-56px_rgba(15,23,42,0.45)] backdrop-blur-xl sm:p-5" style="border-color: #dfe9df; background: linear-gradient(135deg, rgba(255,95,95,0.055), rgba(255,255,255,0.96) 42%, rgba(247,250,246,0.98));">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex min-w-0 gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.85rem]" style="background-color: rgba(255,95,95,0.10); color: #ff5f5f;">
                    <i class="fa-light fa-cookie-bite"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-sm font-extrabold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Cookie Policy') }}</h2>
                    <p class="mt-1 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">
                        {{ __('We use cookies to improve your experience, measure traffic, and support analytics features.') }}
                        <a href="{{ route('guest.privacy-policy') }}" class="font-bold hover:opacity-80" style="color: #ff5f5f;">{{ __('Read our cookie policy') }}</a>
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                <button type="button" x-on:click="decline()" class="localboost-cookie-decline inline-flex h-11 items-center justify-center rounded-[var(--theme-button-radius)] border px-4 text-sm font-bold transition hover:-translate-y-px" style="border-color: #dfe9df; color: #15201b; background-color: rgba(255,255,255,0.78);">
                    {{ __('Decline') }}
                </button>
                <button type="button" x-on:click="accept()" class="localboost-cookie-accept inline-flex h-11 items-center justify-center rounded-[var(--theme-button-radius)] border px-4 text-sm font-bold text-white shadow-[0_16px_30px_-18px_rgba(255,95,95,0.62)] transition hover:-translate-y-px" style="border-color: #ff5f5f; background-color: #ff5f5f;">
                    {{ __('Allow') }}
                </button>
            </div>
        </div>
    </div>
</div>
