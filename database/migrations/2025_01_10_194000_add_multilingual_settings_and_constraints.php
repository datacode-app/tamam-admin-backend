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
     * Add multilingual settings and constraints for data integrity
     *
     * @return void
     */
    public function up()
    {
        // Add multilingual configuration to business_settings
        $this->addMultilingualSettings();
        
        // Add translation validation table
        $this->createTranslationValidationTable();
        
        // Add multilingual audit trail table
        $this->createTranslationAuditTable();
        
        // Add constraints and triggers for data integrity
        $this->addDataIntegrityConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translation_audits');
        Schema::dropIfExists('translation_validations');
        
        // Remove multilingual settings
        DB::table('business_settings')->whereIn('key', [
            'multilingual_enabled',
            'default_language',
            'fallback_language',
            'supported_languages',
            'rtl_languages',
            'translation_cache_enabled',
        ])->delete();
    }
    
    /**
     * Add multilingual configuration settings
     */
    private function addMultilingualSettings()
    {
        $settings = [
            [
                'key' => 'multilingual_enabled',
                'value' => json_encode(true),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_language',
                'value' => json_encode('en'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fallback_language',
                'value' => json_encode('en'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'supported_languages',
                'value' => json_encode(['en', 'ar', 'ckb']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'rtl_languages',
                'value' => json_encode(['ar', 'ckb']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'translation_cache_enabled',
                'value' => json_encode(true),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($settings as $setting) {
            DB::table('business_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
    
    /**
     * Create translation validation table for quality control
     */
    private function createTranslationValidationTable()
    {
        Schema::create('translation_validations', function (Blueprint $table) {
            $table->id();
            
            // Reference to translation
            $table->unsignedBigInteger('translation_id');
            
            // Validation details
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('validator_id', 50)->nullable();
            $table->timestamp('validated_at')->nullable();
            
            // Quality metrics
            $table->json('quality_checks')->nullable(); // grammar, spelling, etc.
            $table->text('validation_notes')->nullable();
            $table->integer('quality_score')->nullable(); // 1-100
            
            // AI validation
            $table->boolean('ai_validated')->default(false);
            $table->json('ai_suggestions')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('translation_id');
            $table->index('status');
            $table->index('validator_id');
            
            // Foreign key constraint
            $table->foreign('translation_id')
                  ->references('id')
                  ->on('translations')
                  ->onDelete('cascade');
        });
    }
    
    /**
     * Create translation audit table for change tracking
     */
    private function createTranslationAuditTable()
    {
        Schema::create('translation_audits', function (Blueprint $table) {
            $table->id();
            
            // Reference to translation
            $table->unsignedBigInteger('translation_id');
            
            // Change tracking
            $table->enum('action', ['created', 'updated', 'deleted', 'imported']);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            
            // User tracking
            $table->string('user_type', 50)->nullable(); // 'admin', 'vendor', 'system'
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 100)->nullable();
            
            // Context
            $table->string('source', 50)->nullable(); // 'manual', 'import', 'api', 'ai'
            $table->json('metadata')->nullable(); // Additional context data
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('translation_id');
            $table->index('action');
            $table->index(['user_type', 'user_id']);
            $table->index('source');
            $table->index('created_at');
            
            // Foreign key constraint
            $table->foreign('translation_id')
                  ->references('id')
                  ->on('translations')
                  ->onDelete('cascade');
        });
    }
    
    /**
     * Add data integrity constraints
     */
    private function addDataIntegrityConstraints()
    {
        // Create stored procedure for translation cleanup
        $cleanupProcedure = "
            CREATE PROCEDURE IF NOT EXISTS CleanupOrphanedTranslations()
            BEGIN
                -- Remove translations for non-existent stores
                DELETE t FROM translations t
                LEFT JOIN stores s ON t.translationable_id = s.id
                WHERE t.translationable_type = 'App\\\\Models\\\\Store'
                AND s.id IS NULL;
                
                -- Remove translations for non-existent items
                DELETE t FROM translations t
                LEFT JOIN items i ON t.translationable_id = i.id
                WHERE t.translationable_type = 'App\\\\Models\\\\Item'
                AND i.id IS NULL;
                
                -- Remove translations for non-existent categories
                DELETE t FROM translations t
                LEFT JOIN categories c ON t.translationable_id = c.id
                WHERE t.translationable_type = 'App\\\\Models\\\\Category'
                AND c.id IS NULL;
            END
        ";
        
        try {
            DB::unprepared($cleanupProcedure);
        } catch (\Exception $e) {
            // MySQL syntax might vary, skip if fails
        }
        
        // Add trigger for translation audit logging (MySQL specific)
        $auditTrigger = "
            CREATE TRIGGER IF NOT EXISTS translation_audit_trigger
            AFTER UPDATE ON translations
            FOR EACH ROW
            BEGIN
                IF OLD.value != NEW.value THEN
                    INSERT INTO translation_audits (
                        translation_id, 
                        action, 
                        old_value, 
                        new_value, 
                        source,
                        created_at,
                        updated_at
                    ) VALUES (
                        NEW.id, 
                        'updated', 
                        OLD.value, 
                        NEW.value, 
                        'trigger',
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ";
        
        try {
            DB::unprepared($auditTrigger);
        } catch (\Exception $e) {
            // MySQL syntax might vary, skip if fails
        }
    }
};