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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); 
            $table->string('email', 100)->unique(); 
            $table->string('password', 255);
            $table->timestamp('email_verified_at')->nullable();
            
            // Tipe data enum yang sudah disesuaikan
            $table->enum('role', ['admin', 'user'])->default('user'); 
            
            $table->rememberToken();
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};