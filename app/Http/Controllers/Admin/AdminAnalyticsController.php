<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $days = min(365, max(7, (int) $request->query('days', 30)));
        $period = in_array($request->query('period'), ['daily', 'weekly', 'monthly'], true) ? $request->query('period') : 'daily';
        $from = now()->subDays($days - 1)->startOfDay();
        $base = AnalyticsEvent::query()->where('created_at', '>=', $from);
        $visitors = (clone $base)->where('event', 'page_view')->distinct('visitor_id')->count('visitor_id');
        $registrations = (clone $base)->where('event', 'registration_completed')->count();
        $connections = (clone $base)->where('event', 'broker_connection_success')->distinct('user_id')->count('user_id');

        $rawTrend = (clone $base)->where('event', 'page_view')
            ->selectRaw('DATE(created_at) as day, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors')
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');
        $dailyTrend = collect(range(0, $days - 1))->map(function ($offset) use ($from, $rawTrend) {
            $day = $from->copy()->addDays($offset)->toDateString();

            return ['day' => $day, 'label' => Carbon::parse($day)->format('d M'), 'views' => (int) ($rawTrend[$day]->views ?? 0), 'visitors' => (int) ($rawTrend[$day]->visitors ?? 0)];
        });
        $trend = $this->groupTrend($dailyTrend, $period);
        $brokerEvents = (clone $base)->whereIn('event', ['broker_connection_success', 'broker_connection_failed'])->get();
        $brokerStats = collect(['shark', 'delta'])->mapWithKeys(function ($broker) use ($brokerEvents) {
            $events = $brokerEvents->filter(fn ($event) => ($event->metadata['broker'] ?? null) === $broker);
            $success = $events->where('event', 'broker_connection_success')->count();
            $failed = $events->where('event', 'broker_connection_failed')->count();

            return [$broker => ['success' => $success, 'failed' => $failed, 'rate' => ($success + $failed) ? round($success / ($success + $failed) * 100, 1) : 0]];
        });
        $campaignEvents = (clone $base)->whereNotNull('campaign')->get(['campaign', 'event', 'visitor_id']);
        $campaigns = $campaignEvents->groupBy('campaign')->map(function ($events, $campaign) {
            return (object) [
                'campaign' => $campaign,
                'visitors' => $events->where('event', 'page_view')->pluck('visitor_id')->filter()->unique()->count(),
                'registrations' => $events->where('event', 'registration_completed')->count(),
                'connections' => $events->where('event', 'broker_connection_success')->count(),
            ];
        })->sortByDesc('visitors')->take(10);

        return view('admin.analytics', [
            'days' => $days,
            'period' => $period,
            'stats' => [
                'visitors' => $visitors,
                'views' => (clone $base)->where('event', 'page_view')->count(),
                'registrations' => $registrations,
                'connections' => $connections,
                'failedConnections' => $brokerEvents->where('event', 'broker_connection_failed')->count(),
                'registrationRate' => $visitors ? round($registrations / $visitors * 100, 1) : 0,
                'connectionRate' => $registrations ? round($connections / $registrations * 100, 1) : 0,
            ],
            'trend' => $trend,
            'maxViews' => max(1, (int) $trend->max('views')),
            'brokerStats' => $brokerStats,
            'campaigns' => $campaigns,
            'sources' => (clone $base)->where('event', 'page_view')->selectRaw("COALESCE(source, 'direct') as source_name, COUNT(DISTINCT visitor_id) as visitors")->groupBy('source_name')->orderByDesc('visitors')->limit(8)->get(),
            'pages' => (clone $base)->where('event', 'page_view')->selectRaw('path, COUNT(*) as views')->groupBy('path')->orderByDesc('views')->limit(8)->get(),
            'devices' => $this->dimensionSummary(clone $base, 'device_type'),
            'browsers' => $this->dimensionSummary(clone $base, 'browser'),
            'operatingSystems' => $this->dimensionSummary(clone $base, 'operating_system'),
            'indiaLocations' => (clone $base)->where('event', 'page_view')
                ->where('country_code', 'IN')
                ->whereNotNull('region')
                ->selectRaw("region, COALESCE(NULLIF(city, ''), 'Unknown city') as city_name, COUNT(DISTINCT visitor_id) as visitors, COUNT(*) as views")
                ->groupBy('region', 'city_name')
                ->orderByDesc('visitors')
                ->limit(20)
                ->get(),
            'recentEvents' => (clone $base)->latest()->limit(25)->get(),
        ]);
    }

    private function groupTrend($dailyTrend, string $period)
    {
        if ($period === 'daily') {
            return $dailyTrend;
        }

        return $dailyTrend->groupBy(function ($point) use ($period) {
            $date = Carbon::parse($point['day']);

            return $period === 'weekly' ? $date->startOfWeek()->toDateString() : $date->startOfMonth()->toDateString();
        })->map(function ($points, $day) use ($period) {
            return [
                'day' => $day,
                'label' => $period === 'weekly' ? 'W/C '.Carbon::parse($day)->format('d M') : Carbon::parse($day)->format('M Y'),
                'views' => $points->sum('views'),
                'visitors' => $points->sum('visitors'),
            ];
        })->values();
    }

    private function dimensionSummary($query, string $column)
    {
        return $query->where('event', 'page_view')->whereNotNull($column)
            ->selectRaw("{$column} as label, COUNT(DISTINCT visitor_id) as visitors")
            ->groupBy($column)->orderByDesc('visitors')->limit(8)->get();
    }
}
