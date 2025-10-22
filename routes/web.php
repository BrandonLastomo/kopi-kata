<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\BookingController;

// --- Controller Admin ---
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\TableController as AdminTableController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

Route::get('/login_admin_page', [AdminUserController::class, 'loginPage'])->name('login.admin');
Route::post('/login_admin', [AdminUserController::class, 'login'])->name('login.admin.post');

Route::get('/register', [UserController::class, 'registerPage'])->name('register');
Route::post('/register', [UserController::class, 'register'])->name('register.post');
Route::get('/login', [UserController::class, 'loginPage'])->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login.post');

Route::get('/', [HomeController::class, 'index'])->name('home');

// Rute yang memerlukan login (menggantikan auth_check.php)
Route::middleware('auth')->group(function () {
    // Halaman Booking (book.php)
    Route::get('book', [BookingController::class, 'index'])->name('book.index');
    
    // Proses form booking (book.php POST)
    Route::post('book', [BookingController::class, 'store'])->name('book.store');

    // Hapus booking (delete_booking.php)
    Route::delete('book/{booking}', [BookingController::class, 'destroy'])->name('book.destroy');
    // Logout
    Route::get('/logout', [UserController::class, 'logout'])->name('logout');
    
    // Dashboard (admin_dashboard.php)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Kelola Bookings (admin_bookings.php, admin_edit_booking.php, dll)
    Route::resource('bookings', AdminBookingController::class);

    // Kelola Tables (admin_tables.php)
    Route::resource('tables', AdminTableController::class);

    // Kelola Users (admin_users.php)
    Route::resource('users', AdminUserController::class);
    // Logout
    Route::get('/logout', [UserController::class, 'logout'])->name('logout');
});