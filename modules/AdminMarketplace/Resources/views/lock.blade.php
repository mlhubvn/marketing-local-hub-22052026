<div class="flex min-h-[60vh] items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="rounded-[1.5rem] border p-8 shadow-[0_30px_90px_-50px_rgba(15,23,42,0.45)]" style="border-color: var(--theme-border-color); background: var(--theme-card-background);">
            <div class="flex flex-col items-center text-center">
                <span class="inline-flex h-16 w-16 items-center justify-center rounded-full" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                    <i class="fa-light fa-lock text-2xl"></i>
                </span>

                <h1 class="mt-5 text-2xl font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">
                    {{ __('Marketplace is locked') }}
                </h1>

                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                    {{ __('This area is protected. Enter the password to view and manage installed packages.') }}
                </p>
            </div>

            <form wire:submit="unlock" class="mt-7 space-y-4">
                <x-ui.input
                    type="password"
                    wire:model="password"
                    :label="__('Password')"
                    :placeholder="__('Enter password')"
                    :error="$errors->first('password')"
                    autocomplete="current-password"
                    autofocus
                />

                <x-ui.button type="submit" class="w-full justify-center">
                    <span wire:loading.remove wire:target="unlock">
                        <i class="fa-light fa-unlock text-sm"></i>
                        {{ __('Unlock') }}
                    </span>
                    <span wire:loading wire:target="unlock">{{ __('Unlocking...') }}</span>
                </x-ui.button>
            </form>
        </div>
    </div>
</div>
