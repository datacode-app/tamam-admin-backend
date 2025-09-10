<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create tables for tracking multilingual import/export operations
     *
     * @return void
     */
    public function up()
    {
        // Create import/export operation logs
        Schema::create('multilingual_import_logs', function (Blueprint $table) {
            $table->id();
            
            // Operation details
            $table->enum('operation_type', ['import', 'export']);
            $table->enum('entity_type', ['store', 'item', 'category', 'banner', 'coupon', 'mixed']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'partial']);
            
            // File details
            $table->string('filename')->nullable();
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable(); // bytes
            $table->string('file_hash')->nullable(); // for integrity checking
            
            // Processing metrics
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('successful_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->integer('skipped_records')->default(0);
            
            // Language details
            $table->json('languages_processed')->nullable(); // ['ar', 'ckb', 'en']
            $table->json('translation_counts')->nullable(); // per-language counts
            
            // Performance metrics
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('processing_time_seconds')->nullable();
            $table->float('memory_peak_mb')->nullable();
            
            // User and system info
            $table->string('user_type', 50)->nullable(); // 'admin', 'vendor'
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 100)->nullable();
            $table->ipAddress('ip_address')->nullable();
            
            // Error handling
            $table->json('errors')->nullable(); // Error details array
            $table->json('warnings')->nullable(); // Warning details array
            $table->text('error_summary')->nullable();
            
            // Configuration
            $table->json('import_config')->nullable(); // Settings used for import
            $table->json('export_config')->nullable(); // Settings used for export
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('operation_type');
            $table->index('entity_type');
            $table->index('status');
            $table->index(['user_type', 'user_id']);
            $table->index('started_at');
            $table->index('completed_at');
        });
        
        // Create detailed record-level logs
        Schema::create('multilingual_import_records', function (Blueprint $table) {
            $table->id();
            
            // Reference to main import log
            $table->unsignedBigInteger('import_log_id');
            
            // Record details
            $table->integer('row_number'); // Row in CSV/Excel
            $table->string('entity_type', 50); // 'store', 'item', etc.
            $table->unsignedBigInteger('entity_id')->nullable(); // Created/updated entity ID
            $table->enum('status', ['success', 'failed', 'skipped', 'warning']);
            
            // Data processed
            $table->json('original_data')->nullable(); // Raw CSV row data
            $table->json('processed_data')->nullable(); // Cleaned/processed data
            $table->json('translations_created')->nullable(); // Translation records created
            
            // Error/warning details
            $table->text('error_message')->nullable();
            $table->text('warning_message')->nullable();
            $table->json('validation_errors')->nullable();
            
            // Language-specific results
            $table->json('language_results')->nullable(); // Per-language success/failure
            
            $table->timestamps();
            
            // Indexes
            $table->index('import_log_id');
            $table->index('row_number');
            $table->index('entity_type');
            $table->index('entity_id');
            $table->index('status');
            
            // Foreign key
            $table->foreign('import_log_id')
                  ->references('id')
                  ->on('multilingual_import_logs')
                  ->onDelete('cascade');
        });
        
        // Create export statistics table
        Schema::create('multilingual_export_stats', function (Blueprint $table) {
            $table->id();
            
            // Time period
            $table->date('export_date');
            $table->string('period_type', 20); // 'daily', 'weekly', 'monthly'
            
            // Entity statistics
            $table->string('entity_type', 50);
            $table->integer('total_entities')->default(0);
            $table->integer('entities_with_translations')->default(0);
            $table->float('translation_coverage_percent')->default(0);
            
            // Language statistics
            $table->json('language_stats')->nullable(); // Per-language statistics
            $table->string('most_translated_language', 10)->nullable();
            $table->string('least_translated_language', 10)->nullable();
            
            // Quality metrics
            $table->integer('empty_translations')->default(0);
            $table->integer('duplicate_translations')->default(0);
            $table->integer('validated_translations')->default(0);
            
            $table->timestamps();
            
            // Unique constraint for period tracking
            $table->unique(['export_date', 'period_type', 'entity_type'], 'idx_unique_export_period');
            
            // Indexes
            $table->index('export_date');
            $table->index('period_type');
            $table->index('entity_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('multilingual_export_stats');
        Schema::dropIfExists('multilingual_import_records');
        Schema::dropIfExists('multilingual_import_logs');
    }
};