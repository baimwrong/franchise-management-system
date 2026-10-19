<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Outlet;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutletController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $user->accessibleOutletsQuery()->with(['region', 'brand'])
            ->withCount(['inventoryStocks', 'users']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('outlet_code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('franchisee_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($regionId = $request->input('region_id')) {
            $query->where('region_id', $regionId);
        }

        $outlets = $query->orderBy('name')->paginate(12)->withQueryString();
        $regions = Region::orderBy('name')->get();

        return view('outlets.index', compact('outlets', 'regions'));
    }

    public function show(Request $request, Outlet $outlet): View
    {
        $user = $request->user();
        if (! $user->canManageOutlet($outlet->id)) {
            abort(403, 'Unauthorized to view this store outlet.');
        }

        $outlet->load(['region', 'brand', 'users']);

        $recentSales = $outlet->salesTransactions()->latest('transaction_date')->limit(10)->get();
        $recentSupplyOrders = $outlet->supplyOrders()->with('requestedBy')->latest()->limit(5)->get();
        $lowStocks = $outlet->inventoryStocks()->with('product')->whereColumn('quantity_on_hand', '<=', 'reorder_threshold')->get();
        $recentAudits = $outlet->audits()->with('auditor')->latest('audit_date')->limit(5)->get();
        $royaltyInvoices = $outlet->royaltyInvoices()->latest('period_start')->limit(6)->get();

        $monthGrossSales = (float) $outlet->salesTransactions()
            ->where('payment_status', 'paid')
            ->where('transaction_date', '>=', now()->startOfMonth())
            ->sum('total_amount');

        return view('outlets.show', compact(
            'outlet',
            'recentSales',
            'recentSupplyOrders',
            'lowStocks',
            'recentAudits',
            'royaltyInvoices',
            'monthGrossSales'
        ));
    }

    public function create(): View
    {
        $regions = Region::orderBy('name')->get();
        $brand = Brand::first();

        return view('outlets.create', compact('regions', 'brand'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'outlet_code' => ['required', 'string', 'max:50', 'unique:outlets,outlet_code'],
            'franchisee_name' => ['required', 'string', 'max:255'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'type' => ['required', 'string', 'in:franchise,company_owned,kiosk,express'],
            'status' => ['required', 'string', 'in:active,pending_approval,renovation,suspended,closed'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'state_province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'tax_rate_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'opening_date' => ['nullable', 'date'],
        ]);

        $brand = Brand::first();
        $validated['brand_id'] = $brand?->id ?? 1;

        $outlet = Outlet::create($validated);

        return redirect()->route('outlets.show', $outlet)
            ->with('status', "Outlet '{$outlet->name}' has been created successfully.");
    }

    public function edit(Request $request, Outlet $outlet): View
    {
        $user = $request->user();
        if (! $user->canManageOutlet($outlet->id)) {
            abort(403, 'Unauthorized to edit this store outlet.');
        }

        $regions = Region::orderBy('name')->get();

        return view('outlets.edit', compact('outlet', 'regions'));
    }

    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $request->user();
        if (! $user->canManageOutlet($outlet->id)) {
            abort(403, 'Unauthorized to update this store outlet.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'outlet_code' => ['required', 'string', 'max:50', 'unique:outlets,outlet_code,'.$outlet->id],
            'franchisee_name' => ['required', 'string', 'max:255'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'type' => ['required', 'string', 'in:franchise,company_owned,kiosk,express'],
            'status' => ['required', 'string', 'in:active,pending_approval,renovation,suspended,closed'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'state_province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'tax_rate_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'opening_date' => ['nullable', 'date'],
        ]);

        $outlet->update($validated);

        return redirect()->route('outlets.show', $outlet)
            ->with('status', "Outlet '{$outlet->name}' has been updated.");
    }

    public function destroy(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isSuperAdmin()) {
            abort(403, 'Only Corporate Super Administrators can delete outlets.');
        }

        $name = $outlet->name;
        $outlet->delete();

        return redirect()->route('outlets.index')
            ->with('status', "Outlet '{$name}' was deleted successfully.");
    }
}
