<?php

namespace Modules\Staff\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\LeaveBalance;

class StaffDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BranchStaffSeeder::class,
        ]);
    }
}
