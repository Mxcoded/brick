<?php

namespace Modules\Staff\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\LeaveBalance;

class BranchStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asokoro Branch Staff
        $asokoroStaff = [
            [
                'name' => 'Adamu Mohammed',
                'email' => 'adamu.mohammed@brickspoint.com',
                'position' => 'Front Desk Officer',
                'department' => 'Front Office',
                'gender' => 'Male',
            ],
            [
                'name' => 'Fatima Ibrahim',
                'email' => 'fatima.ibrahim@brickspoint.com',
                'position' => 'Housekeeping Supervisor',
                'department' => 'Housekeeping',
                'gender' => 'Female',
            ],
            [
                'name' => 'Chukwuemeka Okonkwo',
                'email' => 'chukwuemeka.okonkwo@brickspoint.com',
                'position' => 'Maintenance Technician',
                'department' => 'Maintenance',
                'gender' => 'Male',
            ],
            [
                'name' => 'Aisha Bello',
                'email' => 'aisha.bello@brickspoint.com',
                'position' => 'Guest Relations Officer',
                'department' => 'Front Office',
                'gender' => 'Female',
            ],
            [
                'name' => 'Taiwo Adeleke',
                'email' => 'taiwo.adeleke@brickspoint.com',
                'position' => 'Night Auditor',
                'department' => 'Front Office',
                'gender' => 'Male',
            ],
        ];

        // Wuse Branch Staff
        $wuseStaff = [
            [
                'name' => 'Ngozi Eze',
                'email' => 'ngozi.eze@brickspoint.com',
                'position' => 'Branch Manager',
                'department' => 'Management',
                'gender' => 'Female',
            ],
            [
                'name' => 'Yusuf Abdullahi',
                'email' => 'yusuf.abdullahi@brickspoint.com',
                'position' => 'Front Desk Officer',
                'department' => 'Front Office',
                'gender' => 'Male',
            ],
            [
                'name' => 'Blessing Okoro',
                'email' => 'blessing.okoro@brickspoint.com',
                'position' => 'Housekeeping Attendant',
                'department' => 'Housekeeping',
                'gender' => 'Female',
            ],
            [
                'name' => 'Emeka Nwachukwu',
                'email' => 'emeka.nwachukwu@brickspoint.com',
                'position' => 'Security Officer',
                'department' => 'Security',
                'gender' => 'Male',
            ],
            [
                'name' => 'Halima Suleiman',
                'email' => 'halima.suleiman@brickspoint.com',
                'position' => 'Receptionist',
                'department' => 'Front Office',
                'gender' => 'Female',
            ],
        ];

        // Create Asokoro Branch Staff
        $this->createStaffForBranch($asokoroStaff, 'Asokoro');

        // Create Wuse Branch Staff
        $this->createStaffForBranch($wuseStaff, 'Wuse');

        $this->command->info('Branch staff seeded successfully!');
        $this->command->info('- Asokoro: '.count($asokoroStaff).' staff members');
        $this->command->info('- Wuse: '.count($wuseStaff).' staff members');
    }

    /**
     * Create staff members for a specific branch.
     */
    private function createStaffForBranch(array $staffList, string $branchName): void
    {
        foreach ($staffList as $staffData) {
            // Skip if email already exists
            if (Employee::where('email', $staffData['email'])->exists()) {
                $this->command->warn("Skipping {$staffData['name']} - email already exists");

                continue;
            }

            // Generate unique staff code
            do {
                $staffCode = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            } while (Employee::where('staff_code', $staffCode)->exists());

            $employee = Employee::create([
                'name' => $staffData['name'],
                'email' => $staffData['email'],
                'position' => $staffData['position'],
                'department' => $staffData['department'],
                'gender' => $staffData['gender'],
                'branch_name' => $branchName,
                'status' => 'approved',
                'staff_code' => $staffCode,
                'phone_number' => $this->generatePhoneNumber(),
                'date_of_birth' => $this->generateDOB(),
                'place_of_birth' => 'Abuja',
                'state_of_origin' => $this->getRandomState(),
                'lga' => 'Municipal',
                'nationality' => 'Nigerian',
                'marital_status' => $this->getRandomMaritalStatus(),
                'blood_group' => $this->getRandomBloodGroup(),
                'genotype' => $this->getRandomGenotype(),
                'residential_address' => 'Plot '.rand(1, 500).", {$branchName} District, Abuja",
                'next_of_kin_name' => $this->generateNextOfKinName($staffData['gender']),
                'next_of_kin_phone' => $this->generatePhoneNumber(),
                'ice_contact_name' => $this->generateNextOfKinName($staffData['gender']),
                'ice_contact_phone' => $this->generatePhoneNumber(),
                'start_date' => now()->subMonths(rand(6, 36))->format('Y-m-d'),
            ]);

            // Add default leave balances
            $this->createLeaveBalances($employee);
        }
    }

    /**
     * Create default leave balances for an employee.
     */
    private function createLeaveBalances(Employee $employee): void
    {
        $defaultBalances = [
            ['leave_type' => 'Annual', 'total_days' => 21],
            ['leave_type' => 'Casual', 'total_days' => 5],
            ['leave_type' => 'Compassionate', 'total_days' => 3],
            ['leave_type' => 'Sick', 'total_days' => 3],
            ['leave_type' => 'Paternity', 'total_days' => 14],
            ['leave_type' => 'Maternity', 'total_days' => 84],
        ];

        foreach ($defaultBalances as $balance) {
            LeaveBalance::create([
                'employee_id' => $employee->id,
                'leave_type' => $balance['leave_type'],
                'year' => date('Y'),
                'total_days' => $balance['total_days'],
                'used_days' => 0,
                'remaining_days' => $balance['total_days'],
            ]);
        }
    }

    private function generatePhoneNumber(): string
    {
        $prefixes = ['0803', '0806', '0813', '0816', '0903', '0906', '0913'];

        return $prefixes[array_rand($prefixes)].rand(1000000, 9999999);
    }

    private function generateDOB(): string
    {
        return now()->subYears(rand(22, 45))->subDays(rand(0, 365))->format('Y-m-d');
    }

    private function getRandomState(): string
    {
        $states = ['Lagos', 'Kano', 'Kaduna', 'Enugu', 'Rivers', 'Delta', 'Oyo', 'Anambra', 'Borno', 'Plateau', 'FCT'];

        return $states[array_rand($states)];
    }

    private function getRandomMaritalStatus(): string
    {
        $statuses = ['Single', 'Married', 'Single', 'Married', 'Single']; // Weighted towards single/married

        return $statuses[array_rand($statuses)];
    }

    private function getRandomBloodGroup(): string
    {
        $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        return $groups[array_rand($groups)];
    }

    private function getRandomGenotype(): string
    {
        $genotypes = ['AA', 'AS', 'SS', 'AC'];

        return $genotypes[array_rand($genotypes)];
    }

    private function generateNextOfKinName(string $gender): string
    {
        $maleNames = ['Musa', 'Ibrahim', 'Chidi', 'Kunle', 'Emeka', 'Usman', 'Tunde', 'Adekunle'];
        $femaleNames = ['Amina', 'Grace', 'Chiamaka', 'Bukola', 'Fatima', 'Ngozi', 'Yetunde', 'Adaeze'];
        $surnames = ['Okonkwo', 'Ibrahim', 'Adeleke', 'Mohammed', 'Bello', 'Eze', 'Okoro', 'Nwachukwu'];

        // Pick opposite gender for variety
        $firstName = $gender === 'Male'
            ? $femaleNames[array_rand($femaleNames)]
            : $maleNames[array_rand($maleNames)];

        return $firstName.' '.$surnames[array_rand($surnames)];
    }
}
