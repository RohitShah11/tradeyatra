<?php

namespace App\Http\Controllers;

use App\Models\SupportContribution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportContributionController extends Controller
{
    public function index(): View
    {
        $contributions = SupportContribution::query()
            ->where('status', 'verified')
            ->latest('verified_at')
            ->limit(100)
            ->get();
        $highestAmount = $contributions->max('amount');

        return view('support-fund.index', [
            'contributions' => $contributions,
            'highestAmount' => $highestAmount,
            'totalRaised' => SupportContribution::query()->where('status', 'verified')->sum('amount'),
            'supporterCount' => SupportContribution::query()->where('status', 'verified')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'contributor_name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:254'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]{7,25}$/'],
            'amount' => ['required', 'numeric', 'min:1', 'max:50000'],
            'transaction_reference' => ['required', 'string', 'min:6', 'max:80', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('support_contributions')],
            'message' => ['nullable', 'string', 'max:500'],
            'show_publicly' => ['nullable', 'boolean'],
            'anonymous' => ['nullable', 'boolean'],
        ]);

        SupportContribution::query()->create([
            ...$data,
            'user_id' => $request->user()?->id,
            'show_publicly' => $request->boolean('show_publicly'),
            'anonymous' => $request->boolean('anonymous'),
            'status' => 'pending',
        ]);

        return redirect()->route('support-fund.index')->with('success', 'Contribution details submitted for verification. Thank you for supporting TradeYatra.');
    }
}
