<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\EmailVerificationNoticeController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\StaffAccountController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1,registration:')->name('register.store');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::get('/email/verify', EmailVerificationNoticeController::class)->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)
    ->middleware(['signed', 'throttle:6,1,verification:'])->name('verification.verify');
Route::post('/email/verification-notification', EmailVerificationNotificationController::class)
    ->middleware('throttle:6,1,verification-resend:')->name('verification.send');

Route::middleware(['auth', 'verified', 'active'])->group(function (): void {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware('can:manage-staff')->prefix('admin/staff')->name('admin.staff.')->group(function (): void {
        Route::get('/', [StaffAccountController::class, 'index'])->name('index');
        Route::post('/', [StaffAccountController::class, 'store'])->name('store');
    });
});
