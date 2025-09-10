<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Comprehensive translations table for multilingual support
     * Supports Kurdish (Sorani), Arabic, and extensible language support
     *
     * @return void
     */
    public function up()
    {
        // Drop existing translations table if it exists to recreate properly
        Schema::dropIfExists('translations');
        
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship fields
            $table->string('translationable_type')->index();
            $table->unsignedBigInteger('translationable_id')->index();
            
            // Language and content fields
            $table->string('locale', 10)->index(); // e.g., 'ar', 'ckb', 'en'
            $table->string('key', 100)->index(); // e.g., 'name', 'description', 'address'
            $table->longText('value')->nullable(); // Support for long content
            
            // Metadata fields
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 50)->nullable(); // Track who created translation
            $table->string('updated_by', 50)->nullable(); // Track who updated translation
            
            $table->timestamps();
            
            // Composite indexes for performance
            $table->index(['translationable_type', 'translationable_id'], 'idx_translationable');
            $table->index(['locale', 'key'], 'idx_locale_key');
            $table->index(['translationable_type', 'locale'], 'idx_type_locale');
            
            // Unique constraint to prevent duplicate translations
            $table->unique([
                'translationable_type', 
                'translationable_id', 
                'locale', 
                'key'
            ], 'idx_unique_translation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translations');
    }
};