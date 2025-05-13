<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentUser;
use Illuminate\Support\Str;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EloquentUser::where('email', 'nuevo11@example.com')->delete();
        
        $user = EloquentUser::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Usuario de Prueba',
            'email' => 'nuevo11@example.com',
            'password' => Hash::make('password123'),
            'roles' => ['user'],
            'email_verified_at' => now(),
        ]);
        
        $this->command->info("Usuario de prueba creado: {$user->email}");
        
        $mappingExists = \DB::table('user_id_mappings')
            ->where('user_uuid', $user->id)
            ->exists();
            
        if (!$mappingExists) {
            \DB::table('user_id_mappings')->insert([
                'user_uuid' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $mapping = \DB::table('user_id_mappings')
                ->where('user_uuid', $user->id)
                ->first();
                
            $this->command->info("Mapeo de ID creado: {$mapping->id} -> {$user->id}");
        } else {
            $this->command->info("El mapeo de ID ya existe para el usuario");
        }
    }
}
