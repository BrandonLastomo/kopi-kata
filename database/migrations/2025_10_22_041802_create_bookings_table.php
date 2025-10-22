<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Opsional, jika terhubung ke tabel users
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->integer('table_number');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('message')->nullable();
            $table->timestamps();

            // Index untuk mempercepat query pencarian ketersediaan
            $table->index(['booking_date', 'table_number']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};