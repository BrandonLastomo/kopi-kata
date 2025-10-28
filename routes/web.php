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

// sign admin
Route::get('/login_admin', [AdminUserController::class, 'loginPage'])->name('login.admin');
Route::post('/login_admin', [AdminUserController::class, 'login'])->name('login.admin.post');

// sign user
Route::get('/register', [UserController::class, 'registerPage'])->name('register');
Route::post('/register', [UserController::class, 'register'])->name('register.post');
Route::get('/login', [UserController::class, 'loginPage'])->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login.post');


Route::middleware('auth')->group(function () {
    // User pages
    Route::get('/', [HomeController::class, 'index'])->name('home');
    // booking page
    Route::get('book', [BookingController::class, 'index'])->name('book.index');
    // store booking
    Route::post('book', [BookingController::class, 'store'])->name('book.store');
    // delete booking
    Route::delete('book/{booking}', [BookingController::class, 'destroy'])->name('book.destroy');

    // Admin pages
    // dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // booking manage
    Route::resource('bookings', AdminBookingController::class);
    // table manage
    Route::resource('tables', AdminTableController::class);
    // user manage
    Route::resource('users', AdminUserController::class);
    
    // Logout
    Route::get('/logout', [UserController::class, 'logout'])->name('logout');
});