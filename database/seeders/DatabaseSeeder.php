<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([SikMasterSeeder::class, SikUserSeeder::class, SikWorkflowSeeder::class]);
        // ===== Test data: 2 REVISION entries from approver =====
        if (app()->environment(['local', 'testing'])) {
            $this->call(SikApproverRevisionSeeder::class);
        }
    }
}
