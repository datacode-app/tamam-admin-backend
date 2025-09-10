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
     * Fix translations table id field to have AUTO_INCREMENT if missing
     *
     * @return void
     */
    public function up()
    {
        // Check if translations table exists
        if (!Schema::hasTable('translations')) {
            // Create the table if it doesn't exist
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('translationable_type');
                $table->unsignedBigInteger('translationable_id');
                $table->string('locale');
                $table->string('key');
                $table->text('value');
                $table->timestamps();
                $table->index(['translationable_type', 'translationable_id']);
            });
        } else {
            // Check if id field has AUTO_INCREMENT
            $result = DB::select("SHOW COLUMNS FROM translations WHERE Field = 'id'");
            
            if (!empty($result)) {
                $idColumn = $result[0];
                $extra = $idColumn->Extra ?? '';
                
                // If AUTO_INCREMENT is missing, fix it
                if (stripos($extra, 'auto_increment') === false) {
                    DB::statement('ALTER TABLE translations MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
                }
            } else {
                // If id column doesn't exist, add it
                Schema::table('translations', function (Blueprint $table) {
                    $table->id()->first();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // We don't want to reverse this fix as it would break the system
        // If needed, the original migration can be rolled back instead
    }
};