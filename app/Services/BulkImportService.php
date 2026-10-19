<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\BulkImport;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Region;
use App\Models\SalesTransaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BulkImportService
{
    /**
     * Get sample CSV template header and example rows for the given type.
     */
    public function getTemplateContent(string $type): string
    {
        return match ($type) {
            'outlets' => "name,outlet_code,region_code,franchisee_name,type,status,phone,email,address,city,tax_rate_percentage\n".
                "\"OmniBrew Metro Downtown\",\"OMNI-MET-001\",\"METRO\",\"Corporate / HQ\",\"company_owned\",\"active\",\"+1-555-0101\",\"downtown@omnibrew.com\",\"100 Market St\",\"Metro City\",10.00\n".
                "\"OmniBrew Uptown Franchise\",\"OMNI-UPT-002\",\"METRO\",\"Apex Hospitality LLC\",\"franchise\",\"active\",\"+1-555-0102\",\"uptown@omnibrew.com\",\"450 North Blvd\",\"Metro City\",10.00\n".
                '"OmniBrew Airport Express","OMNI-AIR-003","AIRPORT","Skyline Retail Group","express","active","+1-555-0103","airport@omnibrew.com","Terminal 2 Gate 14","Capital Airport",8.50',

            'products' => "sku,barcode,name,category_slug,unit_of_measure,cost_price,suggested_retail_price,minimum_selling_price,is_track_inventory\n".
                "\"PRD-BEV-001\",\"899100101\",\"Caramel Cloud Latte\",\"handcrafted-coffee\",\"cup\",1.45,5.50,4.50,1\n".
                "\"PRD-BEV-002\",\"899100102\",\"Artisan Nitro Cold Brew\",\"handcrafted-coffee\",\"cup\",1.20,4.95,4.00,1\n".
                "\"PRD-BAK-001\",\"899100201\",\"Butter Flaky Croissant\",\"fresh-bakery\",\"unit\",0.85,3.75,3.00,1\n".
                '"PRD-BEA-001","899100301","Signature Roast Beans 250g","packaged-retail","bag",5.50,14.00,12.00,1',

            'inventory' => "outlet_code,sku,quantity_on_hand,reorder_threshold,target_stock_level\n".
                "\"OMNI-MET-001\",\"PRD-BEV-001\",120.00,25.00,150.00\n".
                "\"OMNI-MET-001\",\"PRD-BAK-001\",45.00,15.00,60.00\n".
                "\"OMNI-UPT-002\",\"PRD-BEV-001\",85.00,20.00,100.00\n".
                '"OMNI-AIR-003","PRD-BEA-001",30.00,10.00,50.00',

            'staff' => "name,email,role,outlet_code,phone,password\n".
                "\"Marcus Vance\",\"marcus.vance@omnifran.local\",\"outlet_manager\",\"OMNI-MET-001\",\"+1-555-0201\",\"Password123!\"\n".
                "\"Elena Rostova\",\"elena.rostova@omnifran.local\",\"staff\",\"OMNI-MET-001\",\"+1-555-0202\",\"Password123!\"\n".
                '"David Chen","david.chen@omnifran.local","franchise_owner","OMNI-UPT-002","+1-555-0203","Password123!"',

            'sales' => "outlet_code,transaction_code,cashier_email,customer_name,customer_phone,subtotal,discount_amount,tax_amount,total_amount,payment_method,payment_status,transaction_date\n".
                "\"OMNI-MET-001\",\"TRX-MET-202610-001\",\"marcus.vance@omnifran.local\",\"Jane Doe\",\"+1-555-9001\",14.20,0.00,1.42,15.62,\"credit_card\",\"paid\",\"2026-10-12 10:15:00\"\n".
                '"OMNI-UPT-002","TRX-UPT-202610-002","david.chen@omnifran.local","Robert Fox","+1-555-9002",22.50,2.00,2.05,22.55,"qris","paid","2026-10-12 11:45:00"',

            default => "header1,header2\nvalue1,value2",
        };
    }

    /**
     * Process an uploaded CSV file.
     *
     * @return array{success: bool, imported_count: int, failed_count: int, errors: array, import_record: BulkImport}
     */
    public function processImport(UploadedFile $file, string $type, User $user): array
    {
        $rows = $this->parseCsv($file);
        $totalRows = count($rows);

        $import = BulkImport::create([
            'user_id' => $user->id,
            'type' => $type,
            'filename' => $file->getClientOriginalName(),
            'total_rows' => $totalRows,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'status' => 'processing',
            'error_log' => [],
        ]);

        $brand = Brand::first() ?? Brand::create([
            'name' => 'Franchise Enterprise',
            'code' => 'FRAN',
            'contact_email' => 'hq@franchise.local',
            'contact_phone' => '+1-555-0000',
            'royalty_rate_percentage' => 5.00,
            'marketing_fund_percentage' => 2.00,
            'currency' => 'USD',
            'currency_symbol' => '$',
        ]);

        $successful = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // account for 1-based index and header row

            try {
                DB::beginTransaction();

                match ($type) {
                    'outlets' => $this->importOutletRow($row, $brand),
                    'products' => $this->importProductRow($row, $brand),
                    'inventory' => $this->importInventoryRow($row),
                    'staff' => $this->importStaffRow($row),
                    'sales' => $this->importSalesRow($row),
                    default => throw new \InvalidArgumentException("Unsupported import type: {$type}"),
                };

                DB::commit();
                $successful++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $errors[] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'data' => $row,
                ];
            }
        }

        $failed = count($errors);
        $status = $failed === 0 ? 'completed' : ($successful > 0 ? 'completed' : 'failed');

        $import->update([
            'successful_rows' => $successful,
            'failed_rows' => $failed,
            'status' => $status,
            'error_log' => $errors,
        ]);

        return [
            'success' => $successful > 0,
            'imported_count' => $successful,
            'failed_count' => $failed,
            'errors' => $errors,
            'import_record' => $import,
        ];
    }

    /**
     * Parse CSV into associative array using headers.
     */
    protected function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            throw new \RuntimeException('Unable to read uploaded file.');
        }

        // Detect BOM (Byte Order Mark) and skip it
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Sniff delimiter from first line
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $semicolonCount = substr_count($firstLine, ';');
        $commaCount = substr_count($firstLine, ',');
        $delimiter = ($semicolonCount > $commaCount) ? ';' : ',';

        // Rewind and skip BOM again
        rewind($handle);
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = null;
        $rows = [];

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Clean headers and rows
            $data = array_map(fn ($val) => trim((string) $val), $data);

            if (! $header) {
                $header = array_map(fn ($col) => strtolower(trim($col)), $data);

                continue;
            }

            // Ignore empty lines
            if (empty(array_filter($data, fn ($v) => $v !== ''))) {
                continue;
            }

            if (count($data) === count($header)) {
                $rows[] = array_combine($header, $data);
            } elseif (count($data) < count($header)) {
                $padded = array_pad($data, count($header), '');
                $rows[] = array_combine($header, $padded);
            }
        }

        fclose($handle);

        return $rows;
    }

    protected function importOutletRow(array $row, Brand $brand): void
    {
        $validator = Validator::make($row, [
            'name' => 'required|string|max:255',
            'outlet_code' => 'required|string|max:50',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(implode(', ', $validator->errors()->all()));
        }

        $regionId = null;
        if (! empty($row['region_code'])) {
            $region = Region::firstOrCreate(
                ['brand_id' => $brand->id, 'code' => strtoupper(trim($row['region_code']))],
                ['name' => ucfirst(strtolower(trim($row['region_code']))).' Region']
            );
            $regionId = $region->id;
        }

        Outlet::updateOrCreate(
            ['outlet_code' => trim($row['outlet_code'])],
            [
                'brand_id' => $brand->id,
                'region_id' => $regionId,
                'name' => trim($row['name']),
                'franchisee_name' => ! empty($row['franchisee_name']) ? trim($row['franchisee_name']) : 'Corporate / HQ',
                'type' => in_array($row['type'] ?? '', ['franchise', 'company_owned', 'kiosk', 'express']) ? $row['type'] : 'franchise',
                'status' => in_array($row['status'] ?? '', ['active', 'pending_approval', 'renovation', 'suspended', 'closed']) ? $row['status'] : 'active',
                'phone' => trim($row['phone']),
                'email' => trim($row['email']),
                'address' => trim($row['address']),
                'city' => trim($row['city']),
                'tax_rate_percentage' => is_numeric($row['tax_rate_percentage'] ?? null) ? (float) $row['tax_rate_percentage'] : 10.00,
            ]
        );
    }

    protected function importProductRow(array $row, Brand $brand): void
    {
        $validator = Validator::make($row, [
            'sku' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'cost_price' => 'required|numeric|min:0',
            'suggested_retail_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(implode(', ', $validator->errors()->all()));
        }

        $categorySlug = ! empty($row['category_slug']) ? Str::slug($row['category_slug']) : 'general';
        $category = Category::firstOrCreate(
            ['brand_id' => $brand->id, 'slug' => $categorySlug],
            ['name' => ucwords(str_replace('-', ' ', $categorySlug))]
        );

        Product::updateOrCreate(
            ['sku' => trim($row['sku'])],
            [
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'barcode' => ! empty($row['barcode']) ? trim($row['barcode']) : null,
                'name' => trim($row['name']),
                'unit_of_measure' => ! empty($row['unit_of_measure']) ? trim($row['unit_of_measure']) : 'unit',
                'cost_price' => (float) $row['cost_price'],
                'suggested_retail_price' => (float) $row['suggested_retail_price'],
                'minimum_selling_price' => is_numeric($row['minimum_selling_price'] ?? null) ? (float) $row['minimum_selling_price'] : (float) $row['cost_price'],
                'is_track_inventory' => filter_var($row['is_track_inventory'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'is_active' => true,
            ]
        );
    }

    protected function importInventoryRow(array $row): void
    {
        $validator = Validator::make($row, [
            'outlet_code' => 'required|string|exists:outlets,outlet_code',
            'sku' => 'required|string|exists:products,sku',
            'quantity_on_hand' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(implode(', ', $validator->errors()->all()));
        }

        $outlet = Outlet::where('outlet_code', trim($row['outlet_code']))->firstOrFail();
        $product = Product::where('sku', trim($row['sku']))->firstOrFail();

        InventoryStock::updateOrCreate(
            [
                'outlet_id' => $outlet->id,
                'product_id' => $product->id,
            ],
            [
                'quantity_on_hand' => (float) $row['quantity_on_hand'],
                'reorder_threshold' => is_numeric($row['reorder_threshold'] ?? null) ? (float) $row['reorder_threshold'] : 10.00,
                'target_stock_level' => is_numeric($row['target_stock_level'] ?? null) ? (float) $row['target_stock_level'] : 50.00,
                'last_restocked_at' => now(),
            ]
        );
    }

    protected function importStaffRow(array $row): void
    {
        $validator = Validator::make($row, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|string|in:super_admin,franchise_owner,outlet_manager,staff',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(implode(', ', $validator->errors()->all()));
        }

        $outletId = null;
        if (! empty($row['outlet_code'])) {
            $outlet = Outlet::where('outlet_code', trim($row['outlet_code']))->first();
            if ($outlet) {
                $outletId = $outlet->id;
            }
        }

        $email = strtolower(trim($row['email']));
        $existingUser = User::where('email', $email)->first();

        $userData = [
            'name' => trim($row['name']),
            'role' => $row['role'],
            'outlet_id' => $outletId,
            'phone' => ! empty($row['phone']) ? trim($row['phone']) : null,
            'is_active' => true,
        ];

        // Do not wipe out custom passwords on staff re-import
        if (! empty($row['password'])) {
            $userData['password'] = Hash::make(trim($row['password']));
        } elseif (! $existingUser) {
            $userData['password'] = Hash::make('Password123!');
        }

        User::updateOrCreate(
            ['email' => $email],
            $userData
        );
    }

    protected function importSalesRow(array $row): void
    {
        $validator = Validator::make($row, [
            'outlet_code' => 'required|string|exists:outlets,outlet_code',
            'transaction_code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(implode(', ', $validator->errors()->all()));
        }

        $outlet = Outlet::where('outlet_code', trim($row['outlet_code']))->firstOrFail();

        $userId = null;
        if (! empty($row['cashier_email'])) {
            $user = User::where('email', strtolower(trim($row['cashier_email'])))->first();
            $userId = $user?->id;
        }

        $transactionDate = ! empty($row['transaction_date'])
            ? Carbon::parse($row['transaction_date'])
            : now();

        $transaction = SalesTransaction::updateOrCreate(
            ['transaction_code' => trim($row['transaction_code'])],
            [
                'outlet_id' => $outlet->id,
                'user_id' => $userId,
                'customer_name' => ! empty($row['customer_name']) ? trim($row['customer_name']) : null,
                'customer_phone' => ! empty($row['customer_phone']) ? trim($row['customer_phone']) : null,
                'subtotal' => (float) $row['subtotal'],
                'discount_amount' => is_numeric($row['discount_amount'] ?? null) ? (float) $row['discount_amount'] : 0.00,
                'tax_amount' => is_numeric($row['tax_amount'] ?? null) ? (float) $row['tax_amount'] : 0.00,
                'total_amount' => (float) $row['total_amount'],
                'payment_method' => in_array($row['payment_method'] ?? '', ['cash', 'credit_card', 'debit_card', 'qris', 'bank_transfer']) ? $row['payment_method'] : 'cash',
                'payment_status' => in_array($row['payment_status'] ?? '', ['paid', 'refunded', 'void']) ? $row['payment_status'] : 'paid',
                'transaction_date' => $transactionDate,
            ]
        );

        // If product SKU is included in row, record line item
        if (! empty($row['sku'])) {
            $product = Product::where('sku', trim($row['sku']))->first();
            if ($product) {
                $qty = is_numeric($row['quantity'] ?? null) ? (float) $row['quantity'] : 1.0;
                $unitPrice = is_numeric($row['unit_price'] ?? null) ? (float) $row['unit_price'] : (float) $product->suggested_retail_price;
                $lineSubtotal = $qty * $unitPrice;

                $transaction->items()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'sku' => $product->sku,
                    ],
                    [
                        'product_name' => $product->name,
                        'unit_price' => $unitPrice,
                        'quantity' => $qty,
                        'subtotal' => $lineSubtotal,
                    ]
                );
            }
        }
    }
}
