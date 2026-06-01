<?php

namespace Modules\AdminSettings\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AdminPlans\Support\CurrencyCatalog;

#[Title('General settings')]
class General extends Component
{
    protected OptionStore $options;

    public string $website_title = '';

    public string $website_description = '';

    public string $website_keyword = '';

    public string $website_favicon = '';

    public string $website_logo_dark = '';

    public string $website_logo_light = '';

    public string $website_logo_brand_dark = '';

    public string $website_logo_brand_light = '';

    public string $format_date = 'd/m/Y';

    public string $format_datetime = 'd/m/Y H:i';

    public string $format_number_style = 'vi_VN';

    public string $default_currency = 'VND';

    public string $format_money_decimals = '0';

    public string $app_timezone = 'UTC';

    public string $contact_company_name = '';

    public string $contact_company_website = '';

    public string $contact_email = '';

    public string $contact_phone_number = '';

    public string $contact_working_hours = '';

    public string $contact_location = '';

    public function boot(OptionStore $options): void
    {
        $this->options = $options;
    }

    public function mount(): void
    {
        $this->website_title = $this->optionOrDefault('website_title', config('site.title', config('app.name', 'Stackposts')));
        $this->website_description = $this->optionOrDefault('website_description', config('site.description', ''));
        $this->website_keyword = $this->optionOrDefault('website_keyword', config('site.keywords', ''));
        $this->website_favicon = (string) $this->options->get('website_favicon', 'img/favicon.png');
        $this->website_logo_dark = (string) $this->options->get('website_logo_dark', 'img/logo-dark.png');
        $this->website_logo_light = (string) $this->options->get('website_logo_light', 'img/logo-light.png');
        $this->website_logo_brand_dark = (string) $this->options->get('website_logo_brand_dark', 'img/logo-brand-dark.png');
        $this->website_logo_brand_light = (string) $this->options->get('website_logo_brand_light', 'img/logo-brand-light.png');
        $this->format_date = (string) $this->options->get('format_date', 'd/m/Y');
        $this->format_datetime = (string) $this->options->get('format_datetime', 'd/m/Y H:i');
        $this->format_number_style = (string) $this->options->get('format_number_style', 'vi_VN');
        $this->default_currency = strtoupper((string) $this->options->get('default_currency', 'VND'));
        $this->format_money_decimals = (string) $this->options->get('format_money_decimals', '0');
        $this->app_timezone = (string) $this->options->get('app_timezone', config('app.timezone', 'UTC'));
        $this->contact_company_name = (string) $this->options->get('contact_company_name', 'Your Company Name');
        $this->contact_company_website = (string) $this->options->get('contact_company_website', 'https://yourcompany.com');
        $this->contact_email = (string) $this->options->get('contact_email', 'support@yourcompany.com');
        $this->contact_phone_number = (string) $this->options->get('contact_phone_number', '+1 234 567 890');
        $this->contact_working_hours = (string) $this->options->get('contact_working_hours', 'Mon - Fri: 09:00 AM - 06:00 PM');
        $this->contact_location = (string) $this->options->get('contact_location', '123 Main Street, City, Country');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'website_title' => ['required', 'string', 'max:255'],
            'website_description' => ['nullable', 'string', 'max:500'],
            'website_keyword' => ['nullable', 'string', 'max:500'],
            'website_favicon' => ['nullable', 'string', 'max:2048'],
            'website_logo_dark' => ['nullable', 'string', 'max:2048'],
            'website_logo_light' => ['nullable', 'string', 'max:2048'],
            'website_logo_brand_dark' => ['nullable', 'string', 'max:2048'],
            'website_logo_brand_light' => ['nullable', 'string', 'max:2048'],
            'format_date' => ['required', 'string', 'max:100'],
            'format_datetime' => ['required', 'string', 'max:100'],
            'format_number_style' => ['required', 'string', Rule::in(['vi_VN', 'en_US'])],
            'default_currency' => ['required', 'string', 'max:10'],
            'format_money_decimals' => ['required', 'string', Rule::in(['0', '1', '2', '3', '4'])],
            'app_timezone' => ['required', 'timezone:all', Rule::in(timezone_options())],
            'contact_company_name' => ['nullable', 'string', 'max:255'],
            'contact_company_website' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone_number' => ['nullable', 'string', 'max:100'],
            'contact_working_hours' => ['nullable', 'string', 'max:255'],
            'contact_location' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            $this->options->set($key, $value);
        }

        [$decimalSeparator, $thousandsSeparator] = platform_format_separators_for_style($validated['format_number_style']);
        $this->options->set('format_decimal_separator', $decimalSeparator);
        $this->options->set('format_thousands_separator', $thousandsSeparator);

        reset_platform_format_settings();

        $this->dispatch('settings-saved');
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    protected function dateFormatOptions(): array
    {
        return collect([
            'd/m/Y',
            'M d, Y',
            'm/d/Y',
            'Y-m-d',
            'd M Y',
        ])->map(fn (string $format): array => [
            'value' => $format,
            'label' => now()->timezone($this->app_timezone ?: config('app.timezone', 'UTC'))->locale(app()->getLocale())->translatedFormat($format).' ('.$format.')',
        ])->all();
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    protected function dateTimeFormatOptions(): array
    {
        return collect([
            'd/m/Y H:i',
            'M d, Y H:i',
            'm/d/Y h:i A',
            'Y-m-d H:i:s',
            'd M Y H:i',
        ])->map(fn (string $format): array => [
            'value' => $format,
            'label' => now()->timezone($this->app_timezone ?: config('app.timezone', 'UTC'))->locale(app()->getLocale())->translatedFormat($format).' ('.$format.')',
        ])->all();
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    protected function numberStyleOptions(): array
    {
        $sample = 1234567;

        return [
            [
                'value' => 'vi_VN',
                'label' => number_format($sample, 0, ',', '.').' ('.__('Vietnamese').')',
            ],
            [
                'value' => 'en_US',
                'label' => number_format($sample, 0, '.', ',').' ('.__('US / International').')',
            ],
        ];
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    protected function moneyDecimalOptions(): array
    {
        return [
            ['value' => '0', 'label' => __('Whole numbers (550.000)')],
            ['value' => '2', 'label' => __('Two decimals (550.000,00)')],
        ];
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    protected function currencyOptions(): array
    {
        return collect(['VND', 'USD', 'EUR', 'GBP', 'JPY', 'SGD', 'THB', 'AUD', 'CNY'])
            ->map(fn (string $code): array => [
                'value' => $code,
                'label' => trim($code.' — '.CurrencyCatalog::nameFor($code).' ('.CurrencyCatalog::symbolFor($code).')'),
            ])
            ->all();
    }

    protected function optionOrDefault(string $key, string $default = ''): string
    {
        $value = $this->options->get($key);

        if ($value === null) {
            return $default;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : $default;
    }

    public function render(): View
    {
        return view('adminsettings::livewire.general', [
            'dateFormatOptions' => $this->dateFormatOptions(),
            'dateTimeFormatOptions' => $this->dateTimeFormatOptions(),
            'numberStyleOptions' => $this->numberStyleOptions(),
            'currencyOptions' => $this->currencyOptions(),
            'moneyDecimalOptions' => $this->moneyDecimalOptions(),
            'timezoneOptions' => timezone_select_options(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('General settings'),
        ]);
    }
}
