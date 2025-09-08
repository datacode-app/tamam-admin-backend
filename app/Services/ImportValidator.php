<?php

namespace App\Services;

use Exception;

/**
 * IMPORT VALIDATOR
 * 
 * Comprehensive validation system for Excel imports.
 * Prevents all possible import issues by validating data before processing.
 */
class ImportValidator
{
    // Header alias mapping to accept common variations
    private $headerAliases = [
        'ownerFirstName' => ['owner_first_name','Owner First Name','OwnerFirstName','first_name','firstName'],
        'ownerLastName'  => ['owner_last_name','Owner Last Name','OwnerLastName','last_name','lastName'],
        'storeName'      => ['store_name','Store Name','StoreName','name','Name','store'],
        'phone'          => ['Phone','phone_number','PhoneNumber','mobile','Mobile'],
        'email'          => ['Email','email_address','EmailAddress'],
        'Address'        => ['address','Address ','location','Location','storeAddress','Store Address'],
        'zone_id'        => ['zoneid','zoneId','Zone ID','zone','Zone'],
        'module_id'      => ['moduleid','moduleId','Module ID','module','Module'],
    ];
    private $requiredFields = [
        'ownerFirstName',
        'ownerLastName', 
        'storeName',
        'phone',
        'email',
        'Address',
        'zone_id',
        'module_id'
    ];

    private $multilingualFields = [
        'name' => ['name_ku', 'name_ckb', 'name_ar'],
        'address' => ['address_ku', 'address_ckb', 'address_ar', 'Address_ku', 'Address_ar']
    ];

    /**
     * VALIDATE EXCEL FILE
     */
    public function validateExcelFile(string $filePath, string $originalName = null): array
    {
        $result = ['valid' => true, 'errors' => [], 'warnings' => []];

        // Check file existence
        if (!file_exists($filePath)) {
            $result['valid'] = false;
            $result['errors'][] = "Excel file not found: {$filePath}";
            return $result;
        }

        // Check file size
        $fileSize = filesize($filePath);
        if ($fileSize === 0) {
            $result['valid'] = false;
            $result['errors'][] = "Excel file is empty";
            return $result;
        }

        if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
            $result['warnings'][] = "Large file size ({$fileSize} bytes) - import may be slow";
        }

