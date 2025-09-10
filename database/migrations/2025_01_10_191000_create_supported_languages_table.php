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
     * Create supported languages table for better multilingual management
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supported_languages', function (Blueprint $table) {
            $table->id();
            
            // Language identification
            $table->string('code', 10)->unique(); // e.g., 'ar', 'ckb', 'en'
            $table->string('name', 100); // e.g., 'Arabic', 'Kurdish Sorani', 'Kurdish'
            $table->string('native_name', 100); // e.g., 'العربية', 'کوردی سۆرانی'
            
            // Language metadata
            $table->string('direction', 3)->default('ltr'); // 'ltr' or 'rtl'
            $table->string('script', 20)->nullable(); // 'latin', 'arabic', 'kurdish'
            $table->string('region', 100)->nullable(); // 'Iraq', 'Kurdistan Region'
            
            // System flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_fallback')->default(false); // For Kurdish variants
            $table->integer('sort_order')->default(0);
            
            // Aliases for compatibility
            $table->json('aliases')->nullable(); // ['kurdish', 'sorani'] for 'ckb'
            
            $table->timestamps();
            
            // Indexes
            $table->index('is_active');
            $table->index('sort_order');
        });
        
        // Insert default supported languages
        $this->insertDefaultLanguages();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supported_languages');
    }
    
    /**
     * Insert default supported languages
     */
    private function insertDefaultLanguages()
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr',
                'script' => 'latin',
                'region' => 'International',
                'is_active' => true,
                'is_default' => true,
                'is_fallback' => false,
                'sort_order' => 1,
                'aliases' => json_encode(['eng', 'english']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'script' => 'arabic',
                'region' => 'Middle East',
                'is_active' => true,
                'is_default' => false,
                'is_fallback' => false,
                'sort_order' => 2,
                'aliases' => json_encode(['ara', 'arabic', 'ar_IQ']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ckb',
                'name' => 'Kurdish Sorani',
                'native_name' => 'کوردی سۆرانی',
                'direction' => 'rtl',
                'script' => 'kurdish',
                'region' => 'Kurdistan Region',
                'is_active' => true,
                'is_default' => false,
                'is_fallback' => true,
                'sort_order' => 3,
                'aliases' => json_encode(['kurdish', 'sorani', 'ckb_IQ']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('supported_languages')->insert($languages);
    }
};