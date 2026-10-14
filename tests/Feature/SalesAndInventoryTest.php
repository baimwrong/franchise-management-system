<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Outlet $outlet;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $brand = Brand::create([
            'name' => 'OmniBrew Coffee & Bakery',
            'code' => 'OMNI',
            'contact_email' => 'hq@omnifran.local',
            'contact_phone' => '+1-555-0000',
        ]);

        $this->outlet = Outlet::create([
            'brand_id' => $brand->id,
            'name' => 'OmniBrew Downtown Flagship',
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

        $category = Category::create([
            'brand_id' => $brand->id,
            'name' => 'Beverages',
            'slug' => 'beverages',
        ]);

        $this->product = Product::create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'sku' => 'PRD-BEV-001',
            'name' => 'Nitro Cold Brew',
            'unit_of_measure' => 'cup',
            'cost_price' => 1.50,
            'suggested_retail_price' => 5.00,
            'minimum_selling_price' => 4.00,
            'is_track_inventory' => true,
            'is_active' => true,
        ]);

        // Initial inventory stock
        InventoryStock::create([
            'outlet_id' => $this->outlet->id,
            'product_id' => $this->product->id,
            'quantity_on_hand' => 100.00,
            'reorder_threshold' => 15.00,
            'target_stock_level' => 80.00,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
        ]);
    }

    public function test_pos_sale_can_be_created_and_depletes_stock(): void
    {
        $response = $this->actingAs($this->admin)->post('/sales', [
            'outlet_id' => $this->outlet->id,
            'customer_name' => 'Sarah Connor',
            'payment_method' => 'credit_card',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $transaction = SalesTransaction::first();
        $this->assertNotNull($transaction);
        $this->assertEquals(10.00, (float) $transaction->subtotal); // 2 * $5.00
        $this->assertEquals(1.00, (float) $transaction->tax_amount); // 10% of $10.00
        $this->assertEquals(11.00, (float) $transaction->total_amount);

        // Check stock depleted by 2 (100 -> 98)
        $stock = InventoryStock::where('outlet_id', $this->outlet->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertEquals(98.00, (float) $stock->quantity_on_hand);
    }

    public function test_manual_stock_adjustment_updates_inventory_and_creates_audit(): void
    {
        $response = $this->actingAs($this->admin)->post('/inventory/adjust', [
            'outlet_id' => $this->outlet->id,
            'product_id' => $this->product->id,
            'adjustment_type' => 'damage',
            'adjustment_quantity' => -5,
            'reason' => 'Dropped case during morning setup',
        ]);

        $response->assertSessionHas('status');

        $stock = InventoryStock::where('outlet_id', $this->outlet->id)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertEquals(95.00, (float) $stock->quantity_on_hand);
        $this->assertDatabaseHas('stock_adjustments', [
            'outlet_id' => $this->outlet->id,
            'type' => 'damage',
        ]);
    }
}
