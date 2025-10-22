<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'table_number',
        'booking_date',
        'start_time',
        'end_time',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     */
    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Relasi: Mendapatkan data meja yang dibooking.
     */
    public function table()
    {
        // Relasi berdasarkan table_number
        return $this->belongsTo(Table::class, 'table_number', 'table_number');
    }

    /**
     * Relasi: Mendapatkan data user (jika ada) yang terkait dengan email booking.
     */
    public function user()
    {
        // Relasi berdasarkan email
        return $this->belongsTo(User::class, 'email', 'email');
    }
}