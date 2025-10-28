<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'table_id',
        'name',
        'email',
        'phone',
        'message',
        'table_number',
        'booking_date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // get booked table
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    // get user who booked
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}