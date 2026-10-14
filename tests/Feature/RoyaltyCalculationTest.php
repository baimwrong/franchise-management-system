<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Outlet;
use App\Models\RoyaltyInvoice;
use App\Models\SalesTransaction;
use App\Models\User;
use App\Services\RoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoyaltyCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Outlet $outlet;

    protected Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brand = Brand::create([
            'name' => 'OmniBrew Coffee & Bakery',
            'code' => 'OMNI',
            'contact_email' => 'hq@omnifran.local',
            'contact_phone' => '+1-555-0000',
            'royalty_rate_percentage' => 5.00,
            'marketing_fund_percentage' => 2.00,
        ]);

        $this->outlet = Outlet::create([
            'brand_id' => $this->brand->id,
            'name' => 'OmniBrew Franchise Store #1',
            'outlet_code' => 'OMNI-FRAN-001',
            'franchisee_name' => 'Apex Licensee LLC',
            'type' => 'franchise',
            'status' => 'active',
            'phone' => '+1-555-0101',
            'email' => 'fran1@omnifran.local',
            'address' => '100 Main St',
            'city' => 'New York',
            'tax_rate_percentage' => 10.00,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_royalty_service_calculates_fees_accurately(): void
    {
        // Record 2 sales transactions for this outlet in September
        SalesTransaction::create([
            'outlet_id' => $this->outlet->id,
            'transaction_code' => 'TRX-SEP-001',
            'subtotal' => 5000.00,
            'tax_amount' => 500.00,
            'total_amount' => 5500.00,
            'payment_status' => 'paid',
            'transaction_date' => '2026-09-10 12:00:00',
        ]);

        SalesTransaction::create([
            'outlet_id' => $this->outlet->id,
            'transaction_code' => 'TRX-SEP-002',
            'subtotal' => 4500.00,
            'tax_amount' => 450.00,
            'total_amount' => 4500.00,
            'payment_status' => 'paid',
            'transaction_date' => '2026-09-20 15:00:00',
        ]);

        $service = app(RoyaltyService::class);
        $start = now()->setYear(2026)->setMonth(9)->startOfMonth();
        $end = now()->setYear(2026)->setMonth(9)->endOfMonth();

        $invoice = $service->generateInvoice($this->outlet, $start, $end);

        // Gross total = $5500 + $4500 = $10,000.00
        $this->assertEquals(10000.00, (float) $invoice->gross_sales);
        // Royalty @ 5% = $500.00
        $this->assertEquals(500.00, (float) $invoice->royalty_fee);
        // Marketing @ 2% = $200.00
        $this->assertEquals(200.00, (float) $invoice->marketing_fee);
        // Tech fee = $50.00, Total = $500 + $200 + $50 = $750.00
        $this->assertEquals(750.00, (float) $invoice->total_amount_due);
    }

    public function test_admin_can_mark_royalty_invoice_as_paid(): void
    {
        $invoice = RoyaltyInvoice::create([
            'outlet_id' => $this->outlet->id,
            'invoice_number' => 'ROY-OMNI-FRAN-001-202609',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'gross_sales' => 10000.00,
            'royalty_percentage' => 5.00,
            'royalty_fee' => 500.00,
            'marketing_fee' => 200.00,
            'technology_fee' => 50.00,
            'total_amount_due' => 750.00,
            'status' => 'issued',
            'due_date' => '2026-10-15',
        ]);

        $response = $this->actingAs($this->admin)->patch("/royalties/{$invoice->id}/paid", [
            'payment_reference' => 'WIRE-ACH-123456',
            'notes' => 'Settled via wire',
        ]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals('WIRE-ACH-123456', $invoice->payment_reference);
        $this->assertNotNull($invoice->paid_at);
    }
}
