<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add multilingual performance indexes to core translatable tables
     *
     * @return void
     */
    public function up()
    {
        // Add indexes to stores table for multilingual performance
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                // Add index for name field (often used in translation queries)
                if (Schema::hasColumn('stores', 'name')) {
                    $table->index('name', 'idx_stores_name');
                }
                
                // Add index for address field
                if (Schema::hasColumn('stores', 'address')) {
                    $table->index('address', 'idx_stores_address');
                }
                
                // Composite index for common multilingual queries
                if (Schema::hasColumn('stores', 'status') && Schema::hasColumn('stores', 'active')) {
                    $table->index(['status', 'active'], 'idx_stores_status_active');
                }
                
                // Index for zone-based queries (common in multilingual exports)
                if (Schema::hasColumn('stores', 'zone_id')) {
                    $table->index('zone_id', 'idx_stores_zone');
                }
                
                // Index for module-based queries
                if (Schema::hasColumn('stores', 'module_id')) {
                    $table->index('module_id', 'idx_stores_module');
                }
            });
        }
        
        // Add indexes to items table for multilingual performance
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                // Add index for name field
                if (Schema::hasColumn('items', 'name')) {
                    $table->index('name', 'idx_items_name');
                }
                
                // Add index for description field
                if (Schema::hasColumn('items', 'description')) {
                    $table->index('description', 'idx_items_description');
                }
                
                // Composite index for store-based queries
                if (Schema::hasColumn('items', 'store_id') && Schema::hasColumn('items', 'status')) {
                    $table->index(['store_id', 'status'], 'idx_items_store_status');
                }
            });
        }
        
        // Add indexes to categories table for multilingual performance
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                // Add index for name field
                if (Schema::hasColumn('categories', 'name')) {
                    $table->index('name', 'idx_categories_name');
                }
                
                // Add index for parent category queries
                if (Schema::hasColumn('categories', 'parent_id')) {
                    $table->index('parent_id', 'idx_categories_parent');
                }
            });
        }
        
        // Add indexes to banners table for multilingual performance
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                if (Schema::hasColumn('banners', 'title')) {
                    $table->index('title', 'idx_banners_title');
                }
            });
        }
        
        // Add indexes to coupons table for multilingual performance
        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                if (Schema::hasColumn('coupons', 'title')) {
                    $table->index('title', 'idx_coupons_title');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes from stores table
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropIndex('idx_stores_name');
                $table->dropIndex('idx_stores_address');
                $table->dropIndex('idx_stores_status_active');
                $table->dropIndex('idx_stores_zone');
                $table->dropIndex('idx_stores_module');
            });
        }
        
        // Drop indexes from items table
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropIndex('idx_items_name');
                $table->dropIndex('idx_items_description');
                $table->dropIndex('idx_items_store_status');
            });
        }
        
        // Drop indexes from categories table
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('idx_categories_name');
                $table->dropIndex('idx_categories_parent');
            });
        }
        
        // Drop indexes from banners table
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->dropIndex('idx_banners_title');
            });
        }
        
        // Drop indexes from coupons table
        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropIndex('idx_coupons_title');
            });
        }
    }
};