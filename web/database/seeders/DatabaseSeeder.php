<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Order matters — run in dependency sequence.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,       // 1. Admin account
            SystemSettingsSeeder::class,  // 2. Default system config
            SampleDataSeeder::class,      // 3. Sample courses & students
        ]);
    }
}
