<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportContribution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminContributionController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status');
        $contributions = SupportContribution::query()
            ->when(in_array($status, ['pending', 'verified', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->latest()->paginate(25)->withQueryString();

        return view('admin.contributions.index', [
            'contributions' => $contributions,
            'status' => $status,
            'pendingCount' => SupportContribution::query()->where('status', 'pending')->count(),
            'verifiedTotal' => SupportContribution::query()->where('status', 'verified')->sum('amount'),
        ]);
    }

    public function update(Request $request, SupportContribution $supportContribution): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'verified', 'rejected'])],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $verified = $data['status'] === 'verified';
        $supportContribution->update([
            ...$data,
            'verified_by' => $verified ? $request->user('admin')->id : null,
            'verified_at' => $verified ? now() : null,
        ]);

        return back()->with('success', 'Contribution status updated.');
    }
}
