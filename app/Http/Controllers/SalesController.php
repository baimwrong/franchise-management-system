<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\SalesTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesController extends Controller
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

        $query = SalesTransaction::with(['outlet', 'user'])->withCount('items');

        if ($selectedOutletId) {
            $query->where('outlet_id', $selectedOutletId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        $transactions = $query->latest('transaction_date')->paginate(15)->withQueryString();
        $outlets = Outlet::orderBy('name')->get();

        $periodSalesTotal = (float) (clone $query)->where('payment_status', 'paid')->sum('total_amount');

        return view('sales.index', compact('transactions', 'outlets', 'selectedOutletId', 'periodSalesTotal'));
    }

    public function show(SalesTransaction $salesTransaction): View
    {
        $salesTransaction->load(['outlet', 'user', 'items.product']);

        return view('sales.show', compact('salesTransaction'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $outlets = $user->isSuperAdmin()
            ? Outlet::active()->orderBy('name')->get()
            : Outlet::where('id', $user->outlet_id)->get();

        $currentOutletId = $request->query('outlet_id') ?? $user->outlet_id ?? $outlets->first()?->id;
        $currentOutlet = Outlet::find($currentOutletId);

        $products = Product::where('is_active', true)->with('category')->orderBy('name')->get();

        return view('sales.create', compact('outlets', 'currentOutlet', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['required', 'string', 'in:cash,credit_card,debit_card,qris,bank_transfer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
        ]);

        $user = $request->user();
        if (! $user->canManageOutlet((int) $validated['outlet_id'])) {
            abort(403, 'Unauthorized to record sales for this outlet.');
        }

        $outlet = Outlet::findOrFail($validated['outlet_id']);

        $transaction = DB::transaction(function () use ($validated, $user, $outlet) {
            $trxCode = 'TRX-'.$outlet->outlet_code.'-'.now()->format('YmdHis');

            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($validated['items'] as $itemData) {
                if (empty($itemData['product_id']) || empty($itemData['quantity'])) {
                    continue;
                }

                $product = Product::findOrFail($itemData['product_id']);
                $qty = (float) $itemData['quantity'];
                $price = (float) $product->suggested_retail_price;
                $lineSubtotal = $qty * $price;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $lineSubtotal,
                ];

                $subtotal += $lineSubtotal;

                // Deplete outlet inventory stock if tracked
                if ($product->is_track_inventory) {
                    $stock = InventoryStock::firstOrCreate(
                        ['outlet_id' => $outlet->id, 'product_id' => $product->id],
                        ['quantity_on_hand' => 0, 'reorder_threshold' => 10, 'target_stock_level' => 50]
                    );
                    $stock->decrement('quantity_on_hand', $qty);
                }
            }

            $taxRate = (float) ($outlet->tax_rate_percentage ?? 10.00);
            $taxAmount = round(($subtotal * $taxRate) / 100, 2);
            $totalAmount = $subtotal + $taxAmount;

            $salesTransaction = SalesTransaction::create([
                'outlet_id' => $outlet->id,
                'user_id' => $user->id,
                'transaction_code' => $trxCode,
                'customer_name' => $validated['customer_name'] ?? 'Walk-in Guest',
                'customer_phone' => $validated['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => 0.00,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'paid',
                'transaction_date' => now(),
            ]);

            foreach ($itemsToCreate as $item) {
                $salesTransaction->items()->create($item);
            }

            return $salesTransaction;
        });

        return redirect()->route('sales.show', $transaction)
            ->with('status', "Sale completed successfully. Transaction #{$transaction->transaction_code}");
    }
}
