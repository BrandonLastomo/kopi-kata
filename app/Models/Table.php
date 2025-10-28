<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;
    
    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'table_number',
        'capacity',
        'location',
        'status',
        'description',
    ];

    /**
     * Relasi: Mendapatkan semua booking untuk meja ini.
     */
    public function bookings()
    {
        // Relasi berdasarkan table_number
        return $this->hasMany(Booking::class);
    }
}