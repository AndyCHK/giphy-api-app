<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\EloquentUser;
use App\Infrastructure\Persistence\Eloquent\Models\EloquentUserIdMapping;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserIdMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los usuarios
        $users = EloquentUser::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No hay usuarios para crear mapeos de ID');
            return;
        }
        
        foreach ($users as $user) {
            // Verificar que el ID sea un UUID válido
            if (!Str::isUuid($user->id)) {
                $this->command->warn("El usuario {$user->email} tiene un ID ({$user->id}) que no es un UUID válido. Saltando...");
                continue;
            }
            
            // Verificar si ya existe un mapeo para este usuario
            $existingMapping = EloquentUserIdMapping::where('user_uuid', $user->id)->first();
            
            if (!$existingMapping) {
                // Crear nuevo mapeo
                try {
                    $mapping = new EloquentUserIdMapping();
                    $mapping->user_uuid = $user->id;
                    $mapping->save();
                    
                    $this->command->info("Mapeo creado para usuario {$user->email}: ID #{$mapping->id} -> UUID {$user->id}");
                } catch (\Exception $e) {
                    $this->command->error("Error al crear mapeo para usuario {$user->email}: {$e->getMessage()}");
                }
            } else {
                $this->command->info("Mapeo ya existe para usuario {$user->email}: ID #{$existingMapping->id} -> UUID {$user->id}");
            }
        }
    }
}
