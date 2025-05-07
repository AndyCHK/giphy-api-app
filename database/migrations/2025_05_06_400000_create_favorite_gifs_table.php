<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_gifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('gif_id');
            $table->string('title')->nullable();
            $table->text('url');
            $table->timestamps();
            
            // Un usuario no puede tener el mismo GIF como favorito más de una vez
            $table->unique(['user_id', 'gif_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_gifs');
    }
}; 