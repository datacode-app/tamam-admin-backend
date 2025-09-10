<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Store;
use App\Models\Category;
use App\Models\Unit;

class ItemImportService
{
    private MultilingualImportService $multilingualService;

    public function __construct()
    {
        $this->multilingualService = new MultilingualImportService();
    }

    /**
     * Normalize import data from old numeric format to new header format
     */
    public function normalizeImportData(array $data): array
    {
        // If data is empty or contains only numeric keys that are headers, return empty
        if (empty($data) || $this->isHeaderRow($data)) {
            return [];
        }

        // If data already has string keys, return as-is (new format)
        if (!is_numeric(array_keys($data)[0])) {
            return $data;
        }

        // Convert old numeric format to new header format
        $headerMapping = [
            0 => 'Id',
            1 => 'Name', 
            2 => 'Description',
            3 => 'Image',
            4 => 'Images',
            5 => 'CategoryId',
            6 => 'SubCategoryId', 
            7 => 'UnitId',
            8 => 'Stock',
            9 => 'Price',
            10 => 'Discount',
            11 => 'DiscountType',
            12 => 'AvailableTimeStarts',
            13 => 'AvailableTimeEnds',
            14 => 'Variations',
            15 => 'ChoiceOptions',
            16 => 'AddOns',
            17 => 'Attributes',
            18 => 'StoreId',
            19 => 'ModuleId',
            20 => 'Status',
            21 => 'Veg',
            22 => 'Recommended',
            23 => 'name_ckb',
            24 => 'name_ar',
            25 => 'description_ckb',
            26 => 'description_ar'
        ];

        $normalizedData = [];
        foreach ($headerMapping as $index => $header) {
            $normalizedData[$header] = $data[$index] ?? '';
        }

        return $normalizedData;
    }

    /**
     * Check if this is a header row that should be skipped
     */
    private function isHeaderRow(array $data): bool
    {
        // Check for numeric headers (0, 1, 2, 3...)
        $numericHeaders = array_filter($data, 'is_numeric');
        if (count($numericHeaders) == count($data) && count($data) > 3) {
            $sortedValues = array_values($data);
            sort($sortedValues, SORT_NUMERIC);
            return $sortedValues[0] == 0 && $sortedValues[1] == 1; // Starts with 0, 1
        }

        // Check for text headers (Id, Name, Description...)
        $textHeaders = ['Id', 'Name', 'Description', 'Price', 'CategoryId'];
        $matchingHeaders = array_intersect($data, $textHeaders);
        return count($matchingHeaders) >= 3;
    }

