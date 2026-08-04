<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnalyticsEventController extends Controller
{
    private const EVENTS = [
        'registration_cta_clicked',
        'registration_form_started',
        'broker_setup_started',
    ];

    public function store(Request $request, AnalyticsTracker $analytics): JsonResponse
    {
        $data = $request->validate([
            'event' => ['required', 'string', Rule::in(self::EVENTS)],
            'metadata' => ['sometimes', 'array'],
            'metadata.cta' => ['sometimes', 'string', 'max:80'],
            'metadata.placement' => ['sometimes', 'string', 'max:80'],
            'metadata.broker' => ['sometimes', Rule::in(['shark', 'delta'])],
        ]);

        $analytics->track($request, $data['event'], $data['metadata'] ?? []);

        return response()->json(['recorded' => true], 201);
    }
}
