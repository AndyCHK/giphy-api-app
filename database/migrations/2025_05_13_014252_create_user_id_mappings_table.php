<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_id_mappings', function (Blueprint $table) {
            $table->id(); // ID numérico autoincremental (para cumplir con el challenge)
            $table->uuid('user_uuid'); // UUID del usuario en la tabla users
            $table->timestamps();
            
            // Índices y restricciones
            $table->unique('user_uuid');
            
            // Clave foránea que referencia al UUID en la tabla users
            $table->foreign('user_uuid')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_id_mappings');
    }
};
