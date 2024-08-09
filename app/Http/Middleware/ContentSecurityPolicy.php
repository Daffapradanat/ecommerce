<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContentSecurityPolicy
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $cspHeader = "default-src 'self' https: data: blob: 'unsafe-inline' 'unsafe-eval'; " .
                     "img-src 'self' https: data: blob: http: https:; " .
                     "connect-src 'self' https: http: ws: wss:; " .
                     "font-src 'self' https: data:; " .
                     "media-src 'self' https: data:; " .
                     "object-src 'none'; " .
                     "script-src 'self' https: 'unsafe-inline' 'unsafe-eval'; " .
                     "style-src 'self' https: 'unsafe-inline';";

        if (!$response instanceof BinaryFileResponse && !$response instanceof StreamedResponse) {
            if (method_exists($response, 'header')) {
                $response->header('Content-Security-Policy', $cspHeader);
            } elseif (method_exists($response, 'headers')) {
                $response->headers->set('Content-Security-Policy', $cspHeader);
            }
            Log::info('CSP Middleware applied: ' . $cspHeader);
        } else {
            Log::info('CSP Middleware skipped for file download or stream');
        }

        return $response;
    }
}
