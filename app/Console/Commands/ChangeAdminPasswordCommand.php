<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;

class ChangeAdminPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:password {username} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change admin user password';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');

        $admin = Admin::where('username', $username)->first();

        if (!$admin) {
            $this->error("Admin user '{$username}' not found.");
            return 1;
        }

        if (strlen($password) < 6) {
            $this->error("Password must be at least 6 characters long.");
            return 1;
        }

        $admin->password = $password; // Will be automatically hashed
        $admin->save();

        $this->info("Password for admin '{$username}' changed successfully!");
        return 0;
    }
}