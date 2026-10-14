<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brand = Brand::create([
            'name' => 'PrimeBrew Coffee & Bakery',
            'code' => 'FRAN',
            'contact_email' => 'hq@franchise.local',
            'contact_phone' => '+1-555-0000',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_download_csv_template(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/bulk-imports/template/outlets');

        $response->assertStatus(200);
        $this->assertStringContainsString('outlet_code', $response->getContent());
    }

    public function test_admin_can_bulk_import_outlets_from_csv(): void
    {
        $csvContent = "name,outlet_code,region_code,franchisee_name,type,status,phone,email,address,city,tax_rate_percentage\n".
            "\"PrimeBrew Metro West\",\"FRAN-WST-001\",\"METRO\",\"Apex Group\",\"franchise\",\"active\",\"+1-555-0101\",\"west@franchise.local\",\"500 West Ave\",\"Metro City\",10.00\n".
            "\"PrimeBrew North Branch\",\"FRAN-NTH-002\",\"METRO\",\"Apex Group\",\"franchise\",\"active\",\"+1-555-0102\",\"north@franchise.local\",\"800 North Rd\",\"Metro City\",10.00\n";

        $file = UploadedFile::fake()->createWithContent('outlets.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post('/admin/bulk-imports', [
            'type' => 'outlets',
            'file' => $file,
        ]);

        $response->assertRedirect('/admin/bulk-imports');

        $this->assertDatabaseHas('outlets', ['outlet_code' => 'FRAN-WST-001']);
        $this->assertDatabaseHas('outlets', ['outlet_code' => 'FRAN-NTH-002']);

        $this->assertDatabaseHas('bulk_imports', [
            'type' => 'outlets',
            'successful_rows' => 2,
            'failed_rows' => 0,
            'status' => 'completed',
        ]);
    }

    public function test_bulk_import_handles_row_validation_errors(): void
    {
        // One valid row, one invalid row missing required fields
        $csvContent = "name,outlet_code,region_code,franchisee_name,type,status,phone,email,address,city,tax_rate_percentage\n".
            "\"Valid Store\",\"FRAN-VAL-001\",\"METRO\",\"Corporate\",\"company_owned\",\"active\",\"+1-555-0101\",\"valid@franchise.local\",\"100 Main St\",\"City\",10.00\n".
            "\"Invalid Store Missing Email & Code\",\"\",\"METRO\",\"Corporate\",\"company_owned\",\"active\",\"\",\"\",\"100 Main St\",\"City\",10.00\n";

        $file = UploadedFile::fake()->createWithContent('mixed_outlets.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post('/admin/bulk-imports', [
            'type' => 'outlets',
            'file' => $file,
        ]);

        $this->assertDatabaseHas('outlets', ['outlet_code' => 'FRAN-VAL-001']);
        $this->assertDatabaseHas('bulk_imports', [
            'type' => 'outlets',
            'successful_rows' => 1,
            'failed_rows' => 1,
            'status' => 'completed',
        ]);
    }

    public function test_non_admin_cannot_access_bulk_import_hub(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->get('/admin/bulk-imports');

        $response->assertStatus(403);
    }
}
