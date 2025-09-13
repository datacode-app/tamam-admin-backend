<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            UserSeeder::class,
            DefaultBrandingSeeder::class,
            BusinessSettingSeeder::class,
            DataSettingSeeder::class,
            EmailTemplateSeeder::class,
            ModuleSeeder::class, // Add default modules (admin, rental, etc.)
        ]);
    }
}
