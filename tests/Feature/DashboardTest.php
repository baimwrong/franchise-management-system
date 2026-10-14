<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $brand = Brand::create([
            'name' => 'OmniBrew Coffee & Bakery',
            'code' => 'OMNI',
            'contact_email' => 'hq@omnifran.local',
            'contact_phone' => '+1-555-0000',
        ]);

        $outlet = Outlet::create([
            'brand_id' => $brand->id,
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

        $user = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Consolidated Franchise Dashboard');
        $response->assertSee('OmniBrew Coffee & Bakery');
    }

    public function test_user_can_switch_outlet_context(): void
    {
        $brand = Brand::create([
            'name' => 'OmniBrew Coffee & Bakery',
            'code' => 'OMNI',
            'contact_email' => 'hq@omnifran.local',
            'contact_phone' => '+1-555-0000',
        ]);

        $outlet = Outlet::create([
            'brand_id' => $brand->id,
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

        $user = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/dashboard/switch-outlet', [
            'outlet_id' => $outlet->id,
        ]);

        $response->assertSessionHas('active_outlet_id', $outlet->id);
    }
}
