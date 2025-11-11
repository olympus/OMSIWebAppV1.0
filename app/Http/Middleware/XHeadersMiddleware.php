<?php

namespace App\Http\Middleware;

use Closure;

class XHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src * 'self' 'unsafe-inline'; style-src * 'self' 'unsafe-inline'; font-src * 'self' 'unsafe-inline';");
        // $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src cdnjs.cloudflare.com cdn.datatables.net 'self' 'unsafe-inline'; style-src cdnjs.cloudflare.com select2.github.io cdn.datatables.net fonts.googleapis.com 'self' 'unsafe-inline'; font-src fonts.googleapis.com fonts.gstatic.com 'self';");
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        return $response;
    }
}
