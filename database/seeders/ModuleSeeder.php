<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create default modules that should be available by default
        $defaultModules = [
            [
                'module_name' => 'Admin Module',
                'module_type' => 'admin',
                'thumbnail' => null,
                'status' => 1, // Active by default
                'stores_count' => 0,
                'icon' => null,
                'theme_id' => 1,
                'description' => 'Administrative module for system management',
                'all_zone_service' => 1,
            ],
            [
                'module_name' => 'Rental Module',
                'module_type' => 'rental',
                'thumbnail' => null,
                'status' => 1, // Active by default
                'stores_count' => 0,
                'icon' => null,
                'theme_id' => 1,
                'description' => 'Car rental and vehicle booking module',
                'all_zone_service' => 0,
            ],
            [
                'module_name' => 'Food Delivery',
                'module_type' => 'food',
                'thumbnail' => null,
                'status' => 1, // Active by default
                'stores_count' => 0,
                'icon' => null,
                'theme_id' => 1,
                'description' => 'Food delivery and restaurant management module',
                'all_zone_service' => 0,
            ],
            [
                'module_name' => 'Grocery',
                'module_type' => 'grocery',
                'thumbnail' => null,
                'status' => 1, // Active by default
                'stores_count' => 0,
                'icon' => null,
                'theme_id' => 1,
                'description' => 'Grocery shopping and delivery module',
                'all_zone_service' => 0,
            ],
            [
                'module_name' => 'Pharmacy',
                'module_type' => 'pharmacy',
                'thumbnail' => null,
                'status' => 1, // Active by default
                'stores_count' => 0,
                'icon' => null,
                'theme_id' => 1,
                'description' => 'Pharmacy and medicine delivery module',
                'all_zone_service' => 0,
            ],
            [
                'module_name' => 'E-commerce',
                'module_type' => 'ecommerce',
                'thumbnail' => null,
                'status' => 1, // Active by default
                'stores_count' => 0,
                'icon' => null,
                'theme_id' => 1,
                'description' => 'E-commerce and online shopping module',
                'all_zone_service' => 1,
            ],
            [
                'module_name' => 'Parcel Delivery',
                'module_type' => 'parcel',
                'thumbnail' => null,
                'status' => 1, // Active by default
                'stores_count' => 0,
                'icon' => null,
                'theme_id' => 1,
                'description' => 'Parcel and package delivery module',
                'all_zone_service' => 0,
            ],
        ];

        foreach ($defaultModules as $moduleData) {
            // Check if module already exists
            $existingModule = Module::where('module_type', $moduleData['module_type'])->first();
            
            if (!$existingModule) {
                Module::create($moduleData);
                $this->command->info("Created {$moduleData['module_name']} module");
            } else {
                $this->command->info("Module {$moduleData['module_name']} already exists, skipping...");
            }
        }
    }
}
