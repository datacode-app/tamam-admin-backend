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
     * Migrate existing single-language data to multilingual structure
     * This preserves existing data while enabling multilingual features
     *
     * @return void
     */
    public function up()
    {
        // Migrate existing store data to translations
        $this->migrateStoreData();
        
        // Migrate existing item data to translations
        $this->migrateItemData();
        
        // Migrate existing category data to translations
        $this->migrateCategoryData();
        
        // Migrate existing banner data to translations
        $this->migrateBannerData();
        
        // Migrate existing coupon data to translations
        $this->migrateCouponData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration is irreversible as it migrates existing data
        // If needed, restore from backup before running this migration
    }
    
    /**
     * Migrate existing store data to translations
     */
    private function migrateStoreData()
    {
        if (!Schema::hasTable('stores')) {
            return;
        }
        
        $stores = DB::table('stores')->select('id', 'name', 'address')->get();
        
        $translations = [];
        foreach ($stores as $store) {
            if (!empty($store->name)) {
                $translations[] = [
                    'translationable_type' => 'App\\Models\\Store',
                    'translationable_id' => $store->id,
                    'locale' => 'en',
                    'key' => 'name',
                    'value' => $store->name,
                    'is_active' => true,
                    'created_by' => 'migration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            if (!empty($store->address)) {
                $translations[] = [
                    'translationable_type' => 'App\\Models\\Store',
                    'translationable_id' => $store->id,
                    'locale' => 'en',
                    'key' => 'address',
                    'value' => $store->address,
                    'is_active' => true,
                    'created_by' => 'migration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if (!empty($translations)) {
            // Use chunks to avoid memory issues with large datasets
            collect($translations)->chunk(1000)->each(function ($chunk) {
                DB::table('translations')->insertOrIgnore($chunk->toArray());
            });
        }
    }
    
    /**
     * Migrate existing item data to translations
     */
    private function migrateItemData()
    {
        if (!Schema::hasTable('items')) {
            return;
        }
        
        $items = DB::table('items')->select('id', 'name', 'description')->get();
        
        $translations = [];
        foreach ($items as $item) {
            if (!empty($item->name)) {
                $translations[] = [
                    'translationable_type' => 'App\\Models\\Item',
                    'translationable_id' => $item->id,
                    'locale' => 'en',
                    'key' => 'name',
                    'value' => $item->name,
                    'is_active' => true,
                    'created_by' => 'migration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            if (!empty($item->description)) {
                $translations[] = [
                    'translationable_type' => 'App\\Models\\Item',
                    'translationable_id' => $item->id,
                    'locale' => 'en',
                    'key' => 'description',
                    'value' => $item->description,
                    'is_active' => true,
                    'created_by' => 'migration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if (!empty($translations)) {
            collect($translations)->chunk(1000)->each(function ($chunk) {
                DB::table('translations')->insertOrIgnore($chunk->toArray());
            });
        }
    }
    
    /**
     * Migrate existing category data to translations
     */
    private function migrateCategoryData()
    {
        if (!Schema::hasTable('categories')) {
            return;
        }
        
        $categories = DB::table('categories')->select('id', 'name')->get();
        
        $translations = [];
        foreach ($categories as $category) {
            if (!empty($category->name)) {
                $translations[] = [
                    'translationable_type' => 'App\\Models\\Category',
                    'translationable_id' => $category->id,
                    'locale' => 'en',
                    'key' => 'name',
                    'value' => $category->name,
                    'is_active' => true,
                    'created_by' => 'migration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if (!empty($translations)) {
            collect($translations)->chunk(1000)->each(function ($chunk) {
                DB::table('translations')->insertOrIgnore($chunk->toArray());
            });
        }
    }
    
    /**
     * Migrate existing banner data to translations
     */
    private function migrateBannerData()
    {
        if (!Schema::hasTable('banners')) {
            return;
        }
        
        $banners = DB::table('banners')->select('id', 'title')->get();
        
        $translations = [];
        foreach ($banners as $banner) {
            if (!empty($banner->title)) {
                $translations[] = [
                    'translationable_type' => 'App\\Models\\Banner',
                    'translationable_id' => $banner->id,
                    'locale' => 'en',
                    'key' => 'title',
                    'value' => $banner->title,
                    'is_active' => true,
                    'created_by' => 'migration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if (!empty($translations)) {
            collect($translations)->chunk(1000)->each(function ($chunk) {
                DB::table('translations')->insertOrIgnore($chunk->toArray());
            });
        }
    }
    
    /**
     * Migrate existing coupon data to translations
     */
    private function migrateCouponData()
    {
        if (!Schema::hasTable('coupons')) {
            return;
        }
        
        $coupons = DB::table('coupons')->select('id', 'title', 'details')->get();
        
        $translations = [];
        foreach ($coupons as $coupon) {
            if (!empty($coupon->title)) {
                $translations[] = [
                    'translationable_type' => 'App\\Models\\Coupon',
                    'translationable_id' => $coupon->id,
                    'locale' => 'en',
                    'key' => 'title',
                    'value' => $coupon->title,
                    'is_active' => true,
                    'created_by' => 'migration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            if (!empty($coupon->details)) {
                $translations[] = [
                    'translationable_type' => 'App\\Models\\Coupon',
                    'translationable_id' => $coupon->id,
                    'locale' => 'en',
                    'key' => 'details',
                    'value' => $coupon->details,
                    'is_active' => true,
                    'created_by' => 'migration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if (!empty($translations)) {
            collect($translations)->chunk(1000)->each(function ($chunk) {
                DB::table('translations')->insertOrIgnore($chunk->toArray());
            });
        }
    }
};