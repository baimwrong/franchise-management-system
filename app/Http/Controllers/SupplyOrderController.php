<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\SupplyOrder;
use App\Models\SupplyOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupplyOrderController extends Controller
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

        $query = SupplyOrder::with(['outlet', 'requestedBy', 'processedBy'])
            ->withCount('items');

        if ($selectedOutletId) {
            $query->where('outlet_id', $selectedOutletId);
        } elseif (! $user->isSuperAdmin()) {
            $accessibleIds = $user->accessibleOutletsQuery()->pluck('id');
            $query->whereIn('outlet_id', $accessibleIds);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $outlets = $user->accessibleOutletsQuery()->orderBy('name')->get();

        return view('supply_orders.index', compact('orders', 'outlets', 'selectedOutletId'));
    }

    public function show(Request $request, SupplyOrder $supplyOrder): View
    {
        $user = $request->user();
        if (! $user->canManageOutlet($supplyOrder->outlet_id)) {
            abort(403, 'Unauthorized to view this requisition order.');
        }

        $supplyOrder->load(['outlet', 'requestedBy', 'processedBy', 'items.product']);

        return view('supply_orders.show', compact('supplyOrder'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $outlets = $user->accessibleOutletsQuery()->active()->orderBy('name')->get();

        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('supply_orders.create', compact('outlets', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
        ]);

        $user = $request->user();
        if (! $user->canManageOutlet((int) $validated['outlet_id'])) {
            abort(403, 'Unauthorized to order for this outlet.');
        }

        $order = DB::transaction(function () use ($validated, $user) {
            $orderNumber = 'SO-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));

            $supplyOrder = SupplyOrder::create([
                'outlet_id' => $validated['outlet_id'],
                'requested_by_user_id' => $user->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'total_amount' => 0,
            ]);

            $total = 0;
            foreach ($validated['items'] as $itemData) {
                if (empty($itemData['product_id']) || empty($itemData['quantity'])) {
                    continue;
                }

                $product = Product::findOrFail($itemData['product_id']);
                $qty = (float) $itemData['quantity'];
                $unitCost = (float) $product->cost_price;
                $subtotal = $qty * $unitCost;

                SupplyOrderItem::create([
                    'supply_order_id' => $supplyOrder->id,
                    'product_id' => $product->id,
                    'quantity_requested' => $qty,
                    'quantity_fulfilled' => 0,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $supplyOrder->update(['total_amount' => $total]);

            return $supplyOrder;
        });

        return redirect()->route('supply-orders.show', $order)
            ->with('status', "Requisition Order #{$order->order_number} submitted to Central Headquarters.");
    }

    public function updateStatus(Request $request, SupplyOrder $supplyOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,processing,dispatched,received,cancelled'],
        ]);

        $user = $request->user();
        $targetStatus = $validated['status'];

        // Strict role & state machine authorization
        if (in_array($targetStatus, ['approved', 'processing', 'dispatched'], true)) {
            if (! $user->isSuperAdmin()) {
                abort(403, 'Only Central Headquarters Super Administrators can approve and dispatch supply orders.');
            }
        }

        if ($targetStatus === 'received') {
            if (! $user->canManageOutlet($supplyOrder->outlet_id)) {
                abort(403, 'Unauthorized to confirm receipt for this destination outlet.');
            }
            if ($supplyOrder->status !== 'dispatched') {
                return back()->withErrors(['error' => 'Supply orders can only be marked as received after being dispatched by Headquarters.']);
            }
        }

        if ($targetStatus === 'cancelled') {
            if (! $user->isSuperAdmin() && ! ($user->canManageOutlet($supplyOrder->outlet_id) && $supplyOrder->status === 'pending')) {
                abort(403, 'Orders can only be cancelled while in pending status.');
            }
        }

        if ($supplyOrder->status === 'received') {
            return back()->withErrors(['error' => 'This supply order has already been received and closed.']);
        }

        DB::transaction(function () use ($supplyOrder, $targetStatus, $user) {
            $updates = [
                'status' => $targetStatus,
                'processed_by_user_id' => $user->id,
            ];

            if ($targetStatus === 'approved') {
                $updates['approved_at'] = now();
                foreach ($supplyOrder->items as $item) {
                    $item->update(['quantity_fulfilled' => $item->quantity_requested]);
                }
            } elseif ($targetStatus === 'dispatched') {
                $updates['dispatched_at'] = now();
            } elseif ($targetStatus === 'received') {
                $updates['received_at'] = now();

                // Increment stock on hand at destination outlet with pessimistic row lock
                foreach ($supplyOrder->items as $item) {
                    $stock = InventoryStock::where('outlet_id', $supplyOrder->outlet_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $stock) {
                        $stock = InventoryStock::create([
                            'outlet_id' => $supplyOrder->outlet_id,
                            'product_id' => $item->product_id,
                            'quantity_on_hand' => 0,
                            'reorder_threshold' => 10,
                            'target_stock_level' => 50,
                        ]);
                    }

                    $fulfilledQty = (float) ($item->quantity_fulfilled ?: $item->quantity_requested);
                    $stock->increment('quantity_on_hand', $fulfilledQty);
                    $stock->update(['last_restocked_at' => now()]);
                }
            }

            $supplyOrder->update($updates);
        });

        return back()->with('status', "Supply order #{$supplyOrder->order_number} status changed to {$targetStatus}.");
    }
}
