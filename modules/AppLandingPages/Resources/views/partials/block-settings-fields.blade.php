@php
    $blockType = (string) ($blockType ?? 'hero');
    $wirePrefix = (string) ($wirePrefix ?? 'builderNewBlock');
    $namePrefix = (string) ($namePrefix ?? 'landing_block');
    $values = (array) ($values ?? []);
@endphp

@switch($blockType)
    @case('benefits')
        <x-ui.input class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.headline" name="{{ $namePrefix }}_headline" :label="__('Section heading')" :placeholder="__('Why choose us')" />
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.items" name="{{ $namePrefix }}_items" :label="__('Benefit items')" rows="4" :help="__('One benefit per line.')" :placeholder="__('Fast response')">{{ data_get($values, 'items') }}</x-ui.textarea>
        @break

    @case('form')
        <x-ui.input wire:model.live="{{ $wirePrefix }}.form_title" name="{{ $namePrefix }}_form_title" :label="__('Form title')" :placeholder="__('Send your details')" />
        <x-ui.input wire:model.live="{{ $wirePrefix }}.submit_button" name="{{ $namePrefix }}_submit_button" :label="__('Submit button')" :placeholder="__('Send request')" />
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.success_message" name="{{ $namePrefix }}_success_message" :label="__('Success message')" rows="3" :placeholder="__('Thank you. We have received your request.')">{{ data_get($values, 'success_message') }}</x-ui.textarea>
        @break

    @case('offer')
    @case('coupon_details')
        <x-ui.input wire:model.live="{{ $wirePrefix }}.offer_title" name="{{ $namePrefix }}_offer_title" :label="__('Offer title')" :placeholder="__('20% off your next visit')" />
        <x-ui.input wire:model.live="{{ $wirePrefix }}.discount" name="{{ $namePrefix }}_discount" :label="__('Discount / value')" :placeholder="__('20% off')" />
        <x-ui.input wire:model.live="{{ $wirePrefix }}.expiry" name="{{ $namePrefix }}_expiry" :label="__('Valid until')" :placeholder="__('Ask staff')" />
        <x-ui.textarea wire:model.live="{{ $wirePrefix }}.terms" name="{{ $namePrefix }}_terms" :label="__('Terms')" rows="3" :placeholder="__('Terms and conditions')">{{ data_get($values, 'terms') }}</x-ui.textarea>
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.redemption_instructions" name="{{ $namePrefix }}_redemption_instructions" :label="__('Redemption instructions')" rows="3" :placeholder="__('Show this offer to the local team when you visit.')">{{ data_get($values, 'redemption_instructions') }}</x-ui.textarea>
        @break

    @case('booking_services')
        <x-ui.input wire:model.live="{{ $wirePrefix }}.service_title" name="{{ $namePrefix }}_service_title" :label="__('Service title')" :placeholder="__('Consultation')" />
        <x-ui.input wire:model.live="{{ $wirePrefix }}.duration" name="{{ $namePrefix }}_duration" :label="__('Duration')" :placeholder="__('30 minutes')" />
        <x-ui.input wire:model.live="{{ $wirePrefix }}.price" name="{{ $namePrefix }}_price" :label="__('Price')" :placeholder="__('Ask us')" />
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.description" name="{{ $namePrefix }}_description" :label="__('Service description')" rows="3" :placeholder="__('Choose a service and preferred time.')">{{ data_get($values, 'description') }}</x-ui.textarea>
        @break

    @case('business_info')
        <x-ui.input class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.headline" name="{{ $namePrefix }}_headline" :label="__('Section heading')" :placeholder="__('Business info')" />
        <div class="md:col-span-2 grid gap-3 sm:grid-cols-2">
            <x-ui.checkbox wire:model.live="{{ $wirePrefix }}.show_address" name="{{ $namePrefix }}_show_address" :label="__('Show address')" />
            <x-ui.checkbox wire:model.live="{{ $wirePrefix }}.show_phone" name="{{ $namePrefix }}_show_phone" :label="__('Show phone')" />
            <x-ui.checkbox wire:model.live="{{ $wirePrefix }}.show_website" name="{{ $namePrefix }}_show_website" :label="__('Show website')" />
            <x-ui.checkbox wire:model.live="{{ $wirePrefix }}.show_hours" name="{{ $namePrefix }}_show_hours" :label="__('Show opening hours')" />
        </div>
        @break

    @case('social_links')
        <x-ui.input class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.headline" name="{{ $namePrefix }}_headline" :label="__('Section heading')" :placeholder="__('Follow us')" />
        <x-ui.input wire:model.live="{{ $wirePrefix }}.facebook_url" name="{{ $namePrefix }}_facebook_url" :label="__('Facebook URL')" :placeholder="__('https://facebook.com/...')" />
        <x-ui.input wire:model.live="{{ $wirePrefix }}.instagram_url" name="{{ $namePrefix }}_instagram_url" :label="__('Instagram URL')" :placeholder="__('https://instagram.com/...')" />
        <x-ui.input class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.website_url" name="{{ $namePrefix }}_website_url" :label="__('Website URL')" :placeholder="__('https://example.com')" />
        @break

    @case('faq')
        <x-ui.input class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.question" name="{{ $namePrefix }}_question" :label="__('Question')" :placeholder="__('What happens next?')" />
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.answer" name="{{ $namePrefix }}_answer" :label="__('Answer')" rows="4" :placeholder="__('Your submission is sent directly to the local team.')">{{ data_get($values, 'answer') }}</x-ui.textarea>
        @break

    @case('testimonials')
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.quote" name="{{ $namePrefix }}_quote" :label="__('Quote')" rows="4" :placeholder="__('Great service and easy follow-up.')">{{ data_get($values, 'quote') }}</x-ui.textarea>
        <x-ui.input class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.author" name="{{ $namePrefix }}_author" :label="__('Author')" :placeholder="__('Local customer')" />
        @break

    @case('map')
        <x-ui.input class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.map_title" name="{{ $namePrefix }}_map_title" :label="__('Map title')" :placeholder="__('Find us')" />
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.map_text" name="{{ $namePrefix }}_map_text" :label="__('Location note')" rows="3" :placeholder="__('Use the business address to show a map or location note.')">{{ data_get($values, 'map_text') }}</x-ui.textarea>
        @break

    @case('opening_hours')
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.hours_text" name="{{ $namePrefix }}_hours_text" :label="__('Opening hours')" rows="4" :help="__('One line per schedule row.')" :placeholder="__('Monday - Friday: 9:00 AM - 5:00 PM')">{{ data_get($values, 'hours_text') }}</x-ui.textarea>
        @break

    @case('thank_you')
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.message" name="{{ $namePrefix }}_message" :label="__('Thank you message')" rows="4" :placeholder="__('Thank you. We have received your request.')">{{ data_get($values, 'message') }}</x-ui.textarea>
        <x-ui.input class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.cta" name="{{ $namePrefix }}_cta" :label="__('Optional CTA')" :placeholder="__('Back to website')" />
        @break

    @case('custom_html')
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.html" name="{{ $namePrefix }}_html" :label="__('HTML content')" rows="6" :placeholder="__('<div>Custom content</div>')">{{ data_get($values, 'html') }}</x-ui.textarea>
        @break

    @default
        <x-ui.input class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.headline" name="{{ $namePrefix }}_headline" :label="__('Headline')" :placeholder="__('Headline')" />
        <x-ui.textarea class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.body" name="{{ $namePrefix }}_body" :label="__('Body / content')" rows="3" :placeholder="__('Body / content')">{{ data_get($values, 'body') }}</x-ui.textarea>
        <x-ui.input class="md:col-span-2" wire:model.live="{{ $wirePrefix }}.cta" name="{{ $namePrefix }}_cta" :label="__('CTA')" :placeholder="__('CTA')" />
@endswitch
