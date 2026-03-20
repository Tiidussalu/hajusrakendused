<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_favorite_subject', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image')->nullable();
            $table->text('description');
            $table->string('director');           // teemale omane lisaväli 1
            $table->unsignedSmallInteger('year');  // teemale omane lisaväli 2
            $table->string('genre')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_favorite_subject');
    }
};
