<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $selectedOutletId = $request->input('outlet_id')
            ?? $request->session()->get('active_outlet_id')
            ?? ($user->outlet_id ?: null);

        if (! $user->isSuperAdmin() && ! $user->isFranchiseOwner() && $user->outlet_id) {
            $selectedOutletId = $user->outlet_id;
        }

        $query = InventoryStock::with(['outlet', 'product.category']);

        if ($selectedOutletId) {
            $query->where('outlet_id', $selectedOutletId);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('low_stock_only')) {
            $query->whereColumn('quantity_on_hand', '<=', 'reorder_threshold');
        }

        $stocks = $query->orderBy('quantity_on_hand', 'asc')->paginate(20)->withQueryString();
        $outlets = Outlet::orderBy('name')->get();
        $selectedOutlet = $selectedOutletId ? Outlet::find($selectedOutletId) : null;

        $lowStockTotal = (clone $query)->whereColumn('quantity_on_hand', '<=', 'reorder_threshold')->count();

        return view('inventory.index', compact(
            'stocks',
            'outlets',
            'selectedOutlet',
            'selectedOutletId',
            'lowStockTotal'
        ));
    }

    public function adjustStock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'product_id' => ['required', 'exists:products,id'],
            'adjustment_type' => ['required', 'string', 'in:damage,waste,audit_correction,initial_stock,return'],
            'adjustment_quantity' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        if (! $user->canManageOutlet((int) $validated['outlet_id'])) {
            abort(403, 'Unauthorized to adjust stock for this outlet.');
        }

        DB::transaction(function () use ($validated, $user) {
            $stock = InventoryStock::firstOrCreate(
                [
                    'outlet_id' => $validated['outlet_id'],
                    'product_id' => $validated['product_id'],
                ],
                [
                    'quantity_on_hand' => 0,
                    'reorder_threshold' => 10,
                    'target_stock_level' => 50,
                ]
            );

            $previousQty = (float) $stock->quantity_on_hand;
            $adjQty = (float) $validated['adjustment_quantity'];
            $newQty = max(0, $previousQty + $adjQty);

            $stock->update([
                'quantity_on_hand' => $newQty,
                'last_restocked_at' => $adjQty > 0 ? now() : $stock->last_restocked_at,
            ]);

            $adjustment = StockAdjustment::create([
                'outlet_id' => $validated['outlet_id'],
                'user_id' => $user->id,
                'reference_code' => 'ADJ-'.strtoupper(Str::random(8)),
                'type' => $validated['adjustment_type'],
                'reason' => $validated['reason'] ?? null,
                'adjustment_date' => now()->toDateString(),
            ]);

            StockAdjustmentItem::create([
                'stock_adjustment_id' => $adjustment->id,
                'product_id' => $validated['product_id'],
                'previous_quantity' => $previousQty,
                'adjustment_quantity' => $adjQty,
                'new_quantity' => $newQty,
            ]);
        });

        return back()->with('status', 'Stock quantity adjusted and audit logged successfully.');
    }

    public function adjustmentsHistory(Request $request): View
    {
        $user = $request->user();
        $selectedOutletId = $request->input('outlet_id')
            ?? $request->session()->get('active_outlet_id')
            ?? ($user->outlet_id ?: null);

        $adjustments = StockAdjustment::with(['outlet', 'user', 'items.product'])
            ->when($selectedOutletId, fn ($q) => $q->where('outlet_id', $selectedOutletId))
            ->latest('adjustment_date')
            ->paginate(15);

        $outlets = Outlet::orderBy('name')->get();

        return view('inventory.adjustments', compact('adjustments', 'outlets', 'selectedOutletId'));
    }
}
