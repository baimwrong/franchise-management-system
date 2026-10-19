<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Outlet;
use App\Models\OutletAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $selectedOutletId = $request->input('outlet_id')
            ?? $request->session()->get('active_outlet_id')
            ?? ($user->outlet_id ?: null);

        if ($selectedOutletId && ! $user->canManageOutlet((int) $selectedOutletId)) {
            $selectedOutletId = $user->outlet_id ?: null;
        }

        $query = OutletAudit::with(['outlet', 'auditor']);

        if ($selectedOutletId) {
            $query->where('outlet_id', $selectedOutletId);
        } elseif (! $user->isSuperAdmin()) {
            $accessibleIds = $user->accessibleOutletsQuery()->pluck('id');
            $query->whereIn('outlet_id', $accessibleIds);
        }

        $audits = $query->latest('audit_date')->paginate(15)->withQueryString();
        $outlets = $user->accessibleOutletsQuery()->orderBy('name')->get();

        $averageAuditScore = (float) (clone $query)->avg('overall_score');

        return view('audits.index', compact('audits', 'outlets', 'selectedOutletId', 'averageAuditScore'));
    }

    public function show(Request $request, OutletAudit $outletAudit): View
    {
        $user = $request->user();
        if (! $user->canManageOutlet($outletAudit->outlet_id)) {
            abort(403, 'Unauthorized to view audits for this outlet.');
        }

        $outletAudit->load(['outlet', 'auditor']);

        return view('audits.show', compact('outletAudit'));
    }

    public function create(): View
    {
        $outlets = Outlet::active()->orderBy('name')->get();

        return view('audits.create', compact('outlets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'audit_date' => ['required', 'date'],
            'food_safety_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'cleanliness_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'brand_standards_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'customer_service_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'findings' => ['nullable', 'string'],
            'corrective_actions' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $overall = round((
            $validated['food_safety_score'] +
            $validated['cleanliness_score'] +
            $validated['brand_standards_score'] +
            $validated['customer_service_score']
        ) / 4, 2);

        $brand = Brand::first();
        $passingThreshold = (float) ($brand->settings['minimum_audit_passing_score'] ?? 75.00);
        $passed = $overall >= $passingThreshold;

        $audit = OutletAudit::create([
            'outlet_id' => $validated['outlet_id'],
            'auditor_id' => $request->user()->id,
            'audit_date' => $validated['audit_date'],
            'food_safety_score' => $validated['food_safety_score'],
            'cleanliness_score' => $validated['cleanliness_score'],
            'brand_standards_score' => $validated['brand_standards_score'],
            'customer_service_score' => $validated['customer_service_score'],
            'overall_score' => $overall,
            'passed' => $passed,
            'findings' => $validated['findings'] ?? null,
            'corrective_actions' => $validated['corrective_actions'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);

        return redirect()->route('audits.show', $audit)
            ->with('status', "Quality Audit recorded with score of {$overall}%.");
    }
}
