<?php

namespace Modules\CustomFont\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\CustomFont\Support\VietnameseFont;
use Symfony\Component\HttpFoundation\Response;

class InjectVietnameseFont
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldInject($request, $response)) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || ! str_contains($content, '</head>')) {
            return $response;
        }

        if (str_contains($content, 'id="customfont-vietnamese"')) {
            return $response;
        }

        $markup = view('customfont::partials.vietnamese-font-head')->render();

        $response->setContent(str_replace('</head>', $markup.'</head>', $content));

        return $response;
    }

    protected function shouldInject(Request $request, Response $response): bool
    {
        if (! $response->isSuccessful()) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return false;
        }

        $routeName = $request->route()?->getName();

        if (is_string($routeName) && in_array($routeName, VietnameseFont::landingRouteNames(), true)) {
            return true;
        }

        return str_starts_with(trim($request->path(), '/'), 'lp/');
    }
}
