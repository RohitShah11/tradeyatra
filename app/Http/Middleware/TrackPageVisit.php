<?php

namespace App\Http\Middleware;

use App\Services\AnalyticsTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    public function __construct(private readonly AnalyticsTracker $tracker) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $userAgent = strtolower((string) $request->userAgent());

        if ($request->isMethod('GET') && $response->isSuccessful() && ! preg_match('/bot|crawl|spider|slurp|preview/', $userAgent)) {
            $this->tracker->track($request, 'page_view');
        }

        return $response;
    }
}
