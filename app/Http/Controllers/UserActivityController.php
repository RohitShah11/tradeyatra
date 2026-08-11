<?php

namespace App\Http\Controllers;

use App\Models\UserActivitySession;
use App\Models\UserPageSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserActivityController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_session' => ['required', 'uuid'],
            'route' => ['nullable', 'string', 'max:150'],
            'path' => ['required', 'string', 'max:500'],
            'visible' => ['required', 'boolean'],
            'idle' => ['required', 'boolean'],
            'elapsed' => ['required', 'integer', 'min:0', 'max:60'],
        ]);

        $user = $request->user();
        $now = now();
        $isActive = $data['visible'] && ! $data['idle'];
        $sessionKey = hash('sha256', $user->id.'|'.$data['client_session']);

        $activity = DB::transaction(function () use ($data, $isActive, $now, $sessionKey, $user) {
            $activity = UserActivitySession::query()->firstOrNew(['session_key' => $sessionKey]);
            if (! $activity->exists) {
                $activity->fill(['user_id' => $user->id, 'started_at' => $now, 'active_seconds' => 0, 'idle_seconds' => 0]);
            }
            $activity->fill([
                'current_route' => $data['route'] ?? null,
                'current_path' => $data['path'],
                'last_seen_at' => $now,
                'ended_at' => null,
            ]);
            $activity->active_seconds += $isActive ? $data['elapsed'] : 0;
            $activity->idle_seconds += $isActive ? 0 : $data['elapsed'];
            if ($isActive) {
                $activity->last_interacted_at = $now;
            }
            $activity->save();

            $page = UserPageSession::query()->where('user_activity_session_id', $activity->id)->whereNull('ended_at')->latest('id')->first();
            if ($page && $page->path !== $data['path']) {
                $page->update(['ended_at' => $now, 'last_seen_at' => $now]);
                $page = null;
            }
            if (! $page) {
                $page = UserPageSession::query()->create([
                    'user_activity_session_id' => $activity->id, 'user_id' => $user->id,
                    'route' => $data['route'] ?? null, 'path' => $data['path'],
                    'started_at' => $now, 'last_seen_at' => $now,
                ]);
            }
            $page->route = $data['route'] ?? null;
            $page->last_seen_at = $now;
            $page->active_seconds += $isActive ? $data['elapsed'] : 0;
            $page->idle_seconds += $isActive ? 0 : $data['elapsed'];
            $page->save();

            return $activity;
        });

        return response()->json(['recorded' => true, 'status' => $activity->presenceStatus()]);
    }
}
