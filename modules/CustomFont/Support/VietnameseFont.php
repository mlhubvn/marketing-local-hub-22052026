<?php

namespace Modules\CustomFont\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VietnameseFont
{
    public const STACK = '"Be Vietnam Pro", Inter, "Segoe UI", ui-sans-serif, system-ui, sans-serif';

    public const BUNNY_STYLESHEET = 'https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700,800|inter:400,500,600,700,800';

    /**
     * @return array<int, string>
     */
    public static function landingRouteNames(): array
    {
        return [
            'landing-pages.public',
            'landing-pages.preview',
        ];
    }

    public static function injectIntoResponse(Request $request, Response $response): void
    {
        if (! self::shouldInject($request, $response)) {
            return;
        }

        $content = $response->getContent();

        if (! is_string($content) || ! str_contains($content, '</head>')) {
            return;
        }

        if (str_contains($content, 'id="customfont-vietnamese"')) {
            return;
        }

        $markup = view('customfont::partials.vietnamese-font-head')->render();

        $response->setContent(str_replace('</head>', $markup.'</head>', $content));
        $response->headers->set('X-CustomFont', 'injected');
    }

    public static function shouldInject(Request $request, Response $response): bool
    {
        if (! $response->isSuccessful()) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return false;
        }

        $routeName = $request->route()?->getName();

        if (is_string($routeName) && in_array($routeName, self::landingRouteNames(), true)) {
            return true;
        }

        if (str_starts_with(trim($request->path(), '/'), 'lp/')) {
            return true;
        }

        $content = $response->getContent();

        if (! is_string($content)) {
            return false;
        }

        return str_contains($content, 'class="shell"')
            && str_contains($content, 'form-card');
    }
}
