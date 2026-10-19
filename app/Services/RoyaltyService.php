<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Outlet;
use App\Models\RoyaltyInvoice;
use App\Models\SalesTransaction;
use Illuminate\Support\Carbon;

class RoyaltyService
{
    /**
     * Generate royalty invoice for an outlet for a specific period.
     */
    public function generateInvoice(Outlet $outlet, Carbon $periodStart, Carbon $periodEnd): RoyaltyInvoice
    {
        $brand = $outlet->brand ?? Brand::first();
        $royaltyPercent = (float) ($brand->royalty_rate_percentage ?? 5.00);
        $marketingPercent = (float) ($brand->marketing_fund_percentage ?? 2.00);

        // Sum paid sales transactions in the period for this outlet (copy dates to prevent mutating arguments)
        $grossSales = (float) SalesTransaction::where('outlet_id', $outlet->id)
            ->where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()])
            ->sum('total_amount');

        $royaltyFee = round(($grossSales * $royaltyPercent) / 100, 2);
        $marketingFee = round(($grossSales * $marketingPercent) / 100, 2);
        $techFee = (float) ($brand->settings['monthly_portal_tech_fee'] ?? 50.00);
        $totalDue = $royaltyFee + $marketingFee + $techFee;

        $invoiceNumber = 'ROY-'.$outlet->outlet_code.'-'.$periodStart->format('Ym');

        $existing = RoyaltyInvoice::where('invoice_number', $invoiceNumber)->first();
        if ($existing && $existing->status === 'paid') {
            return $existing;
        }

        $invoiceData = [
            'outlet_id' => $outlet->id,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'gross_sales' => $grossSales,
            'royalty_percentage' => $royaltyPercent,
            'royalty_fee' => $royaltyFee,
            'marketing_fee' => $marketingFee,
            'technology_fee' => $techFee,
            'total_amount_due' => $totalDue,
            'due_date' => $periodEnd->copy()->addDays(15)->toDateString(),
        ];

        if (! $existing) {
            $invoiceData['status'] = 'issued';
        }

        return RoyaltyInvoice::updateOrCreate(
            ['invoice_number' => $invoiceNumber],
            $invoiceData
        );
    }

    /**
     * Generate royalty invoices for all active franchise outlets for previous month.
     */
    public function generateMonthlyBillingForAll(?Carbon $date = null): int
    {
        $targetDate = $date ?? now()->subMonth();
        $start = $targetDate->copy()->startOfMonth();
        $end = $targetDate->copy()->endOfMonth();

        $outlets = Outlet::active()->where('type', 'franchise')->get();
        $count = 0;

        foreach ($outlets as $outlet) {
            $this->generateInvoice($outlet, $start, $end);
            $count++;
        }

        return $count;
    }
}
