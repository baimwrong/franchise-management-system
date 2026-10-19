<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\OutletProduct;
use App\Models\Product;
use App\Models\RoyaltyInvoice;
use App\Models\SupplyOrder;
use App\Models\SupplyOrderItem;
use App\Models\User;
use App\Services\RoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AuthorizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Brand $brand;

    protected Outlet $outletA;

    protected Outlet $outletB;

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
            'technology_fee_monthly' => 50.00,
        ]);

        $this->outletA = Outlet::create([
            'brand_id' => $this->brand->id,
            'name' => 'OmniBrew Downtown',
            'outlet_code' => 'OMNI-DWT-001',
            'franchisee_name' => 'Corporate Direct',
            'type' => 'company_owned',
            'status' => 'active',
            'phone' => '+1-555-0101',
            'email' => 'downtown@omnifran.local',
            'address' => '100 Main St',
            'city' => 'New York',
            'tax_rate_percentage' => 10.00,
        ]);

        $this->outletB = Outlet::create([
            'brand_id' => $this->brand->id,
            'name' => 'OmniBrew Uptown',
            'outlet_code' => 'OMNI-UPT-002',
            'franchisee_name' => 'Apex Licensee',
            'type' => 'franchise',
            'status' => 'active',
            'phone' => '+1-555-0102',
            'email' => 'uptown@omnifran.local',
            'address' => '200 Uptown Blvd',
            'city' => 'New York',
            'tax_rate_percentage' => 10.00,
        ]);
    }

    public function test_unauthorized_user_cannot_switch_to_another_outlet_context(): void
    {
        $managerA = User::factory()->create([
            'role' => 'outlet_manager',
            'outlet_id' => $this->outletA->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($managerA)->post('/dashboard/switch-outlet', [
            'outlet_id' => $this->outletB->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_outlet_manager_cannot_elevate_user_role_to_super_admin(): void
    {
        $managerA = User::factory()->create([
            'role' => 'outlet_manager',
            'outlet_id' => $this->outletA->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($managerA)->post('/users', [
            'name' => 'Malicious User',
            'email' => 'hacker@omnifran.local',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'role' => 'super_admin',
            'outlet_id' => $this->outletA->id,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', ['email' => 'hacker@omnifran.local']);
    }

    public function test_outlet_manager_cannot_assign_user_to_another_outlet(): void
    {
        $managerA = User::factory()->create([
            'role' => 'outlet_manager',
            'outlet_id' => $this->outletA->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($managerA)->post('/users', [
            'name' => 'Staff In Other Outlet',
            'email' => 'staffother@omnifran.local',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'role' => 'staff',
            'outlet_id' => $this->outletB->id,
            'is_active' => true,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'staffother@omnifran.local']);
    }

    public function test_staff_cannot_create_or_delete_outlets(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'outlet_id' => $this->outletA->id,
            'is_active' => true,
        ]);

        $postResponse = $this->actingAs($staff)->post('/outlets', [
            'brand_id' => $this->brand->id,
            'name' => 'Unauthorized Outlet',
            'outlet_code' => 'OMNI-UNAUTH-999',
            'franchisee_name' => 'Rogue',
            'type' => 'franchise',
            'status' => 'active',
            'address' => 'Nowhere',
            'city' => 'Nowhere',
        ]);
        $postResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($staff)->delete("/outlets/{$this->outletA->id}");
        $deleteResponse->assertStatus(403);
    }

    public function test_staff_cannot_create_products(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'outlet_id' => $this->outletA->id,
            'is_active' => true,
        ]);

        $category = Category::create([
            'brand_id' => $this->brand->id,
            'name' => 'Pastries',
            'slug' => 'pastries',
        ]);

        $response = $this->actingAs($staff)->post('/products', [
            'brand_id' => $this->brand->id,
            'category_id' => $category->id,
            'sku' => 'ROGUE-001',
            'name' => 'Rogue Croissant',
            'unit_of_measure' => 'piece',
            'cost_price' => 1.00,
            'suggested_retail_price' => 3.50,
        ]);

        $response->assertStatus(403);
    }

    public function test_paid_royalty_invoice_is_not_overwritten_by_regeneration(): void
    {
        $start = Carbon::create(2026, 8, 1, 0, 0, 0);
        $end = Carbon::create(2026, 8, 31, 23, 59, 59);

        $invoice = RoyaltyInvoice::create([
            'outlet_id' => $this->outletB->id,
            'invoice_number' => 'ROY-OMNI-UPT-002-202608',
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'gross_sales' => 8000.00,
            'royalty_percentage' => 5.00,
            'royalty_fee' => 400.00,
            'marketing_fee' => 160.00,
            'technology_fee' => 50.00,
            'total_amount_due' => 610.00,
            'status' => 'paid',
            'payment_reference' => 'PREV-PAID-REF-001',
            'paid_at' => now(),
            'due_date' => '2026-09-15',
        ]);

        $service = app(RoyaltyService::class);
        $regenerated = $service->generateInvoice($this->outletB, $start, $end);

        $this->assertEquals('paid', $regenerated->status);
        $this->assertEquals('PREV-PAID-REF-001', $regenerated->payment_reference);
        $this->assertEquals(610.00, (float) $regenerated->total_amount_due);
    }

    public function test_supply_order_cannot_be_received_directly_from_pending_or_approved(): void
    {
        $manager = User::factory()->create([
            'role' => 'outlet_manager',
            'outlet_id' => $this->outletA->id,
            'is_active' => true,
        ]);

        $category = Category::create([
            'brand_id' => $this->brand->id,
            'name' => 'Supplies',
            'slug' => 'supplies',
        ]);

        $product = Product::create([
            'brand_id' => $this->brand->id,
            'category_id' => $category->id,
            'sku' => 'SUP-BEV-001',
            'name' => 'Coffee Beans 1kg',
            'unit_of_measure' => 'bag',
            'cost_price' => 10.00,
            'suggested_retail_price' => 20.00,
            'is_track_inventory' => true,
            'is_active' => true,
        ]);

        $stock = InventoryStock::create([
            'outlet_id' => $this->outletA->id,
            'product_id' => $product->id,
            'quantity_on_hand' => 10.00,
        ]);

        $order = SupplyOrder::create([
            'outlet_id' => $this->outletA->id,
            'order_number' => 'SUP-TEST-001',
            'status' => 'pending',
            'total_cost' => 100.00,
            'requested_by_user_id' => $manager->id,
        ]);

        SupplyOrderItem::create([
            'supply_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity_requested' => 10,
            'unit_cost' => 10.00,
            'subtotal' => 100.00,
        ]);

        // Attempting to jump directly from pending to received
        $response = $this->actingAs($manager)->patch("/supply-orders/{$order->id}/status", [
            'status' => 'received',
        ]);

        $response->assertSessionHasErrors(['error']);
        $order->refresh();
        $this->assertEquals('pending', $order->status);

        $stock->refresh();
        $this->assertEquals(10.00, (float) $stock->quantity_on_hand); // Stock untouched
    }

    public function test_custom_outlet_product_pricing_takes_precedence_in_pos_sales(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'outlet_id' => $this->outletA->id,
            'is_active' => true,
        ]);

        $category = Category::create([
            'brand_id' => $this->brand->id,
            'name' => 'Bakery',
            'slug' => 'bakery',
        ]);

        $product = Product::create([
            'brand_id' => $this->brand->id,
            'category_id' => $category->id,
            'sku' => 'BAK-001',
            'name' => 'Artisan Bagel',
            'unit_of_measure' => 'piece',
            'cost_price' => 1.00,
            'suggested_retail_price' => 5.00,
            'is_track_inventory' => true,
            'is_active' => true,
        ]);

        // Custom store-level price set to 6.50
        OutletProduct::create([
            'outlet_id' => $this->outletA->id,
            'product_id' => $product->id,
            'selling_price' => 6.50,
            'is_available' => true,
        ]);

        InventoryStock::create([
            'outlet_id' => $this->outletA->id,
            'product_id' => $product->id,
            'quantity_on_hand' => 50.00,
        ]);

        $response = $this->actingAs($admin)->post('/sales', [
            'outlet_id' => $this->outletA->id,
            'customer_name' => 'John Doe',
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('sales_transactions', [
            'outlet_id' => $this->outletA->id,
            'subtotal' => 13.00, // 2 * $6.50
            'tax_amount' => 1.30, // 10% tax
            'total_amount' => 14.30,
        ]);

        $stock = InventoryStock::where('outlet_id', $this->outletA->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertEquals(48.00, (float) $stock->quantity_on_hand);
    }
}
