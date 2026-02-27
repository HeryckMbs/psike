<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@psike.com');
        $adminPassword = env('ADMIN_PASSWORD', 'admin123');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrador',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        // Atribuir role de admin se usar Spatie Permission
        if (method_exists($admin, 'assignRole')) {
            try {
                $admin->assignRole('admin');
            } catch (\Exception $e) {
                // Role pode não existir ainda, ignorar
            }
        }

        $this->command->info("Admin user created/updated:");
        $this->command->info("Email: {$adminEmail}");
        $this->command->info("Password: {$adminPassword}");
    }
}
