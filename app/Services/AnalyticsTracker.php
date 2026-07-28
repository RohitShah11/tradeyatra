<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class AnalyticsTracker
{
    public function track(Request $request, string $event, array $metadata = [], ?int $userId = null): AnalyticsEvent
    {
        $visitorId = $request->session()->get('analytics_visitor_id');
        if (! $visitorId) {
            $visitorId = (string) Str::uuid();
            $request->session()->put('analytics_visitor_id', $visitorId);
        }

        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $parameter) {
            if ($request->filled($parameter)) {
                $request->session()->put("analytics_{$parameter}", Str::limit((string) $request->query($parameter), 150, ''));
            }
        }

        $client = $this->clientDetails((string) $request->userAgent());
        $location = $this->locationDetails($request);

        return AnalyticsEvent::create([
            'visitor_id' => $visitorId,
            'user_id' => $userId ?? auth('web')->id(),
            'event' => $event,
            'route' => $request->route()?->getName(),
            'path' => Str::limit('/'.$request->path(), 500, ''),
            'referrer' => Str::limit((string) $request->headers->get('referer'), 500, ''),
            'source' => $request->session()->get('analytics_utm_source') ?: $this->referralSource($request),
            'medium' => $request->session()->get('analytics_utm_medium'),
            'campaign' => $request->session()->get('analytics_utm_campaign'),
            'device_type' => $client['device'],
            'browser' => $client['browser'],
            'operating_system' => $client['os'],
            'country_code' => $location['country_code'] ?? null,
            'country' => $location['country'] ?? null,
            'region' => $location['region'] ?? null,
            'city' => $location['city'] ?? null,
            'metadata' => $metadata ?: null,
        ]);
    }

    private function referralSource(Request $request): string
    {
        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        return $host && $host !== $request->getHost() ? Str::limit($host, 100, '') : 'direct';
    }

    private function clientDetails(string $userAgent): array
    {
        $agent = strtolower($userAgent);
        $device = preg_match('/ipad|tablet|kindle/', $agent) ? 'Tablet' : (preg_match('/mobile|iphone|android/', $agent) ? 'Mobile' : 'Desktop');
        $browser = match (true) {
            str_contains($agent, 'edg/') => 'Edge',
            str_contains($agent, 'opr/') || str_contains($agent, 'opera') => 'Opera',
            str_contains($agent, 'chrome/') => 'Chrome',
            str_contains($agent, 'firefox/') => 'Firefox',
            str_contains($agent, 'safari/') => 'Safari',
            default => 'Other',
        };
        $os = match (true) {
            str_contains($agent, 'windows') => 'Windows',
            str_contains($agent, 'android') => 'Android',
            str_contains($agent, 'iphone') || str_contains($agent, 'ipad') => 'iOS',
            str_contains($agent, 'mac os') || str_contains($agent, 'macintosh') => 'macOS',
            str_contains($agent, 'linux') => 'Linux',
            default => 'Other',
        };

        return compact('device', 'browser', 'os');
    }

    /**
     * Resolve an approximate location without retaining the visitor's raw IP.
     */
    private function locationDetails(Request $request): array
    {
        if ($request->session()->has('analytics_location')) {
            return (array) $request->session()->get('analytics_location');
        }

        $ip = $request->ip();
        if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return [];
        }

        $cacheKey = 'analytics_location_'.hash_hmac('sha256', $ip, (string) config('app.key'));

        try {
            $location = Cache::remember($cacheKey, now()->addDays(30), function () use ($ip) {
                $response = Http::acceptJson()
                    ->connectTimeout(1)
                    ->timeout(2)
                    ->get('https://ipwho.is/'.rawurlencode($ip), [
                        'fields' => 'success,country_code,country,region,city',
                    ]);

                if (! $response->successful() || ! $response->json('success')) {
                    return [];
                }

                return [
                    'country_code' => Str::upper(Str::limit((string) $response->json('country_code'), 2, '')),
                    'country' => Str::limit((string) $response->json('country'), 100, ''),
                    'region' => Str::limit((string) $response->json('region'), 120, ''),
                    'city' => Str::limit((string) $response->json('city'), 120, ''),
                ];
            });
        } catch (Throwable) {
            $location = [];
        }

        if ($location) {
            $request->session()->put('analytics_location', $location);
        }

        return $location;
    }
}
