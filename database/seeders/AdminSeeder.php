<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create default admin accounts
        $admins = [
            [
                'username' => 'admin',
                'password' => 'admin123',
                'email' => 'admin@licensemanagement.com',
                'is_active' => true,
            ],
            [
                'username' => 'superadmin',
                'password' => 'super123',
                'email' => 'superadmin@licensemanagement.com',
                'is_active' => true,
            ],
        ];

        foreach ($admins as $adminData) {
            Admin::updateOrCreate(
                ['username' => $adminData['username']],
                $adminData
            );
        }

        $this->command->info('Admin accounts created successfully!');
        $this->command->line('Default credentials:');
        $this->command->line('Username: admin, Password: admin123');
        $this->command->line('Username: superadmin, Password: super123');
    }
}