<section class="w-full">
    <x-settings.layout :heading="__('General settings')" :subheading="__('Control shared application metadata, default formatting, and contact information for all future SaaS modules.')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <x-theme.section-card
                :title="__('Website settings')"
                :description="__('These values become the default identity layer for the platform and its future SaaS products.')"
                body-class="space-y-5 p-6"
            >
                <x-ui.input wire:model="website_title" :label="__('Website title')" type="text" :error="$errors->first('website_title')" />

                <x-ui.textarea wire:model="website_description" :label="__('Website description')" rows="4" :error="$errors->first('website_description')" />

                <x-ui.textarea wire:model="website_keyword" :label="__('Website keywords')" rows="3" :error="$errors->first('website_keyword')" />
            </x-theme.section-card>

            <x-theme.section-card
                :title="__('Brand settings')"
                :description="__('Manage the favicon and logo assets reused across guest pages, authentication screens, headers, and shared brand surfaces.')"
                body-class="space-y-5 p-6"
            >
                <x-ui.input-file-picker
                    wire:model="website_favicon"
                    :value="$website_favicon"
                    value-field="url"
                    :label="__('Website favicon')"
                    :error="$errors->first('website_favicon')"
                    :help="__('Choose a favicon from the file library, or paste a media path, file id, or full URL.')"
                    :dialog-title="__('Choose website favicon')"
                    :dialog-description="__('Select the favicon asset used in browser tabs and bookmarks.')"
                />

                <div class="grid gap-5 md:grid-cols-2">
                    <x-ui.input-file-picker
                        wire:model="website_logo_dark"
                        :value="$website_logo_dark"
                        value-field="url"
                        :label="__('Website logo dark')"
                        :error="$errors->first('website_logo_dark')"
                        :help="__('Choose the logo used on dark or high-contrast surfaces.')"
                        :dialog-title="__('Choose dark logo')"
                    />

                    <x-ui.input-file-picker
                        wire:model="website_logo_light"
                        :value="$website_logo_light"
                        value-field="url"
                        :label="__('Website logo light')"
                        :error="$errors->first('website_logo_light')"
                        :help="__('Choose the logo used on light surfaces when an alternate version is needed.')"
                        :dialog-title="__('Choose light logo')"
                    />
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <x-ui.input-file-picker
                        wire:model="website_logo_brand_dark"
                        :value="$website_logo_brand_dark"
                        value-field="url"
                        :label="__('Website logo brand dark')"
                        :error="$errors->first('website_logo_brand_dark')"
                        :help="__('Used by guest headers, auth layouts, and shared brand blocks on dark surfaces.')"
                        :dialog-title="__('Choose dark brand logo')"
                    />

                    <x-ui.input-file-picker
                        wire:model="website_logo_brand_light"
                        :value="$website_logo_brand_light"
                        value-field="url"
                        :label="__('Website logo brand light')"
                        :error="$errors->first('website_logo_brand_light')"
                        :help="__('Use this when your brand mark needs a dedicated version for light surfaces.')"
                        :dialog-title="__('Choose light brand logo')"
                    />
                </div>
            </x-theme.section-card>

            <x-theme.section-card
                :title="__('Locale & regional formats')"
                :description="__('Platform-wide defaults for dates, numbers, and currency. Modules should use format_date_locale(), format_number_locale(), and format_money() so changes apply everywhere.')"
                body-class="space-y-5 p-6"
            >
                <div class="grid gap-5 md:grid-cols-2">
                    <x-ui.select wire:model="format_date" :label="__('Date format')" :error="$errors->first('format_date')">
                        <option value="">{{ __('Select date format') }}</option>
                        @foreach ($dateFormatOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select wire:model="format_datetime" :label="__('Date and time format')" :error="$errors->first('format_datetime')">
                        <option value="">{{ __('Select date and time format') }}</option>
                        @foreach ($dateTimeFormatOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <x-ui.select wire:model="app_timezone" :label="__('Timezone')" :error="$errors->first('app_timezone')">
                    <option value="">{{ __('Select timezone') }}</option>
                    @foreach ($timezoneOptions as $timezone)
                        <option value="{{ $timezone['value'] }}">{{ $timezone['label'] }}</option>
                    @endforeach
                </x-ui.select>

                <div class="grid gap-5 md:grid-cols-3">
                    <x-ui.select wire:model="format_number_style" :label="__('Number format')" :error="$errors->first('format_number_style')">
                        @foreach ($numberStyleOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select wire:model="default_currency" :label="__('Default currency')" :error="$errors->first('default_currency')">
                        @foreach ($currencyOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select wire:model="format_money_decimals" :label="__('Money decimals')" :error="$errors->first('format_money_decimals')">
                        @foreach ($moneyDecimalOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="rounded-2xl border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-border-color-rgb, 148, 163, 184), .45); color: var(--theme-muted-text-color);">
                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Preview after save') }}</p>
                    <ul class="mt-2 space-y-1">
                        <li>{{ __('Date') }}: {{ format_date_locale(now()) }}</li>
                        <li>{{ __('Date & time') }}: {{ format_datetime_locale(now()) }}</li>
                        <li>{{ __('Number') }}: {{ format_number_locale(1234567) }}</li>
                        <li>{{ __('Money') }}: {{ format_money(550000) }}</li>
                    </ul>
                </div>
            </x-theme.section-card>

            <x-theme.section-card
                :title="__('Contact settings')"
                :description="__('These details can be reused across footer blocks, support pages, invoices, and legal content.')"
                body-class="space-y-5 p-6"
            >
                <div class="grid gap-5 md:grid-cols-2">
                    <x-ui.input wire:model="contact_company_name" :label="__('Company name')" type="text" :error="$errors->first('contact_company_name')" />
                    <x-ui.input wire:model="contact_company_website" :label="__('Company website')" type="url" :error="$errors->first('contact_company_website')" />
                    <x-ui.input wire:model="contact_email" :label="__('Email address')" type="email" :error="$errors->first('contact_email')" />
                    <x-ui.input wire:model="contact_phone_number" :label="__('Phone number')" type="text" :error="$errors->first('contact_phone_number')" />
                </div>

                <x-ui.input wire:model="contact_working_hours" :label="__('Working hours')" type="text" :error="$errors->first('contact_working_hours')" />
                <x-ui.textarea wire:model="contact_location" :label="__('Location')" rows="3" :error="$errors->first('contact_location')" />
            </x-theme.section-card>

            <div class="flex items-center gap-4">
                <x-ui.button type="submit">{{ __('Save changes') }}</x-ui.button>

                <x-action-message class="text-emerald-600 dark:text-emerald-400" on="settings-saved">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
