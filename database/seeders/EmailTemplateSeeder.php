<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $email_tempaltes = file_get_contents('database/partial/email_tempaltes.sql');
        DB::statement($email_tempaltes);
    }
}