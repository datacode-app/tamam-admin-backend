<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Vendor;
use App\Models\Zone;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreImportService
{
    private MultilingualImportService $multilingualService;

    public function __construct()
    {
        $this->multilingualService = new MultilingualImportService();
    }

    /**
     * Map template fields to database fields and separate vendor data
     */
    public function mapImportFields(array $importData): array
    {
        $mappedData = [];
        $vendorData = [];

        foreach ($importData as $key => $value) {
            switch ($key) {
                case 'storeName':
                    $mappedData['name'] = $value;
                    break;
                case 'ownerFirstName':
                    $vendorData['first_name'] = $value;
                    break;
                case 'ownerLastName':
                    $vendorData['last_name'] = $value;
                    break;
                case 'ownerEmail':
                    $vendorData['email'] = $value;
                    break;
                case 'DeliveryTime':
                    $mappedData['delivery_time'] = $value;
                    break;
                case 'Tax':
                    $mappedData['tax'] = is_numeric($value) ? (float)$value : 0;
                    break;
                case 'Comission':
                    $mappedData['comission'] = is_numeric($value) ? (float)$value : 0;
                    break;
                case 'MinimumOrderAmount':
                    $mappedData['minimum_order'] = is_numeric($value) ? (float)$value : 0;
                    break;
                case 'zone_id':
                    $mappedData['zone_id'] = (int)$value;
                    break;
                case 'module_id':
                    $mappedData['module_id'] = (int)$value;
                    break;
                // Map all other direct fields
                default:
                    // Use snake_case for database fields
                    $dbField = $this->convertToSnakeCase($key);
                    $mappedData[$dbField] = $value;
                    break;
            }
        }

        // Add vendor data to mapped data
        $mappedData['vendor'] = $vendorData;
        
        return $mappedData;
    }

    /**
     * Convert camelCase to snake_case for database field mapping
     */
    private function convertToSnakeCase(string $field): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $field));
    }

    /**
     * Validate required fields for store creation
     */
    public function validateRequiredFields(array $importData): array
    {
        $requiredFields = [
            'storeName', 'phone', 'email', 'zone_id', 'module_id'
        ];

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (empty($importData[$field])) {
                $missingFields[] = $field;
            }
        }

        $errors = [];
        if (!empty($missingFields)) {
            $errors = $missingFields;
        }

        // Validate zone_id
        if (isset($importData['zone_id'])) {
            if (!Zone::where('id', $importData['zone_id'])->exists()) {
                $errors[] = "Invalid zone_id: {$importData['zone_id']}";
            }
        }

        // Validate module_id
        if (isset($importData['module_id'])) {
            if (!Module::where('id', $importData['module_id'])->exists()) {
                $errors[] = "Invalid module_id: {$importData['module_id']}";
            }
        }

        return [
            'valid' => empty($errors),
            'missing_fields' => $missingFields,
            'errors' => $errors
        ];
    }

    /**
     * Parse delivery time string and validate format
     */
    public function parseDeliveryTime(string $deliveryTime): array
    {
        // Handle formats like: "20-30 min", "45-60 minutes", "30 min", "15-45 mins"
        $pattern = '/(\d+)(?:-(\d+))?\s*(mins?|minutes?)/i';
        
        if (preg_match($pattern, $deliveryTime, $matches)) {
            $min = (int)$matches[1];
            $max = isset($matches[2]) && !empty($matches[2]) ? (int)$matches[2] : $min;
            
            if ($max < $min) {
                return [
                    'valid' => false,
                    'error' => 'Maximum time must be greater than minimum'
                ];
            }
            
            return [
                'valid' => true,
                'min' => $min,
                'max' => $max
            ];
        }

        return [
            'valid' => false,
            'error' => 'Invalid delivery time format'
        ];
    }

    /**
     * ENHANCED: Find or create vendor handling duplicates properly
     */
    public function findOrCreateVendor(array $importData): array
    {
        $phone = $importData['phone'] ?? null;
        $email = $importData['email'] ?? null;
        $firstName = $importData['ownerFirstName'] ?? 'Store';
        $lastName = $importData['ownerLastName'] ?? 'Owner';

        if (!$phone || !$email) {
            return [
                'success' => false,
                'error' => 'Phone and email are required for vendor creation'
            ];
        }

        try {
            // Check if vendor exists by phone
            $existingVendor = Vendor::where('phone', $phone)->first();
            
            if ($existingVendor) {
                return [
                    'success' => true,
                    'vendor' => $existingVendor,
                    'created' => false,
                    'message' => 'Using existing vendor with phone: ' . $phone
                ];
            }

            // Check if vendor exists by email
            $existingVendor = Vendor::where('email', $email)->first();
            
            if ($existingVendor) {
                // Update phone if different
                if ($existingVendor->phone !== $phone) {
                    // Check if the new phone is already taken by another vendor
                    if (Vendor::where('phone', $phone)->where('id', '!=', $existingVendor->id)->exists()) {
                        return [
                            'success' => false,
                            'error' => 'Phone number ' . $phone . ' is already taken by another vendor'
                        ];
                    }
                    $existingVendor->phone = $phone;
                    $existingVendor->save();
                }
                
                return [
                    'success' => true,
                    'vendor' => $existingVendor,
                    'created' => false,
                    'message' => 'Using existing vendor with email: ' . $email
                ];
            }

            // Create new vendor
            $vendor = new Vendor();
            $vendor->f_name = $firstName;
            $vendor->l_name = $lastName;
            $vendor->phone = $phone;
            $vendor->email = $email;
            $vendor->password = Hash::make('password123'); // Default password
            $vendor->status = 1;
            $vendor->save();

            return [
                'success' => true,
                'vendor' => $vendor,
                'created' => true,
                'message' => 'Created new vendor: ' . $firstName . ' ' . $lastName
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Vendor creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ENHANCED: Create store with proper vendor handling and transaction rollback
     */
    public function createStoreWithVendor(array $importData): array
    {
        try {
            DB::beginTransaction();

            // Step 1: Find or create vendor
            $vendorResult = $this->findOrCreateVendor($importData);
            if (!$vendorResult['success']) {
                DB::rollback();
                return [
                    'success' => false,
                    'error' => $vendorResult['error']
                ];
            }

            $vendor = $vendorResult['vendor'];

            // Step 2: Map import fields
            $mappedData = $this->mapImportFields($importData);

            // Step 3: Create store
            $store = new Store();
            $store->name = $mappedData['name'];
            $store->phone = $mappedData['phone'] ?? $vendor->phone;
            $store->email = $mappedData['email'] ?? $vendor->email;
            $store->logo = $mappedData['logo'] ?? 'default.png';
            $store->cover_photo = $mappedData['cover_photo'] ?? 'default.png';
            $store->address = $mappedData['address'] ?? '';
            $store->latitude = $mappedData['latitude'] ?? 0;
            $store->longitude = $mappedData['longitude'] ?? 0;
            $store->vendor_id = $vendor->id;
            $store->zone_id = $mappedData['zone_id'];
            $store->module_id = $mappedData['module_id'];
            $store->minimum_order = $mappedData['minimum_order'] ?? 0;
            $store->comission = $mappedData['comission'] ?? 0;
            $store->tax = $mappedData['tax'] ?? 0;
            $store->delivery_time = $mappedData['delivery_time'] ?? '30-45 min';
            $store->minimum_shipping_charge = $mappedData['minimum_delivery_fee'] ?? 0;
            $store->per_km_shipping_charge = $mappedData['per_km_delivery_fee'] ?? 0;
            $store->maximum_shipping_charge = $mappedData['maximum_delivery_fee'] ?? 0;
            $store->schedule_order = ($mappedData['schedule_order'] ?? 'no') === 'yes' ? 1 : 0;
            $store->status = ($mappedData['status'] ?? 'inactive') === 'active' ? 1 : 0;
            $store->self_delivery_system = ($mappedData['self_delivery_system'] ?? 'no') === 'yes' ? 1 : 0;
            $store->veg = ($mappedData['veg'] ?? 'no') === 'yes' ? 1 : 0;
            $store->non_veg = ($mappedData['non_veg'] ?? 'no') === 'yes' ? 1 : 0;
            $store->free_delivery = ($mappedData['free_delivery'] ?? 'no') === 'yes' ? 1 : 0;
            $store->take_away = ($mappedData['take_away'] ?? 'no') === 'yes' ? 1 : 0;
            $store->delivery = ($mappedData['delivery'] ?? 'yes') === 'yes' ? 1 : 0;
            $store->reviews_section = ($mappedData['reviews_section'] ?? 'yes') === 'yes' ? 1 : 0;
            $store->pos_system = ($mappedData['pos_system'] ?? 'inactive') === 'active' ? 1 : 0;
            $store->active = ($mappedData['store_open'] ?? 'yes') === 'yes' ? 1 : 0;
            $store->featured = ($mappedData['featured_store'] ?? 'no') === 'yes' ? 1 : 0;
            $store->save();

            // Step 4: Process multilingual translations
            $translations = $this->multilingualService->processMultilingualData($importData, 'Store', $store->id);
            
            // Insert translations
            foreach ($translations as $translation) {
                DB::table('translations')->insert($translation);
            }

            DB::commit();

            return [
                'success' => true,
                'store' => $store,
                'vendor' => $vendor,
                'vendor_created' => $vendorResult['created'],
                'translations_count' => count($translations),
                'message' => 'Store created successfully with ' . count($translations) . ' translations'
            ];

        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'error' => 'Store creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk import stores from array of import data
     */
    public function bulkImportStores(array $importDataArray): array
    {
        $results = [
            'successful_imports' => 0,
            'failed_imports' => 0,
            'errors' => [],
            'created_stores' => [],
            'skipped_stores' => []
        ];

        foreach ($importDataArray as $index => $importData) {
            $storeName = $importData['storeName'] ?? 'Unknown Store';
            
            try {
                // Validate data first
                $validation = $this->validateRequiredFields($importData);
                if (!$validation['valid']) {
                    $results['failed_imports']++;
                    $results['errors'][] = "Row " . ($index + 1) . " ($storeName): Validation failed - " . implode(', ', $validation['errors']);
                    continue;
                }

                // Create store with vendor
                $result = $this->createStoreWithVendor($importData);
                
                if ($result['success']) {
                    $results['successful_imports']++;
                    $results['created_stores'][] = [
                        'store_id' => $result['store']->id,
                        'store_name' => $result['store']->name,
                        'vendor_id' => $result['vendor']->id,
                        'vendor_created' => $result['vendor_created'],
                        'translations_count' => $result['translations_count']
                    ];
                } else {
                    $results['failed_imports']++;
                    $results['errors'][] = "Row " . ($index + 1) . " ($storeName): " . $result['error'];
                }

            } catch (\Exception $e) {
                $results['failed_imports']++;
                $results['errors'][] = "Row " . ($index + 1) . " ($storeName): Unexpected error - " . $e->getMessage();
            }
        }

        return $results;
    }
}