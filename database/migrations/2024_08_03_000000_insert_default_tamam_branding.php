<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InsertDefaultTamamBranding extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert default Tamam branding settings
        $defaultSettings = [
            [
                'key' => 'business_name',
                'value' => 'Tamam',
                'storage' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'logo',
                'value' => 'business/tamam-default-logo.png',
                'storage' => json_encode([['key' => 'storage', 'value' => 'public']]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'icon',
                'value' => 'business/tamam-default-icon.png',
                'storage' => json_encode([['key' => 'storage', 'value' => 'public']]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'email_address',
                'value' => 'info@tamam.shop',
                'storage' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'phone',
                'value' => '+964 750 123 4567',
                'storage' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'currency',
                'value' => 'IQD',
                'storage' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'timezone',
                'value' => 'Asia/Baghdad',
                'storage' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'country',
                'value' => 'IQ',
                'storage' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('business_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the default branding settings
        $keys = [
            'business_name', 'logo', 'icon', 'email_address', 
            'phone', 'currency', 'timezone', 'country'
        ];
        
        DB::table('business_settings')->whereIn('key', $keys)->delete();
    }
}