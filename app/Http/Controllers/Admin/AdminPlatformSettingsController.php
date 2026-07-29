<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminPlatformSettingsController extends Controller
{
    public function updateAutomaticTradeSync(Request $request): RedirectResponse
    {
        $request->validate([
            'automatic_trade_sync_enabled' => ['required', 'boolean'],
        ]);

        $enabled = $request->boolean('automatic_trade_sync_enabled');

        PlatformSetting::current()->update([
            'automatic_trade_sync_enabled' => $enabled,
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Automatic trade sync is now '.($enabled ? 'ON.' : 'OFF. Manual sync remains available.'));
    }
}
