<?php

namespace App\Helpers;

/**
 * SAFE HELPER - Systematic Error Prevention
 * 
 * Implements the suggestions provided for preventing count() and other common errors
 */
class SafeHelper
{
    /**
     * Safe count function that prevents null errors
     * 
     * @param mixed $value
     * @return int
     */
    public static function safe_count($value): int
    {
        return is_array($value) || $value instanceof \Countable ? count($value) : 0;
    }

    /**
     * Safe array access with default value
     * 
     * @param array|null $array
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    public static function safe_get($array, $key, $default = null)
    {
        return is_array($array) && isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Safe object property access
     * 
     * @param object|null $object
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    public static function safe_property($object, $property, $default = null)
    {
        return is_object($object) && property_exists($object, $property) ? $object->{$property} : $default;
    }

    /**
     * Validate migration data before processing
     * 
     * @param array $data
     * @param array $requiredFields
     * @return bool
     */
    public static function validate_migration_data(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                \Log::warning("Migration validation failed: Missing field '$field'");
                return false;
            }
        }
        return true;
    }

    /**
     * Safe JSON decode with error handling
     * 
     * @param string|null $json
     * @param array $default
     * @return array
     */
    public static function safe_json_decode($json, array $default = []): array
    {
        if (!is_string($json)) {
            return $default;
        }
        
        $decoded = json_decode($json, true);
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : $default;
    }
}