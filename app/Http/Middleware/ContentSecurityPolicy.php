<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Log;

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

        $response->header('Content-Security-Policy', $cspHeader);
        Log::info('CSP Middleware applied: ' . $cspHeader);

        return $response;
    }
}
