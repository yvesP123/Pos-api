<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {username} {password} {--email=} {--inactive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');
        $email = $this->option('email');
        $isActive = !$this->option('inactive');

        // Validate input
        $validator = Validator::make([
            'username' => $username,
            'password' => $password,
            'email' => $email,
        ], [
            'username' => 'required|string|max:255|unique:admins,username',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|unique:admins,email',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("- $error");
            }
            return 1;
        }

        try {
            $admin = Admin::create([
                'username' => $username,
                'password' => $password, // Will be automatically hashed
                'email' => $email,
                'is_active' => $isActive,
            ]);

            $this->info("Admin user '{$username}' created successfully!");
            $this->line("ID: {$admin->id}");
            $this->line("Username: {$admin->username}");
            $this->line("Email: " . ($admin->email ?: 'Not set'));
            $this->line("Status: " . ($admin->is_active ? 'Active' : 'Inactive'));

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to create admin user: " . $e->getMessage());
            return 1;
        }
    }
}