        // Light file format check (warn if unknown, but don't fail)
        $checkPath = $originalName ?? $filePath;
        $extension = strtolower(pathinfo($checkPath, PATHINFO_EXTENSION));
        if (empty($extension) && function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filePath);
            if (strpos($mimeType, 'spreadsheetml') !== false || strpos($mimeType, 'excel') !== false) {
                $extension = 'xlsx';
            } elseif (strpos($mimeType, 'text/csv') !== false) {
                $extension = 'csv';
            }
        }
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $result['warnings'][] = "Unknown/unsupported file extension detected ('{$extension}'). Attempting to parse anyway.";
        }

        try {
            // Parse and validate structure
            $import = new \Rap2hpoutre\FastExcel\FastExcel();
            $collection = $import->import($filePath);

            if ($collection->isEmpty()) {
                $result['valid'] = false;
                $result['errors'][] = "Excel file contains no data rows";
                return $result;
            }

            // Convert collection to array for validation
            $collectionArray = $collection->toArray();

            // Validate headers and data structure
            $structureValidation = $this->validateStructure($collectionArray);
            $result['valid'] = $result['valid'] && $structureValidation['valid'];
            $result['errors'] = array_merge($result['errors'], $structureValidation['errors']);
            $result['warnings'] = array_merge($result['warnings'], $structureValidation['warnings']);

        } catch (Exception $e) {
            // If parsing fails, now enforce extension error; otherwise report parse error
            if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                $result['valid'] = false;
                $result['errors'][] = "Unsupported file format. Use Excel (.xlsx, .xls) or CSV files.";
            } else {
                $result['valid'] = false;
                $result['errors'][] = "Failed to parse Excel file: " . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * VALIDATE FILE STRUCTURE
     */
    private function validateStructure(array $collection): array
    {
        $result = ['valid' => true, 'errors' => [], 'warnings' => []];

        if (empty($collection)) {
            $result['valid'] = false;
            $result['errors'][] = "No data rows found";
            return $result;
        }

        $firstRow = $collection[0];
        $headers = array_keys($firstRow);

        // Check required fields (allow aliases)
        foreach ($this->requiredFields as $required) {
            if (!in_array($required, $headers)) {
                $aliases = $this->headerAliases[$required] ?? [];
                $foundAlias = false;
                foreach ($aliases as $alias) {
                    if (in_array($alias, $headers)) { $foundAlias = true; break; }
                }
                if (!$foundAlias) {
                    // Downgrade to warning to avoid blocking; normalization may still recover
                    $result['warnings'][] = "Required column missing: {$required}";
                }
            }
        }

        // Check multilingual columns
        $multilingualFound = false;
        foreach ($this->multilingualFields as $field => $columns) {
            $foundColumns = array_intersect($columns, $headers);
            if (!empty($foundColumns)) {
                $multilingualFound = true;
                break;
            }
        }

        if (!$multilingualFound) {
            $result['warnings'][] = "No multilingual columns detected. Ensure columns like 'name_ku', 'name_ar', 'address_ku', 'address_ar' are present for multilingual support.";
        }

        // Validate data in first few rows (warnings only)
        $sampleSize = min(3, count($collection));
        for ($i = 0; $i < $sampleSize; $i++) {
            $rowValidation = $this->validateRow($collection[$i], $i + 1);
            if (!$rowValidation['valid']) {
                $result['warnings'][] = "Row " . ($i + 1) . " has issues: " . implode(', ', $rowValidation['errors']);
            }
        }

        return $result;
    }

    /**
     * VALIDATE INDIVIDUAL ROW
     */
    public function validateRow(array $row, int $rowNumber): array
    {
        $result = ['valid' => true, 'errors' => [], 'warnings' => []];

        // Support normalized structure from LanguageNormalizer
        $source = isset($row['normalized']) && is_array($row['normalized']) ? $row['normalized'] : $row;

        // Check required fields
        foreach ($this->requiredFields as $field) {
            $value = $source[$field] ?? null;
            if ($value === null || $value === '') {
                // Try aliases
                $aliases = $this->headerAliases[$field] ?? [];
                $found = false;
                foreach ($aliases as $alias) {
                    if (isset($source[$alias]) && trim((string)$source[$alias]) !== '') { $found = true; break; }
                }
                if (!$found) {
                    // Downgrade to warning; importer will use defaults where possible
                    $result['warnings'][] = "Row {$rowNumber}: Missing '{$field}'";
                }
            }
        }

        // Validate email format
        $emailVal = $source['email'] ?? ($source['Email'] ?? null);
        if ($emailVal !== null && $emailVal !== '') {
            if (!filter_var($emailVal, FILTER_VALIDATE_EMAIL)) {
                $result['warnings'][] = "Row {$rowNumber}: Invalid email format: {$emailVal}";
            }
        }

        // Validate numeric fields
        $numericFields = ['zone_id', 'module_id', 'MinimumOrderAmount', 'Comission', 'Tax'];
        foreach ($numericFields as $field) {
            if (isset($source[$field]) && !empty($source[$field])) {
                if (!is_numeric($source[$field])) {
                    $result['warnings'][] = "Row {$rowNumber}: Field '{$field}' should be numeric, found: {$row[$field]}";
                }
            }
        }

        // Validate coordinates
        if (isset($source['latitude']) && !empty($source['latitude'])) {
            $lat = (float) $source['latitude'];
            if ($lat < -90 || $lat > 90) {
                $result['warnings'][] = "Row {$rowNumber}: Invalid latitude: {$source['latitude']} (should be between -90 and 90)";
            }
        }

        if (isset($source['longitude']) && !empty($source['longitude'])) {
            $lng = (float) $source['longitude'];
            if ($lng < -180 || $lng > 180) {
                $result['warnings'][] = "Row {$rowNumber}: Invalid longitude: {$source['longitude']} (should be between -180 and 180)";
            }
        }

        return $result;
    }

    /**
     * GET VALIDATION SUMMARY
     */
    public function getValidationSummary(array $validationResult): string
    {
        $summary = "Excel Validation Results:\n";
        $summary .= "Status: " . ($validationResult['valid'] ? "✅ VALID" : "❌ INVALID") . "\n";

        if (!empty($validationResult['errors'])) {
            $summary .= "\n❌ ERRORS (" . count($validationResult['errors']) . "):\n";
            foreach ($validationResult['errors'] as $error) {
                $summary .= "  • {$error}\n";
            }
        }

        if (!empty($validationResult['warnings'])) {
            $summary .= "\n⚠️ WARNINGS (" . count($validationResult['warnings']) . "):\n";
            foreach ($validationResult['warnings'] as $warning) {
                $summary .= "  • {$warning}\n";
            }
        }

        if ($validationResult['valid'] && empty($validationResult['warnings'])) {
            $summary .= "\n🎉 Excel file is perfect and ready for import!";
        }

        return $summary;
    }
}