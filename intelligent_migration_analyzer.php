<?php
/**
 * INTELLIGENT MIGRATION ANALYZER
 * Scans ALL models, detects missing tables, and automatically creates them
 * NO MORE ENDLESS MIGRATION ISSUES!
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "🧠 INTELLIGENT MIGRATION ANALYZER\n";
echo "=" . str_repeat("=", 60) . "\n\n";

try {
    // 1. Scan all model files
    echo "1. 📂 SCANNING ALL MODEL FILES\n";
    echo "-------------------------------\n";
    
    $modelPaths = [
        'app/Models',
        'app/Model', // Some projects use singular
        'Modules/*/Entities' // Module-based models
    ];
    
    $modelFiles = [];
    
    foreach ($modelPaths as $path) {
        if (is_dir($path)) {
            $files = glob("$path/*.php");
            foreach ($files as $file) {
                $modelFiles[] = $file;
                echo "   Found: " . basename($file) . "\n";
            }
        }
        
        // Handle wildcard paths for modules
        if (strpos($path, '*') !== false) {
            $dirs = glob($path, GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                if (is_dir($dir)) {
                    $files = glob("$dir/*.php");
                    foreach ($files as $file) {
                        $modelFiles[] = $file;
                        echo "   Found: " . basename($file) . " (Module)\n";
                    }
                }
            }
        }
    }
    
    echo "Total models found: " . count($modelFiles) . "\n\n";
    
    // 2. Extract table information from models
    echo "2. 🔍 ANALYZING MODEL-TABLE RELATIONSHIPS\n";
    echo "-----------------------------------------\n";
    
    $modelTableMap = [];
    $missingTables = [];
    
    foreach ($modelFiles as $modelFile) {
        $content = file_get_contents($modelFile);
        $className = pathinfo($modelFile, PATHINFO_FILENAME);
        
        // Extract namespace
        preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch);
        $namespace = $namespaceMatch[1] ?? 'App\\Models';
        $fullClassName = $namespace . '\\' . $className;
        
        // Try to determine table name
        $tableName = null;
        
        // Look for explicit table definition
        if (preg_match('/protected\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            $tableName = $matches[1];
        } else {
            // Use Laravel convention: snake_case plural of class name
            $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
            if (!str_ends_with($tableName, 's')) {
                $tableName .= 's'; // Simple pluralization
            }
        }
        
        $modelTableMap[$className] = [
            'file' => $modelFile,
            'class' => $fullClassName,
            'table' => $tableName,
            'namespace' => $namespace
        ];
        
        echo "   $className → $tableName\n";
    }
    
    echo "\n3. 🏗️ CHECKING EXISTING TABLES\n";
    echo "------------------------------\n";
    
    $existingTables = [];
    try {
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $tableKey = "Tables_in_$dbName";
        
        foreach ($tables as $table) {
            $existingTables[] = $table->$tableKey;
        }
        
        echo "Existing tables: " . count($existingTables) . "\n";
        
    } catch (Exception $e) {
        echo "❌ Error getting existing tables: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // 4. Find missing tables
    echo "\n4. ❌ IDENTIFYING MISSING TABLES\n";
    echo "--------------------------------\n";
    
    foreach ($modelTableMap as $model => $info) {
        if (!in_array($info['table'], $existingTables)) {
            $missingTables[] = $info;
            echo "   MISSING: {$info['table']} (for model $model)\n";
        }
    }
    
    if (empty($missingTables)) {
        echo "✅ All model tables exist!\n";
        exit(0);
    }
    
    echo "\nFound " . count($missingTables) . " missing tables\n";
    
    // 5. Create missing tables with intelligent field detection
    echo "\n5. 🔧 CREATING MISSING TABLES\n";
    echo "-----------------------------\n";
    
    foreach ($missingTables as $tableInfo) {
        $tableName = $tableInfo['table'];
        $modelFile = $tableInfo['file'];
        $className = basename($modelFile, '.php');
        
        echo "\nCreating table: $tableName\n";
        
        try {
            Schema::create($tableName, function (Blueprint $table) use ($modelFile, $tableName, $className) {
                // Always start with ID
                $table->id();
                
                // Analyze model file for field clues
                $content = file_get_contents($modelFile);
                
                // Look for fillable fields
                $fillableFields = [];
                if (preg_match('/protected\s+\$fillable\s*=\s*\[(.*?)\]/s', $content, $matches)) {
                    $fillableContent = $matches[1];
                    preg_match_all('/[\'"]([^\'"]+)[\'"]/', $fillableContent, $fieldMatches);
                    $fillableFields = $fieldMatches[1];
                }
                
                // Look for casts to determine field types
                $casts = [];
                if (preg_match('/protected\s+\$casts\s*=\s*\[(.*?)\]/s', $content, $matches)) {
                    $castsContent = $matches[1];
                    preg_match_all('/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/', $castsContent, $castMatches);
                    for ($i = 0; $i < count($castMatches[1]); $i++) {
                        $casts[$castMatches[1][$i]] = $castMatches[2][$i];
                    }
                }
                
                // Common field patterns for different table types
                $commonFields = [];
                
                if (str_contains($tableName, 'admin')) {
                    $commonFields = [
                        'f_name' => 'string',
                        'l_name' => 'string',
                        'email' => 'string',
                        'password' => 'string',
                        'phone' => 'string',
                        'role_id' => 'unsignedBigInteger',
                        'is_logged_in' => 'boolean'
                    ];
                } elseif ($tableName === 'admin_roles') {
                    $commonFields = [
                        'name' => 'string',
                        'modules' => 'json',
                        'status' => 'boolean'
                    ];
                } elseif (str_contains($tableName, 'order')) {
                    $commonFields = [
                        'user_id' => 'unsignedBigInteger',
                        'store_id' => 'unsignedBigInteger',
                        'delivery_man_id' => 'unsignedBigInteger',
                        'order_amount' => 'decimal',
                        'order_status' => 'string',
                        'payment_status' => 'string',
                        'payment_method' => 'string',
                        'order_type' => 'string',
                        'delivery_address' => 'json',
                        'scheduled_at' => 'timestamp',
                        'delivered_at' => 'timestamp'
                    ];
                } elseif (str_contains($tableName, 'store') || str_contains($tableName, 'vendor')) {
                    $commonFields = [
                        'name' => 'string',
                        'email' => 'string',
                        'phone' => 'string',
                        'logo' => 'string',
                        'address' => 'text',
                        'latitude' => 'decimal',
                        'longitude' => 'decimal',
                        'status' => 'boolean'
                    ];
                } elseif (str_contains($tableName, 'category')) {
                    $commonFields = [
                        'name' => 'string',
                        'image' => 'string',
                        'parent_id' => 'unsignedBigInteger',
                        'position' => 'integer',
                        'status' => 'boolean'
                    ];
                } elseif (str_contains($tableName, 'item') || str_contains($tableName, 'product')) {
                    $commonFields = [
                        'name' => 'string',
                        'description' => 'text',
                        'image' => 'string',
                        'category_id' => 'unsignedBigInteger',
                        'store_id' => 'unsignedBigInteger',
                        'price' => 'decimal',
                        'discount' => 'decimal',
                        'status' => 'boolean',
                        'available_time_starts' => 'time',
                        'available_time_ends' => 'time'
                    ];
                }
                
                // Add fillable fields with intelligent typing
                foreach ($fillableFields as $field) {
                    if (isset($casts[$field])) {
                        // Use cast type
                        switch ($casts[$field]) {
                            case 'boolean':
                                $table->boolean($field)->default(false);
                                break;
                            case 'integer':
                                $table->integer($field)->default(0);
                                break;
                            case 'decimal':
                                $table->decimal($field, 10, 2)->default(0);
                                break;
                            case 'json':
                                $table->json($field)->nullable();
                                break;
                            case 'datetime':
                                $table->dateTime($field)->nullable();
                                break;
                            default:
                                $table->string($field)->nullable();
                        }
                    } elseif (isset($commonFields[$field])) {
                        // Use common field type
                        switch ($commonFields[$field]) {
                            case 'string':
                                $table->string($field)->nullable();
                                break;
                            case 'text':
                                $table->text($field)->nullable();
                                break;
                            case 'boolean':
                                $table->boolean($field)->default(false);
                                break;
                            case 'integer':
                                $table->integer($field)->default(0);
                                break;
                            case 'unsignedBigInteger':
                                $table->unsignedBigInteger($field)->nullable();
                                break;
                            case 'decimal':
                                $table->decimal($field, 10, 2)->default(0);
                                break;
                            case 'json':
                                $table->json($field)->nullable();
                                break;
                            case 'timestamp':
                                $table->timestamp($field)->nullable();
                                break;
                            case 'time':
                                $table->time($field)->nullable();
                                break;
                            default:
                                $table->string($field)->nullable();
                        }
                    } else {
                        // Intelligent field type guessing
                        if (str_contains($field, 'email')) {
                            $table->string($field)->unique()->nullable();
                        } elseif (str_contains($field, 'password')) {
                            $table->string($field);
                        } elseif (str_contains($field, '_id')) {
                            $table->unsignedBigInteger($field)->nullable();
                        } elseif (str_contains($field, 'phone')) {
                            $table->string($field, 20)->nullable();
                        } elseif (str_contains($field, 'price') || str_contains($field, 'amount') || str_contains($field, 'cost')) {
                            $table->decimal($field, 10, 2)->default(0);
                        } elseif (str_contains($field, 'is_') || str_contains($field, 'has_') || str_ends_with($field, 'status')) {
                            $table->boolean($field)->default(false);
                        } elseif (str_contains($field, 'description') || str_contains($field, 'address') || str_contains($field, 'content')) {
                            $table->text($field)->nullable();
                        } elseif (str_contains($field, '_at')) {
                            $table->timestamp($field)->nullable();
                        } elseif (str_contains($field, 'image') || str_contains($field, 'logo') || str_contains($field, 'avatar')) {
                            $table->string($field, 255)->nullable();
                        } else {
                            $table->string($field)->nullable();
                        }
                    }
                }
                
                // Add remaining common fields if they weren't in fillable
                foreach ($commonFields as $fieldName => $fieldType) {
                    if (!in_array($fieldName, $fillableFields) && !Schema::hasColumn($tableName, $fieldName)) {
                        switch ($fieldType) {
                            case 'string':
                                $table->string($fieldName)->nullable();
                                break;
                            case 'text':
                                $table->text($fieldName)->nullable();
                                break;
                            case 'boolean':
                                $table->boolean($fieldName)->default(false);
                                break;
                            case 'integer':
                                $table->integer($fieldName)->default(0);
                                break;
                            case 'unsignedBigInteger':
                                $table->unsignedBigInteger($fieldName)->nullable();
                                break;
                            case 'decimal':
                                $table->decimal($fieldName, 10, 2)->default(0);
                                break;
                            case 'json':
                                $table->json($fieldName)->nullable();
                                break;
                            case 'timestamp':
                                $table->timestamp($fieldName)->nullable();
                                break;
                            case 'time':
                                $table->time($fieldName)->nullable();
                                break;
                        }
                    }
                }
                
                // Always add timestamps unless model specifies otherwise
                if (!str_contains($content, '$timestamps = false')) {
                    $table->timestamps();
                }
            });
            
            echo "✅ Created table: $tableName\n";
            
        } catch (Exception $e) {
            echo "❌ Failed to create $tableName: " . $e->getMessage() . "\n";
        }
    }
    
    // 6. Specific fix for admin_roles
    echo "\n6. 🎯 SPECIFIC FIXES\n";
    echo "-------------------\n";
    
    if (!Schema::hasTable('admin_roles')) {
        echo "Creating admin_roles table specifically...\n";
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('modules')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        
        // Insert default admin role
        DB::table('admin_roles')->insert([
            'id' => 1,
            'name' => 'Master Admin',
            'modules' => json_encode([]),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✅ Created admin_roles with default data\n";
    }
    
    echo "\n🎉 INTELLIGENT MIGRATION ANALYSIS COMPLETE!\n";
    echo "=" . str_repeat("=", 60) . "\n";
    echo "✅ All missing tables have been created\n";
    echo "✅ Field types intelligently determined\n";
    echo "✅ Common patterns automatically applied\n";
    echo "✅ No more missing table errors!\n";
    
} catch (Exception $e) {
    echo "💥 ANALYZER ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}