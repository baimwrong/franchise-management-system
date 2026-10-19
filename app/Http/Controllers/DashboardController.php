<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\RoyaltyInvoice;
use App\Models\SalesTransaction;
use App\Models\SupplyOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $brand = Brand::first();

        // Determine active outlet context
        $selectedOutletId = $request->query('outlet_id')
            ?? $request->session()->get('active_outlet_id')
            ?? ($user->outlet_id ?: null);

        // Staff & Outlet Manager cannot view other outlets
        if (! $user->isSuperAdmin() && ! $user->isFranchiseOwner() && $user->outlet_id) {
            $selectedOutletId = $user->outlet_id;
        }

        // Verify that selected outlet is accessible to this user
        if ($selectedOutletId && ! $user->canManageOutlet((int) $selectedOutletId)) {
            $selectedOutletId = $user->outlet_id ?: null;
        }

        $allOutlets = $user->accessibleOutletsQuery()->orderBy('name')->get();
        $selectedOutlet = $selectedOutletId ? Outlet::find($selectedOutletId) : null;

        // KPI Calculations
        $totalOutlets = $user->accessibleOutletsQuery()->count();
        $activeOutlets = $user->accessibleOutletsQuery()->where('status', 'active')->count();

        $salesQuery = SalesTransaction::query()->where('payment_status', 'paid');
        if ($selectedOutletId) {
            $salesQuery->where('outlet_id', $selectedOutletId);
        } elseif (! $user->isSuperAdmin()) {
            $accessibleIds = $user->accessibleOutletsQuery()->pluck('id');
            $salesQuery->whereIn('outlet_id', $accessibleIds);
        }

        $startOfMonth = now()->startOfMonth();
        $startOfToday = now()->startOfDay();

        $totalSalesMonth = (float) (clone $salesQuery)->where('transaction_date', '>=', $startOfMonth)->sum('total_amount');
        $totalSalesToday = (float) (clone $salesQuery)->where('transaction_date', '>=', $startOfToday)->sum('total_amount');
        $totalSalesAllTime = (float) (clone $salesQuery)->sum('total_amount');

        // Low stock count
        $inventoryQuery = InventoryStock::query()->whereColumn('quantity_on_hand', '<=', 'reorder_threshold');
        if ($selectedOutletId) {
            $inventoryQuery->where('outlet_id', $selectedOutletId);
        } elseif (! $user->isSuperAdmin()) {
            $accessibleIds = $user->accessibleOutletsQuery()->pluck('id');
            $inventoryQuery->whereIn('outlet_id', $accessibleIds);
        }
        $lowStockCount = $inventoryQuery->count();

        // Pending supply orders
        $supplyOrderQuery = SupplyOrder::where('status', 'pending');
        if ($selectedOutletId) {
            $supplyOrderQuery->where('outlet_id', $selectedOutletId);
        } elseif (! $user->isSuperAdmin()) {
            $accessibleIds = $user->accessibleOutletsQuery()->pluck('id');
            $supplyOrderQuery->whereIn('outlet_id', $accessibleIds);
        }
        $pendingSupplyOrders = $supplyOrderQuery->count();

        // Unpaid Royalties
        $royaltyQuery = RoyaltyInvoice::whereIn('status', ['issued', 'overdue']);
        if ($selectedOutletId) {
            $royaltyQuery->where('outlet_id', $selectedOutletId);
        } elseif (! $user->isSuperAdmin()) {
            $accessibleIds = $user->accessibleOutletsQuery()->pluck('id');
            $royaltyQuery->whereIn('outlet_id', $accessibleIds);
        }
        $unpaidRoyaltiesAmount = (float) $royaltyQuery->sum('total_amount_due');

        // Recent sales transactions
        $recentTransactions = (clone $salesQuery)
            ->with(['outlet', 'user'])
            ->latest('transaction_date')
            ->limit(8)
            ->get();

        // Recent supply orders
        $recentSupplyOrders = SupplyOrder::with(['outlet', 'requestedBy'])
            ->when($selectedOutletId, fn ($q) => $q->where('outlet_id', $selectedOutletId))
            ->when(! $selectedOutletId && ! $user->isSuperAdmin(), fn ($q) => $q->whereIn('outlet_id', $user->accessibleOutletsQuery()->pluck('id')))
            ->latest()
            ->limit(6)
            ->get();

        // Outlet Performance Comparison (Top Outlets by Sales this month)
        $outletPerformance = $user->accessibleOutletsQuery()->withSum([
            'salesTransactions' => function ($q) use ($startOfMonth) {
                $q->where('payment_status', 'paid')->where('transaction_date', '>=', $startOfMonth);
            },
        ], 'total_amount')
            ->orderByDesc('sales_transactions_sum_total_amount')
            ->limit(6)
            ->get();

        // Top 5 Products
        $topProducts = Product::with('category')
            ->withSum('salesTransactionItems', 'quantity')
            ->orderByDesc('sales_transaction_items_sum_quantity')
            ->limit(5)
            ->get();

        // Chart Data: Past 7 Days Sales Trend (Aggregated into 1 Query)
        $sevenDaysAgo = now()->subDays(6)->startOfDay();
        $dailySales = (clone $salesQuery)
            ->where('transaction_date', '>=', $sevenDaysAgo)
            ->selectRaw('DATE(transaction_date) as day_date, SUM(total_amount) as day_total')
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->pluck('day_total', 'day_date');

        $chartLabels = [];
        $chartSalesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $chartLabels[] = $day->format('M d');
            $dateKey = $day->format('Y-m-d');
            $chartSalesData[] = (float) ($dailySales[$dateKey] ?? 0.00);
        }

        return view('dashboard.index', compact(
            'brand',
            'user',
            'allOutlets',
            'selectedOutlet',
            'selectedOutletId',
            'totalOutlets',
            'activeOutlets',
            'totalSalesMonth',
            'totalSalesToday',
            'totalSalesAllTime',
            'lowStockCount',
            'pendingSupplyOrders',
            'unpaidRoyaltiesAmount',
            'recentTransactions',
            'recentSupplyOrders',
            'outletPerformance',
            'topProducts',
            'chartLabels',
            'chartSalesData'
        ));
    }

    public function switchOutlet(Request $request): RedirectResponse
    {
        $user = $request->user();
        $outletId = $request->input('outlet_id');

        if ($outletId) {
            if (! $user->canManageOutlet((int) $outletId)) {
                abort(403, 'Unauthorized to switch to this outlet context.');
            }
            $request->session()->put('active_outlet_id', (int) $outletId);
        } else {
            if (! $user->isSuperAdmin() && ! $user->isFranchiseOwner()) {
                abort(403, 'Unauthorized to view consolidated brand context.');
            }
            $request->session()->forget('active_outlet_id');
        }

        return back()->with('status', 'Outlet context updated.');
    }
}
