@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    @include(theme_view('partials.marketing-lb-about-styles', 'guest'))

    <div class="lb-sales lb-about-fullpage">
        @include(theme_view('partials.about-sections', 'guest'))
    </div>

    <script>
        (() => {
            const root = document.querySelector('.lb-about-fullpage');

            if (!root || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            const revealTargets = root.querySelectorAll([
                '.lb-about-screen .lb-wrap > .grid',
                '.lb-about-screen .lb-wrap > .text-center',
                '.lb-about-screen .lb-wrap > .lb-card',
                '.lb-about-screen .lb-card.lb-final-cta',
                '.lb-about-chapters',
            ].join(','));

            revealTargets.forEach((element, index) => {
                if (element.classList.contains('lb-reveal') || element.closest('#about')) {
                    return;
                }

                element.classList.add('lb-scroll');
                element.style.setProperty('--lb-stagger', `${Math.min(index % 8, 7) * 55}ms`);
            });

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, {
                threshold: 0.16,
                rootMargin: '0px 0px -8% 0px',
            });

            root.querySelectorAll('.lb-scroll').forEach((element) => observer.observe(element));
        })();
    </script>
@endcomponent
