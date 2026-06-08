@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    @include(theme_view('partials.marketing-lb-about-styles', 'guest'))

    <div class="lb-sales">
        @include(theme_view('partials.about-sections', 'guest'))
    </div>

    <script>
        (() => {
            const root = document.querySelector('.lb-sales');

            if (!root || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            const revealTargets = root.querySelectorAll([
                '.lb-section > .grid',
                '.lb-section > .text-center',
                '.lb-section > .lb-card',
                '.lb-workflow-band .lb-wrap > div',
                '.lb-card',
                '.lb-window',
                '.lb-pill',
                '.lb-proof-visual',
            ].join(','));

            revealTargets.forEach((element, index) => {
                if (element.classList.contains('lb-reveal') || element.closest('#mlhub-ai-mcp')) {
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