    /**
     * Validate required fields
     */
    public function validateRequiredFields(array $data, bool $moduleIdRequired = false): array
    {
        $requiredFields = [
            'Name',
            'CategoryId',
            'SubCategoryId', 
            'Price',
            'StoreId',
            'Discount',
            'DiscountType'
        ];

        if ($moduleIdRequired) {
            $requiredFields[] = 'ModuleId';
        }

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missingFields[] = $field;
            }
        }

        return [
            'valid' => empty($missingFields),
            'missing_fields' => $missingFields
        ];
    }

    /**
     * Validate price constraints
     */
    public function validatePrice($price, string $itemId): array
    {
        if (!is_numeric($price)) {
            return [
                'valid' => false,
                'error' => "Price must be a valid number for item {$itemId}"
            ];
        }

        $numericPrice = (float)$price;
        if ($numericPrice <= 0) {
            return [
                'valid' => false,
                'error' => "Price must be greater than 0 for item {$itemId}"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate discount constraints
     */
    public function validateDiscount($discount, string $itemId): array
    {
        if (!is_numeric($discount)) {
            return [
                'valid' => false,
                'error' => "Discount must be a valid number for item {$itemId}"
            ];
        }

        $numericDiscount = (float)$discount;
        if ($numericDiscount < 0 || $numericDiscount > 100) {
            return [
                'valid' => false,
                'error' => "Discount must be between 0-100 for item {$itemId}"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate image filename length
     */
    public function validateImageName(string $imageName, string $itemId): array
    {
        if (strlen($imageName) > 33) { // Conservative limit
            return [
                'valid' => false,
                'error' => "Image filename too long for item {$itemId}"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate available time constraints
     */
    public function validateAvailableTime(string $startTime, string $endTime, string $itemId): array
    {
        // Empty times are valid (defaults will be applied)
        if (empty($startTime) && empty($endTime)) {
            return ['valid' => true];
        }

        // Validate time format
        if (!empty($startTime) && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $startTime)) {
            return [
                'valid' => false,
                'error' => "Invalid AvailableTimeStarts format for item {$itemId}"
            ];
        }

        if (!empty($endTime) && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $endTime)) {
            return [
                'valid' => false,
                'error' => "Invalid AvailableTimeEnds format for item {$itemId}"
            ];
        }

        // Check logical order
        if (!empty($startTime) && !empty($endTime) && $endTime <= $startTime) {
            return [
                'valid' => false,
                'error' => "AvailableTimeEnds must be greater than AvailableTimeStarts for item {$itemId}"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate foreign key references
     */
    public function validateForeignKeys(array $data): array
    {
        $errors = [];

        // Validate StoreId
        if (isset($data['StoreId']) && !Store::where('id', $data['StoreId'])->exists()) {
            $errors[] = "Store ID {$data['StoreId']} does not exist";
        }

        // Validate CategoryId
        if (isset($data['CategoryId']) && !Category::where('id', $data['CategoryId'])->exists()) {
            $errors[] = "Category ID {$data['CategoryId']} does not exist";
        }

        // Validate SubCategoryId
        if (isset($data['SubCategoryId']) && !Category::where('id', $data['SubCategoryId'])->exists()) {
            $errors[] = "SubCategory ID {$data['SubCategoryId']} does not exist";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Process item data for database insertion
     */
    public function processItemData(array $normalizedData, string $moduleType = 'food'): array
    {
        $processedData = [
            'original_id' => $normalizedData['Id'] ?? null,
            'name' => $normalizedData['Name'] ?? '',
            'description' => $normalizedData['Description'] ?? '',
            'image' => $normalizedData['Image'] ?? null,
            'images' => $normalizedData['Images'] ?? '[]',
            'category_id' => $this->determineCategoryId($normalizedData),
            'unit_id' => $normalizedData['UnitId'] ?? null,
            'stock' => isset($normalizedData['Stock']) ? (int)$normalizedData['Stock'] : 0,
            'price' => (float)($normalizedData['Price'] ?? 0),
            'discount' => (float)($normalizedData['Discount'] ?? 0),
            'discount_type' => $normalizedData['DiscountType'] ?? 'percent',
            'available_time_starts' => $normalizedData['AvailableTimeStarts'] ?? '00:00:00',
            'available_time_ends' => $normalizedData['AvailableTimeEnds'] ?? '23:59:59',
            'choice_options' => $normalizedData['ChoiceOptions'] ?? '[]',
            'add_ons' => $normalizedData['AddOns'] ?? '[]',
            'attributes' => $normalizedData['Attributes'] ?? '[]',
            'store_id' => $normalizedData['StoreId'] ?? null,
            'module_id' => $normalizedData['ModuleId'] ?? 1,
            'status' => $this->convertStatus($normalizedData['Status'] ?? 'active'),
            'veg' => $this->convertBoolean($normalizedData['Veg'] ?? 'no'),
            'recommended' => $this->convertBoolean($normalizedData['Recommended'] ?? 'no'),
        ];

        // Process variations based on module type
        $variationData = $this->processVariations(
            $normalizedData['Variations'] ?? '[]', 
            $moduleType
        );
        $processedData = array_merge($processedData, $variationData);

        return $processedData;
    }

    /**
     * Determine which category ID to use (SubCategory takes precedence)
     */
    public function determineCategoryId(array $data): ?int
    {
        if (!empty($data['SubCategoryId'])) {
            return (int)$data['SubCategoryId'];
        }
        
        if (!empty($data['CategoryId'])) {
            return (int)$data['CategoryId'];
        }

        return null;
    }

    /**
     * Process variations based on module type
     */
    public function processVariations(string $variations, string $moduleType): array
    {
        if ($moduleType === 'food') {
            return [
                'variations' => json_encode([]),
                'food_variations' => $variations
            ];
        }

        return [
            'variations' => $variations,
            'food_variations' => json_encode([])
        ];
    }

    /**
     * Convert status string to boolean
     */
    private function convertStatus(string $status): int
    {
        return strtolower($status) === 'active' ? 1 : 0;
    }

    /**
     * Convert yes/no string to boolean
     */
    private function convertBoolean(string $value): int
    {
        return strtolower($value) === 'yes' ? 1 : 0;
    }
}