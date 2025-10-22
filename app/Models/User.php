<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'role' ditambahkan ke fillable
    ];

    /**
     * Atribut yang harus disembunyikan saat serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Otomatis hash password
    ];

    /**
     * Relasi: Mendapatkan daftar booking yang terkait dengan email user ini.
     * (Relasi ini tetap menggunakan email sesuai skema database asli)
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'email', 'email');
    }

    /**
     * Fungsi helper untuk mengecek apakah user adalah Admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Fungsi helper untuk mengecek apakah user adalah User biasa.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }
}