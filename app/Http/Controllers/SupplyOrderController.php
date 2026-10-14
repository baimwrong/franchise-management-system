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

        if (! $user->isSuperAdmin() && ! $user->isFranchiseOwner() && $user->outlet_id) {
            $selectedOutletId = $user->outlet_id;
        }

        $query = SupplyOrder::with(['outlet', 'requestedBy', 'processedBy'])
            ->withCount('items');

        if ($selectedOutletId) {
            $query->where('outlet_id', $selectedOutletId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $outlets = Outlet::orderBy('name')->get();

        return view('supply_orders.index', compact('orders', 'outlets', 'selectedOutletId'));
    }

    public function show(SupplyOrder $supplyOrder): View
    {
        $supplyOrder->load(['outlet', 'requestedBy', 'processedBy', 'items.product']);

        return view('supply_orders.show', compact('supplyOrder'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $outlets = $user->isSuperAdmin()
            ? Outlet::active()->orderBy('name')->get()
            : Outlet::where('id', $user->outlet_id)->get();

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

        DB::transaction(function () use ($supplyOrder, $targetStatus, $user) {
            $updates = [
                'status' => $targetStatus,
                'processed_by_user_id' => $user->id,
            ];

            if ($targetStatus === 'approved') {
                $updates['approved_at'] = now();
                // Fulfill requested quantities by default on approval
                foreach ($supplyOrder->items as $item) {
                    $item->update(['quantity_fulfilled' => $item->quantity_requested]);
                }
            } elseif ($targetStatus === 'dispatched') {
                $updates['dispatched_at'] = now();
            } elseif ($targetStatus === 'received') {
                $updates['received_at'] = now();

                // Increment stock on hand at destination outlet
                foreach ($supplyOrder->items as $item) {
                    $stock = InventoryStock::firstOrCreate(
                        [
                            'outlet_id' => $supplyOrder->outlet_id,
                            'product_id' => $item->product_id,
                        ],
                        [
                            'quantity_on_hand' => 0,
                            'reorder_threshold' => 10,
                            'target_stock_level' => 50,
                        ]
                    );

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
