<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\BulkImport;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\OutletAudit;
use App\Models\Product;
use App\Models\Region;
use App\Models\RoyaltyInvoice;
use App\Models\SalesTransaction;
use App\Models\SupplyOrder;
use App\Models\SupplyOrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with enterprise franchise data.
     */
    public function run(): void
    {
        // 1. Create Brand Headquarters
        $brand = Brand::firstOrCreate(
            ['code' => 'FRAN'],
            [
                'name' => 'PrimeBrew Coffee & Artisan Bakery',
                'tagline' => 'Freshly Brewed Excellence Across Every City',
                'corporate_tax_id' => 'US-TAX-8829104',
                'hq_address' => '700 Grand Boulevard, Suite 1500, New York, NY 10001',
                'contact_email' => 'corporate@franchise.local',
                'contact_phone' => '+1 (800) 555-6664',
                'royalty_rate_percentage' => 5.00,
                'marketing_fund_percentage' => 2.00,
                'currency' => 'USD',
                'currency_symbol' => '$',
                'settings' => [
                    'price_corridor_tolerance_pct' => 15.0,
                    'minimum_audit_passing_score' => 75.0,
                    'reorder_lead_time_days' => 3,
                ],
            ]
        );

        // 2. Regions
        $metroRegion = Region::firstOrCreate(
            ['brand_id' => $brand->id, 'code' => 'METRO'],
            [
                'name' => 'Metropolitan Core District',
                'manager_name' => 'Alexander Hayes',
                'manager_email' => 'alex.hayes@franchise.local',
                'manager_phone' => '+1-555-0301',
            ]
        );

        $coastalRegion = Region::firstOrCreate(
            ['brand_id' => $brand->id, 'code' => 'COASTAL'],
            [
                'name' => 'Coastal Pacific Region',
                'manager_name' => 'Sophia Morales',
                'manager_email' => 'sophia.m@franchise.local',
                'manager_phone' => '+1-555-0302',
            ]
        );

        $airportRegion = Region::firstOrCreate(
            ['brand_id' => $brand->id, 'code' => 'AIRPORT'],
            [
                'name' => 'Transit & Airport Concessions',
                'manager_name' => 'Jonathan Sterling',
                'manager_email' => 'j.sterling@franchise.local',
                'manager_phone' => '+1-555-0303',
            ]
        );

        // 3. Outlets
        $outletsData = [
            [
                'brand_id' => $brand->id,
                'region_id' => $metroRegion->id,
                'name' => 'PrimeBrew Downtown Flagship',
                'outlet_code' => 'FRAN-DWT-001',
                'franchisee_name' => 'Corporate / Direct Owned',
                'type' => 'company_owned',
                'status' => 'active',
                'phone' => '+1-555-0101',
                'email' => 'downtown@franchise.local',
                'address' => '120 Broadway Ave, Floor 1',
                'city' => 'New York',
                'state_province' => 'NY',
                'postal_code' => '10006',
                'tax_identification_number' => 'NY-TAX-10101',
                'tax_rate_percentage' => 8.875,
                'opening_date' => '2023-01-15',
                'operating_hours' => ['weekday' => '06:00 - 22:00', 'weekend' => '07:00 - 23:00'],
            ],
            [
                'brand_id' => $brand->id,
                'region_id' => $metroRegion->id,
                'name' => 'PrimeBrew Uptown Midtown Plaza',
                'outlet_code' => 'FRAN-UPT-002',
                'franchisee_name' => 'Apex Hospitality Holdings LLC',
                'type' => 'franchise',
                'status' => 'active',
                'phone' => '+1-555-0102',
                'email' => 'uptown@franchise.local',
                'address' => '590 Lexington Ave',
                'city' => 'New York',
                'state_province' => 'NY',
                'postal_code' => '10022',
                'tax_identification_number' => 'NY-TAX-10102',
                'tax_rate_percentage' => 8.875,
                'opening_date' => '2023-06-01',
                'operating_hours' => ['weekday' => '06:30 - 21:00', 'weekend' => '08:00 - 20:00'],
            ],
            [
                'brand_id' => $brand->id,
                'region_id' => $coastalRegion->id,
                'name' => 'PrimeBrew Santa Monica Pier',
                'outlet_code' => 'FRAN-SMP-003',
                'franchisee_name' => 'Pacific Crest Franchisees Inc',
                'type' => 'franchise',
                'status' => 'active',
                'phone' => '+1-555-0103',
                'email' => 'santamonica@franchise.local',
                'address' => '200 Ocean Promenade',
                'city' => 'Santa Monica',
                'state_province' => 'CA',
                'postal_code' => '90401',
                'tax_identification_number' => 'CA-TAX-20201',
                'tax_rate_percentage' => 9.50,
                'opening_date' => '2024-03-10',
                'operating_hours' => ['weekday' => '07:00 - 22:00', 'weekend' => '07:00 - 23:00'],
            ],
            [
                'brand_id' => $brand->id,
                'region_id' => $airportRegion->id,
                'name' => 'PrimeBrew JFK Terminal 4 Express',
                'outlet_code' => 'FRAN-JFK-004',
                'franchisee_name' => 'Skyline Concessions Group',
                'type' => 'express',
                'status' => 'active',
                'phone' => '+1-555-0104',
                'email' => 'jfk.t4@franchise.local',
                'address' => 'JFK International Airport, Gate B22',
                'city' => 'Queens',
                'state_province' => 'NY',
                'postal_code' => '11430',
                'tax_identification_number' => 'NY-TAX-10104',
                'tax_rate_percentage' => 8.875,
                'opening_date' => '2024-08-20',
                'operating_hours' => ['weekday' => '05:00 - 23:30', 'weekend' => '05:00 - 23:30'],
            ],
            [
                'brand_id' => $brand->id,
                'region_id' => $metroRegion->id,
                'name' => 'PrimeBrew Financial District Kiosk',
                'outlet_code' => 'FRAN-FDK-005',
                'franchisee_name' => 'Apex Hospitality Holdings LLC',
                'type' => 'kiosk',
                'status' => 'active',
                'phone' => '+1-555-0105',
                'email' => 'fidi@franchise.local',
                'address' => '33 Maiden Lane',
                'city' => 'New York',
                'state_province' => 'NY',
                'postal_code' => '10038',
                'tax_identification_number' => 'NY-TAX-10105',
                'tax_rate_percentage' => 8.875,
                'opening_date' => '2025-02-01',
                'operating_hours' => ['weekday' => '07:00 - 18:00', 'weekend' => 'Closed'],
            ],
            [
                'brand_id' => $brand->id,
                'region_id' => $coastalRegion->id,
                'name' => 'PrimeBrew Seattle Waterfront (Pending Opening)',
                'outlet_code' => 'FRAN-SEA-006',
                'franchisee_name' => 'Emerald Sound Ventures',
                'type' => 'franchise',
                'status' => 'pending_approval',
                'phone' => '+1-555-0106',
                'email' => 'seattle@franchise.local',
                'address' => '1001 Alaskan Way',
                'city' => 'Seattle',
                'state_province' => 'WA',
                'postal_code' => '98104',
                'tax_identification_number' => 'WA-TAX-30301',
                'tax_rate_percentage' => 10.25,
                'opening_date' => '2026-11-01',
                'operating_hours' => ['weekday' => '07:00 - 20:00', 'weekend' => '08:00 - 20:00'],
            ],
        ];

        $outlets = [];
        foreach ($outletsData as $data) {
            $outlets[$data['outlet_code']] = Outlet::firstOrCreate(
                ['outlet_code' => $data['outlet_code']],
                $data
            );
        }

        $downtownOutlet = $outlets['FRAN-DWT-001'];
        $uptownOutlet = $outlets['FRAN-UPT-002'];

        // 4. Create Standard Demo Users for Each Role
        $defaultPassword = Hash::make('Password123!');

        User::firstOrCreate(
            ['email' => 'admin@franchise.local'],
            [
                'name' => 'Corporate Executive Admin',
                'role' => 'super_admin',
                'outlet_id' => null,
                'phone' => '+1-555-9999',
                'password' => $defaultPassword,
                'is_active' => true,
                'last_login_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'owner@franchise.local'],
            [
                'name' => 'Richard Sterling (Franchise Licensee)',
                'role' => 'franchise_owner',
                'outlet_id' => $uptownOutlet->id,
                'phone' => '+1-555-8888',
                'password' => $defaultPassword,
                'is_active' => true,
                'last_login_at' => now()->subHours(2),
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@franchise.local'],
            [
                'name' => 'Samantha Reed (Store General Manager)',
                'role' => 'outlet_manager',
                'outlet_id' => $downtownOutlet->id,
                'phone' => '+1-555-7777',
                'password' => $defaultPassword,
                'is_active' => true,
                'last_login_at' => now()->subMinutes(45),
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff@franchise.local'],
            [
                'name' => 'Leo Martinez (Lead Barista & Cashier)',
                'role' => 'staff',
                'outlet_id' => $downtownOutlet->id,
                'phone' => '+1-555-6666',
                'password' => $defaultPassword,
                'is_active' => true,
                'last_login_at' => now()->subMinutes(10),
            ]
        );

        // 5. Product Categories
        $catBeverages = Category::firstOrCreate(
            ['brand_id' => $brand->id, 'slug' => 'handcrafted-beverages'],
            ['name' => 'Handcrafted Beverages', 'description' => 'Signature coffees, espresso specialties, cold brews and cold teas', 'display_order' => 1]
        );

        $catBakery = Category::firstOrCreate(
            ['brand_id' => $brand->id, 'slug' => 'artisan-bakery'],
            ['name' => 'Artisan Bakery', 'description' => 'Fresh daily croissants, muffins, bagels, and pastries', 'display_order' => 2]
        );

        $catRetailBeans = Category::firstOrCreate(
            ['brand_id' => $brand->id, 'slug' => 'packaged-retail'],
            ['name' => 'Packaged Whole Beans', 'description' => 'Single-origin and house blend whole bean coffee for retail customer purchase', 'display_order' => 3]
        );

        $catConsumables = Category::firstOrCreate(
            ['brand_id' => $brand->id, 'slug' => 'store-consumables'],
            ['name' => 'Store Raw Supplies & Packaging', 'description' => 'Brand branded biodegradable cups, lids, syrups, and espresso beans for store prep', 'display_order' => 4]
        );

        // 6. Products
        $productsData = [
            [
                'brand_id' => $brand->id,
                'category_id' => $catBeverages->id,
                'sku' => 'PRD-BEV-001',
                'barcode' => '899100101',
                'name' => 'Velvet Caramel Cloud Latte (Grande)',
                'description' => 'Espresso paired with steamed oat milk, handcrafted caramel syrup, and silky cold foam.',
                'unit_of_measure' => 'cup',
                'cost_price' => 1.45,
                'suggested_retail_price' => 5.75,
                'minimum_selling_price' => 4.50,
                'is_track_inventory' => true,
                'is_active' => true,
            ],
            [
                'brand_id' => $brand->id,
                'category_id' => $catBeverages->id,
                'sku' => 'PRD-BEV-002',
                'barcode' => '899100102',
                'name' => 'Artisan Nitro Cold Brew (16oz)',
                'description' => 'Cold-steeped for 20 hours, nitrogen infused on tap for a cascading crema finish.',
                'unit_of_measure' => 'cup',
                'cost_price' => 1.10,
                'suggested_retail_price' => 5.25,
                'minimum_selling_price' => 4.00,
                'is_track_inventory' => true,
                'is_active' => true,
            ],
            [
                'brand_id' => $brand->id,
                'category_id' => $catBeverages->id,
                'sku' => 'PRD-BEV-003',
                'barcode' => '899100103',
                'name' => 'Matcha Kyoto Vanilla Frappe',
                'description' => 'Stone-ground ceremonial Uji matcha blended with organic vanilla and milk.',
                'unit_of_measure' => 'cup',
                'cost_price' => 1.60,
                'suggested_retail_price' => 6.25,
                'minimum_selling_price' => 5.00,
                'is_track_inventory' => true,
                'is_active' => true,
            ],
            [
                'brand_id' => $brand->id,
                'category_id' => $catBakery->id,
                'sku' => 'PRD-BAK-001',
                'barcode' => '899100201',
                'name' => 'Normandy Butter Flaky Croissant',
                'description' => 'Classic French croissant made with 82% pure butter, baked fresh every morning.',
                'unit_of_measure' => 'unit',
                'cost_price' => 0.95,
                'suggested_retail_price' => 3.95,
                'minimum_selling_price' => 3.00,
                'is_track_inventory' => true,
                'is_active' => true,
            ],
            [
                'brand_id' => $brand->id,
                'category_id' => $catBakery->id,
                'sku' => 'PRD-BAK-002',
                'barcode' => '899100202',
                'name' => 'Valrhona Dark Chocolate Babka Slice',
                'description' => 'Twisted sweet brioche dough layered with premium French dark chocolate.',
                'unit_of_measure' => 'unit',
                'cost_price' => 1.30,
                'suggested_retail_price' => 4.80,
                'minimum_selling_price' => 3.75,
                'is_track_inventory' => true,
                'is_active' => true,
            ],
            [
                'brand_id' => $brand->id,
                'category_id' => $catRetailBeans->id,
                'sku' => 'PRD-BEA-001',
                'barcode' => '899100301',
                'name' => 'Heritage House Espresso Blend (340g Whole Bean)',
                'description' => 'Tasting notes of dark cocoa, toasted hazelnut, and dark cherries.',
                'unit_of_measure' => 'bag',
                'cost_price' => 6.20,
                'suggested_retail_price' => 16.50,
                'minimum_selling_price' => 14.00,
                'is_track_inventory' => true,
                'is_active' => true,
            ],
            [
                'brand_id' => $brand->id,
                'category_id' => $catConsumables->id,
                'sku' => 'PRD-SUP-001',
                'barcode' => '899100401',
                'name' => 'Premium Branded Bio Cups 16oz (Case of 1000)',
                'description' => 'Compostable plant-lined paper cups with corporate brand logo.',
                'unit_of_measure' => 'case',
                'cost_price' => 45.00,
                'suggested_retail_price' => 55.00,
                'minimum_selling_price' => 50.00,
                'is_track_inventory' => true,
                'is_active' => true,
            ],
        ];

        $products = [];
        foreach ($productsData as $prod) {
            $products[$prod['sku']] = Product::firstOrCreate(
                ['sku' => $prod['sku']],
                $prod
            );
        }

        // 7. Seed Inventory Stock Levels for each outlet
        foreach ($outlets as $outlet) {
            foreach ($products as $sku => $product) {
                $qty = match ($sku) {
                    'PRD-BEV-001' => rand(60, 200),
                    'PRD-BEV-002' => rand(40, 150),
                    'PRD-BAK-001' => rand(5, 50), // some will trigger low stock alert
                    'PRD-BEA-001' => rand(12, 45),
                    'PRD-SUP-001' => rand(3, 15),
                    default => rand(20, 80),
                };

                InventoryStock::firstOrCreate(
                    [
                        'outlet_id' => $outlet->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity_on_hand' => $qty,
                        'reserved_quantity' => 0,
                        'reorder_threshold' => 15,
                        'target_stock_level' => 80,
                        'last_restocked_at' => now()->subDays(rand(1, 5)),
                    ]
                );
            }
        }

        // 8. Seed Realistic Sales Transactions (Last 14 days)
        $cashierUser = User::where('email', 'staff@franchise.local')->first();
        $sampleCustomers = [
            ['Jane Sterling', '+1-555-2001'],
            ['David Harrison', '+1-555-2002'],
            ['Alicia Wong', '+1-555-2003'],
            ['Carlos Gomez', '+1-555-2004'],
            ['Emily Watson', '+1-555-2005'],
        ];

        for ($day = 13; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day)->setTime(rand(8, 20), rand(0, 59));

            foreach ([$downtownOutlet, $uptownOutlet] as $outlet) {
                $trxCount = rand(3, 7);
                for ($k = 0; $k < $trxCount; $k++) {
                    $customer = $sampleCustomers[array_rand($sampleCustomers)];
                    $p1 = $products['PRD-BEV-001'];
                    $p2 = $products['PRD-BAK-001'];

                    $q1 = rand(1, 3);
                    $q2 = rand(1, 2);

                    $subtotal = ($q1 * (float) $p1->suggested_retail_price) + ($q2 * (float) $p2->suggested_retail_price);
                    $tax = round(($subtotal * (float) $outlet->tax_rate_percentage) / 100, 2);
                    $total = $subtotal + $tax;

                    $trxCode = 'TRX-'.$outlet->outlet_code.'-'.$date->format('Ymd').'-'.str_pad((string) rand(100, 999), 4, '0', STR_PAD_LEFT);

                    $trx = SalesTransaction::firstOrCreate(
                        ['transaction_code' => $trxCode],
                        [
                            'outlet_id' => $outlet->id,
                            'user_id' => $cashierUser->id,
                            'customer_name' => $customer[0],
                            'customer_phone' => $customer[1],
                            'subtotal' => $subtotal,
                            'discount_amount' => 0.00,
                            'tax_amount' => $tax,
                            'total_amount' => $total,
                            'payment_method' => ['credit_card', 'cash', 'qris'][rand(0, 2)],
                            'payment_status' => 'paid',
                            'transaction_date' => $date->copy()->addMinutes(rand(10, 300)),
                        ]
                    );

                    $trx->items()->firstOrCreate(
                        ['sales_transaction_id' => $trx->id, 'product_id' => $p1->id],
                        [
                            'product_name' => $p1->name,
                            'sku' => $p1->sku,
                            'unit_price' => $p1->suggested_retail_price,
                            'quantity' => $q1,
                            'subtotal' => $q1 * (float) $p1->suggested_retail_price,
                        ]
                    );

                    $trx->items()->firstOrCreate(
                        ['sales_transaction_id' => $trx->id, 'product_id' => $p2->id],
                        [
                            'product_name' => $p2->name,
                            'sku' => $p2->sku,
                            'unit_price' => $p2->suggested_retail_price,
                            'quantity' => $q2,
                            'subtotal' => $q2 * (float) $p2->suggested_retail_price,
                        ]
                    );
                }
            }
        }

        // 9. Central Supply Requisition Orders
        $managerUser = User::where('email', 'manager@franchise.local')->first();
        $adminUser = User::where('email', 'admin@franchise.local')->first();

        $so1 = SupplyOrder::firstOrCreate(
            ['order_number' => 'SO-202610-001'],
            [
                'outlet_id' => $uptownOutlet->id,
                'requested_by_user_id' => $managerUser->id,
                'processed_by_user_id' => $adminUser->id,
                'status' => 'dispatched',
                'total_amount' => 540.00,
                'notes' => 'Weekly replenishment for holiday peak foot-traffic.',
                'approved_at' => now()->subDays(2),
                'dispatched_at' => now()->subDay(),
            ]
        );

        SupplyOrderItem::firstOrCreate(
            ['supply_order_id' => $so1->id, 'product_id' => $products['PRD-BEA-001']->id],
            [
                'quantity_requested' => 40,
                'quantity_fulfilled' => 40,
                'unit_cost' => 6.20,
                'subtotal' => 248.00,
            ]
        );

        SupplyOrderItem::firstOrCreate(
            ['supply_order_id' => $so1->id, 'product_id' => $products['PRD-SUP-001']->id],
            [
                'quantity_requested' => 6,
                'quantity_fulfilled' => 6,
                'unit_cost' => 45.00,
                'subtotal' => 270.00,
            ]
        );

        // Pending requisition
        SupplyOrder::firstOrCreate(
            ['order_number' => 'SO-202610-002'],
            [
                'outlet_id' => $downtownOutlet->id,
                'requested_by_user_id' => $managerUser->id,
                'status' => 'pending',
                'total_amount' => 310.00,
                'notes' => 'Urgent restocking: Paper cups and roasted espresso beans.',
            ]
        );

        // 10. Royalty Invoices
        RoyaltyInvoice::firstOrCreate(
            ['invoice_number' => 'ROY-'.$uptownOutlet->outlet_code.'-202609'],
            [
                'outlet_id' => $uptownOutlet->id,
                'period_start' => '2026-09-01',
                'period_end' => '2026-09-30',
                'gross_sales' => 42850.00,
                'royalty_percentage' => 5.00,
                'royalty_fee' => 2142.50,
                'marketing_fee' => 857.00,
                'technology_fee' => 50.00,
                'total_amount_due' => 3049.50,
                'status' => 'paid',
                'due_date' => '2026-10-15',
                'paid_at' => '2026-10-10 14:22:00',
                'payment_reference' => 'WIRE-ACH-889104',
                'notes' => 'Cleared via Corporate Wire ACH.',
            ]
        );

        RoyaltyInvoice::firstOrCreate(
            ['invoice_number' => 'ROY-'.$uptownOutlet->outlet_code.'-202610'],
            [
                'outlet_id' => $uptownOutlet->id,
                'period_start' => '2026-10-01',
                'period_end' => '2026-10-31',
                'gross_sales' => 18450.00,
                'royalty_percentage' => 5.00,
                'royalty_fee' => 922.50,
                'marketing_fee' => 369.00,
                'technology_fee' => 50.00,
                'total_amount_due' => 1341.50,
                'status' => 'issued',
                'due_date' => '2026-11-15',
                'notes' => 'Current billing period accrual.',
            ]
        );

        // 11. Outlet Audits
        OutletAudit::firstOrCreate(
            [
                'outlet_id' => $downtownOutlet->id,
                'audit_date' => '2026-09-25',
            ],
            [
                'auditor_id' => $adminUser->id,
                'food_safety_score' => 98.0,
                'cleanliness_score' => 95.0,
                'brand_standards_score' => 94.0,
                'customer_service_score' => 96.0,
                'overall_score' => 95.75,
                'passed' => true,
                'findings' => 'Outstanding espresso extraction consistency. Staff fully certified in food allergen protocols.',
                'corrective_actions' => 'None required. Commended for exemplary corporate standard adherence.',
            ]
        );

        OutletAudit::firstOrCreate(
            [
                'outlet_id' => $uptownOutlet->id,
                'audit_date' => '2026-10-02',
            ],
            [
                'auditor_id' => $adminUser->id,
                'food_safety_score' => 88.0,
                'cleanliness_score' => 84.0,
                'brand_standards_score' => 89.0,
                'customer_service_score' => 86.0,
                'overall_score' => 86.75,
                'passed' => true,
                'findings' => 'Pastry display case illumination dim; minor signage placement deviation from brand handbook.',
                'corrective_actions' => 'Replace display LED bulbs and align promotional shelf talkers with standard catalog.',
                'follow_up_date' => '2026-11-02',
            ]
        );

        // 12. Sample Bulk Import Log
        BulkImport::firstOrCreate(
            ['filename' => 'outlets_onboarding_batch1.csv'],
            [
                'user_id' => $adminUser->id,
                'type' => 'outlets',
                'total_rows' => 5,
                'successful_rows' => 5,
                'failed_rows' => 0,
                'status' => 'completed',
                'error_log' => [],
            ]
        );
    }
}
