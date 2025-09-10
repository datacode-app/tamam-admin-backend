<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Comprehensive error handling for bulk imports
 * Created to solve Issue #7: Ensure complete data insertion with error handling
 */
class BulkImportErrorHandler
{
    public static function logImportError($type, $row, $error, $context = [])
    {
        $log_path = storage_path("logs/bulk_import_errors.log");
        $timestamp = date("Y-m-d H:i:s");
        $row_data = is_array($row) ? json_encode($row) : $row;
        $context_data = !empty($context) ? ' - Context: ' . json_encode($context) : '';
        $message = "[{$timestamp}] {$type} Import Error - Row: {$row_data} - Error: {$error}{$context_data}" . PHP_EOL;
        
        file_put_contents($log_path, $message, FILE_APPEND | LOCK_EX);
        
        // Also log to Laravel's log system
        Log::error("Bulk Import Error - {$type}", [
            'row' => $row,
            'error' => $error,
            'context' => $context
        ]);
    }
    
    public static function validateRequiredFields($data, $required_fields)
    {
        $missing = [];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }
    
    public static function validateStoreData($collection)
    {
        $errors = [];
        
        // Required fields for store import
        $required_fields = [
            'ownerFirstName', 'storeName', 'phone', 'email', 
            'latitude', 'longitude', 'zone_id', 'DeliveryTime', 'Tax'
        ];
        
        $missing = self::validateRequiredFields($collection, $required_fields);
        if (!empty($missing)) {
            $errors[] = 'Missing required fields: ' . implode(', ', $missing);
        }
        
        // Validate email format
        if (isset($collection['email']) && !filter_var($collection['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format: ' . $collection['email'];
        }
        
        // Validate coordinates
        if (isset($collection['latitude'])) {
            $lat = floatval($collection['latitude']);
            if ($lat < -90 || $lat > 90) {
                $errors[] = 'Latitude must be between -90 and 90: ' . $lat;
            }
        }
        
        if (isset($collection['longitude'])) {
            $lng = floatval($collection['longitude']);
            if ($lng < -180 || $lng > 180) {
                $errors[] = 'Longitude must be between -180 and 180: ' . $lng;
            }
        }
        
        // Validate percentages
        if (isset($collection['Comission'])) {
            $commission = floatval($collection['Comission']);
            if ($commission < 0 || $commission > 100) {
                $errors[] = 'Commission must be between 0 and 100: ' . $commission;
            }
        }
        
        if (isset($collection['Tax'])) {
            $tax = floatval($collection['Tax']);
            if ($tax < 0 || $tax > 100) {
                $errors[] = 'Tax must be between 0 and 100: ' . $tax;
            }
        }
        
        return $errors;
    }
    
    public static function validateDeliveryTime($delivery_time)
    {
        if (empty($delivery_time)) {
            return ['Delivery time is required'];
        }
        
        $errors = [];
        $delivery_time_str = trim($delivery_time);
        
        // Remove 'min', 'minutes', 'mins' from string
        $delivery_time_clean = str_replace(['min', 'minutes', 'mins'], '', $delivery_time_str);
        $delivery_time_clean = trim($delivery_time_clean);
        
        if (strpos($delivery_time_clean, '-') !== false) {
            $parts = explode('-', $delivery_time_clean);
            if (count($parts) >= 2) {
                $min_time = intval(trim($parts[0]));
                $max_time = intval(trim($parts[1]));
                
                if ($min_time <= 0 || $max_time <= 0) {
                    $errors[] = 'Delivery times must be positive numbers';
                }
                
                if ($min_time > $max_time) {
                    $errors[] = 'Maximum delivery time must be greater than minimum delivery time';
                }
                
                // Allow reasonable delivery time ranges (5 minutes to 24 hours)
                if ($min_time < 5 || $max_time > 1440) {
                    $errors[] = 'Delivery time must be between 5 minutes and 24 hours';
                }
            } else {
                $errors[] = 'Invalid delivery time format. Use "20-100 min" format';
            }
        } else {
            // Single value format
            $time = intval($delivery_time_clean);
            if ($time <= 0) {
                $errors[] = 'Delivery time must be a positive number';
            }
            if ($time < 5 || $time > 1440) {
                $errors[] = 'Delivery time must be between 5 minutes and 24 hours';
            }
        }
        
        return $errors;
    }
    
    public static function createImportSummaryReport($type, $total_rows, $successful_imports, $failed_imports, $errors = [])
    {
        $report_path = storage_path("logs/bulk_import_summary_" . date('Y-m-d_H-i-s') . ".log");
        
        $summary = "BULK IMPORT SUMMARY REPORT\n";
        $summary .= "==========================\n";
        $summary .= "Import Type: {$type}\n";
        $summary .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $summary .= "Total Rows: {$total_rows}\n";
        $summary .= "Successful: {$successful_imports}\n";
        $summary .= "Failed: {$failed_imports}\n";
        $summary .= "Success Rate: " . round(($successful_imports / $total_rows) * 100, 2) . "%\n\n";
        
        if (!empty($errors)) {
            $summary .= "ERROR DETAILS:\n";
            $summary .= "==============\n";
            foreach ($errors as $index => $error) {
                $summary .= ($index + 1) . ". {$error}\n";
            }
        }
        
        $summary .= "\nEND REPORT\n";
        
        file_put_contents($report_path, $summary);
        
        return $report_path;
    }
}