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
        Schema::create('giphy_id_mappings', function (Blueprint $table) {
            $table->id(); // ID numérico autoincremental (para cumplir con el challenge)
            $table->string('giphy_id', 100); // ID alfanumérico de Giphy
            $table->timestamps();
            
            $table->unique('giphy_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giphy_id_mappings');
    }
};
