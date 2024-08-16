<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContentSecurityPolicy
{
    private $hasLogged = false;

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $cspHeader = "default-src 'self' https: data: blob: 'unsafe-inline' 'unsafe-eval'; " .
                     "img-src 'self' https: data: blob: http: https: file:; " .
                     "connect-src 'self' https: http: ws: wss: *; " .
                     "font-src 'self' https: data:; " .
                     "media-src 'self' https: data:; " .
                     "object-src 'none'; " .
                     "script-src 'self' https: 'unsafe-inline' 'unsafe-eval'; " .
                     "style-src 'self' https: 'unsafe-inline';";
                    //  "form-action 'self';";

        if (!$response instanceof BinaryFileResponse && !$response instanceof StreamedResponse) {
            $currentHeader = $response->headers->get('Content-Security-Policy');

            if ($currentHeader !== $cspHeader) {
                if (method_exists($response, 'header')) {
                    $response->header('Content-Security-Policy', $cspHeader);
                } elseif (method_exists($response, 'headers')) {
                    $response->headers->set('Content-Security-Policy', $cspHeader);
                }

                if (!$this->hasLogged) {
                    Log::info('CSP Middleware applied');
                    $this->hasLogged = true;
                }
            }
        } elseif (!$this->hasLogged) {
            Log::info('CSP Middleware skipped for file download or stream');
            $this->hasLogged = true;
        }

        return $response;
    }
}
