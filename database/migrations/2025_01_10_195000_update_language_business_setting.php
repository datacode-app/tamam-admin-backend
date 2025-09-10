<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Update the legacy 'language' business setting to use the new standardized language codes.
     * This ensures admin forms display the correct language tabs: ckb, en, ar (no more 'ku').
     */
    public function up(): void
    {
        // Check if the old 'language' business setting exists
        $existingLanguageSetting = DB::table('business_settings')
            ->where('key', 'language')
            ->first();

        if ($existingLanguageSetting) {
            $currentValue = json_decode($existingLanguageSetting->value, true);
            
            // If it contains 'ku', replace with 'ckb' and remove any other deprecated codes
            if (is_array($currentValue)) {
                // Replace 'ku' with 'ckb' and remove 'kmr'
                $updatedLanguages = [];
                foreach ($currentValue as $lang) {
                    if ($lang === 'ku') {
                        $updatedLanguages[] = 'ckb';
                    } elseif (!in_array($lang, ['kmr'])) { // Skip deprecated codes
                        $updatedLanguages[] = $lang;
                    }
                }
                
                // Ensure we have the standard three languages
                $standardLanguages = ['en', 'ar', 'ckb'];
                $finalLanguages = array_unique(array_merge($updatedLanguages, $standardLanguages));
                
                // Sort to ensure consistent order: en, ar, ckb
                sort($finalLanguages);
                
                DB::table('business_settings')
                    ->where('key', 'language')
                    ->update([
                        'value' => json_encode($finalLanguages),
                        'updated_at' => now(),
                    ]);
                    
                // Updated language business setting successfully
            }
        } else {
            // Create the language business setting with our standard languages
            DB::table('business_settings')->insert([
                'key' => 'language',
                'value' => json_encode(['en', 'ar', 'ckb']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Created language business setting successfully
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is safe to keep - it standardizes language codes
        // Rolling back would potentially break the multilingual system
        // Rolling back language standardization is not recommended
    }
};