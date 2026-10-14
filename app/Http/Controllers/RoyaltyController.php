<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Outlet;
use App\Models\RoyaltyInvoice;
use App\Services\RoyaltyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RoyaltyController extends Controller
{
    public function __construct(
        protected RoyaltyService $royaltyService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $selectedOutletId = $request->input('outlet_id')
            ?? $request->session()->get('active_outlet_id')
            ?? ($user->outlet_id ?: null);

        if (! $user->isSuperAdmin() && ! $user->isFranchiseOwner() && $user->outlet_id) {
            $selectedOutletId = $user->outlet_id;
        }

        $query = RoyaltyInvoice::with('outlet');

        if ($selectedOutletId) {
            $query->where('outlet_id', $selectedOutletId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->latest('period_start')->paginate(15)->withQueryString();
        $outlets = Outlet::where('type', 'franchise')->orderBy('name')->get();
        $brand = Brand::first();

        $totalRoyaltiesDue = (float) (clone $query)->whereIn('status', ['issued', 'overdue'])->sum('total_amount_due');
        $totalRoyaltiesCollected = (float) (clone $query)->where('status', 'paid')->sum('total_amount_due');

        return view('royalties.index', compact(
            'invoices',
            'outlets',
            'selectedOutletId',
            'brand',
            'totalRoyaltiesDue',
            'totalRoyaltiesCollected'
        ));
    }

    public function show(RoyaltyInvoice $royaltyInvoice): View
    {
        $royaltyInvoice->load('outlet.brand');

        return view('royalties.show', compact('royaltyInvoice'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $date = Carbon::createFromFormat('Y-m', $validated['month']);
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        if (! empty($validated['outlet_id'])) {
            $outlet = Outlet::findOrFail($validated['outlet_id']);
            $invoice = $this->royaltyService->generateInvoice($outlet, $start, $end);

            return redirect()->route('royalties.show', $invoice)
                ->with('status', "Royalty invoice generated for {$outlet->name} ({$validated['month']}).");
        }

        $count = $this->royaltyService->generateMonthlyBillingForAll($date);

        return redirect()->route('royalties.index')
            ->with('status', "Generated {$count} royalty billing invoices for period {$validated['month']}.");
    }

    public function markAsPaid(Request $request, RoyaltyInvoice $royaltyInvoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_reference' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $royaltyInvoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $validated['payment_reference'],
            'notes' => $validated['notes'] ?? $royaltyInvoice->notes,
        ]);

        return back()->with('status', "Invoice #{$royaltyInvoice->invoice_number} marked as paid.");
    }
}
