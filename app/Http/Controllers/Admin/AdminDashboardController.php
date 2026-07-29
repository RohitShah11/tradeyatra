<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\PlatformSetting;
use App\Models\Trade;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'newUsers' => User::where('created_at', '>=', now()->subDays(30))->count(),
                'trades' => Trade::count(),
                'messages' => ContactMessage::whereIn('status', ['new', 'in_progress'])->count(),
            ],
            'recentUsers' => User::latest()->limit(6)->get(),
            'recentMessages' => ContactMessage::latest()->limit(6)->get(),
            'automaticTradeSyncEnabled' => PlatformSetting::automaticTradeSyncEnabled(),
        ]);
    }
}
