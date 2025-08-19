<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:reset-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset passwords for system users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = [
            'admin@medifarma.com' => 'admin123',
            'gp@medifarma.com' => 'gp123',
            'bi@medifarma.com' => 'bi123'
        ];

        foreach ($users as $email => $password) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->password = Hash::make($password);
                $user->is_active = true;
                $user->email_verified_at = now();
                $user->save();
                $this->info("✅ Password reset for: {$email}");
            } else {
                $this->error("❌ User not found: {$email}");
            }
        }

        $this->info("\n🔑 Credentials:");
        $this->info("• admin@medifarma.com → admin123");
        $this->info("• gp@medifarma.com → gp123");
        $this->info("• bi@medifarma.com → bi123");
    }
}
