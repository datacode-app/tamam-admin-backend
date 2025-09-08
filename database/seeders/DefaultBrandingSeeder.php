<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DefaultBrandingSeeder extends Seeder
{
    /**
     * Run the database seeds for default Tamam branding.
     *
     * @return void
     */
    public function run()
    {
        // Default Tamam branding settings
        $defaultSettings = [
            // Company Information
            [
                'key' => 'business_name',
                'value' => 'Tamam',
                'storage' => json_encode([])
            ],
            [
                'key' => 'email_address', 
                'value' => 'info@tamam.shop',
                'storage' => json_encode([])
            ],
            [
                'key' => 'phone',
                'value' => '+964 750 123 4567',
                'storage' => json_encode([])
            ],
            
            // Branding Assets
            [
                'key' => 'logo',
                'value' => 'business/tamam-default-logo.png',
                'storage' => json_encode([['key' => 'storage', 'value' => 'public']])
            ],
            [
                'key' => 'icon',
                'value' => 'business/tamam-default-icon.png', 
                'storage' => json_encode([['key' => 'storage', 'value' => 'public']])
            ],
            
            // System Configuration
            [
                'key' => 'currency',
                'value' => 'IQD',
                'storage' => json_encode([])
            ],
            [
                'key' => 'timezone',
                'value' => 'Asia/Baghdad',
                'storage' => json_encode([])
            ],
            [
                'key' => 'country',
                'value' => 'IQ',
                'storage' => json_encode([])
            ],
            [
                'key' => 'site_direction',
                'value' => 'ltr',
                'storage' => json_encode([])
            ],
            
            // App Settings
            [
                'key' => 'app_name',
                'value' => 'Tamam',
                'storage' => json_encode([])
            ],
            [
                'key' => 'meta_title',
                'value' => 'Tamam - Multi-Vendor Delivery Platform',
                'storage' => json_encode([])
            ],
            [
                'key' => 'meta_description',
                'value' => 'Order food, groceries, pharmacy items and more from local stores with Tamam delivery platform.',
                'storage' => json_encode([])
            ]
        ];

        foreach ($defaultSettings as $setting) {
            // Check if storage column exists
            $updateData = [
                'value' => $setting['value'],
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Add storage only if the column exists
            if (Schema::hasColumn('business_settings', 'storage')) {
                $updateData['storage'] = $setting['storage'];
            }
            
            BusinessSetting::updateOrCreate(
                ['key' => $setting['key']],
                $updateData
            );
        }

        $this->command->info('✅ Default Tamam branding settings have been seeded successfully!');
    }
}