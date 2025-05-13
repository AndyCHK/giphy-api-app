<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios existentes - Comentados para evitar duplicados
        /*
        EloquentUser::create([
            'id' => (string) Str::uuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => (string) new Password('password123'),
            'roles' => ['admin'],
            'email_verified_at' => now(),
        ]);

        EloquentUser::create([
            'id' => (string) Str::uuid(),
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => (string) new Password('password123'),
            'roles' => ['user'],
            'email_verified_at' => now(),
        ]);

        EloquentUser::factory(5)->create();
        */

        // Añadir usuario de prueba nuevo
        EloquentUser::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => (string) new Password('password'),
            'roles' => ['user'],
            'email_verified_at' => now(),
        ]);
    }
} 