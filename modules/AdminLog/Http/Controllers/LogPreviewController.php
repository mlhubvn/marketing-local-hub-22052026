<?php

namespace Modules\AdminLog\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AdminLog\Support\LogManager;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogPreviewController extends Controller
{
    public function __invoke(Request $request, LogManager $logs): Response
    {
        $file = basename(trim((string) $request->query('file', '')));
        $lines = max(50, min(500, (int) $request->query('lines', 200)));

        if ($file === '') {
            abort(404);
        }

        try {
            $content = $logs->tail($file, $lines);
        } catch (Throwable $exception) {
            report($exception);

            abort(404);
        }

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
