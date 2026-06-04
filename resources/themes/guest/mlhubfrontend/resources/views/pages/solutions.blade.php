@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    @include(theme_view('partials.marketing-lb-about-styles', 'guest'))

    <div class="lb-sales">
        @include(theme_view('partials.solutions-catalog', 'guest'))
    </div>
@endcomponent
