<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Borrower;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ---------- Public ----------
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1'); // brute-force protection
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');

    // Forgot / reset password
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Sends each role to its own dashboard
Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'borrower.dashboard');
})->middleware('auth')->name('dashboard');

// ---------- Profile & settings (both roles) ----------
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->middleware('throttle:6,1')->name('profile.password');
});

// ---------- Administrator ----------
Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('equipment', Admin\EquipmentController::class);
    Route::resource('categories', Admin\CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('borrowers', [Admin\BorrowerController::class, 'index'])->name('borrowers.index');
    Route::get('borrowers/{borrower}', [Admin\BorrowerController::class, 'show'])->name('borrowers.show');
    Route::get('borrowers/{borrower}/edit', [Admin\BorrowerController::class, 'edit'])->name('borrowers.edit');
    Route::put('borrowers/{borrower}', [Admin\BorrowerController::class, 'update'])->name('borrowers.update');
    Route::patch('borrowers/{borrower}/toggle', [Admin\BorrowerController::class, 'toggle'])->name('borrowers.toggle');

    Route::get('requests', [Admin\RequestController::class, 'index'])->name('requests.index');
    Route::get('requests/{borrowingRequest}', [Admin\RequestController::class, 'show'])->name('requests.show');
    Route::post('requests/{borrowingRequest}/approve', [Admin\RequestController::class, 'approve'])->name('requests.approve');
    Route::post('requests/{borrowingRequest}/reject', [Admin\RequestController::class, 'reject'])->name('requests.reject');

    Route::get('returns', [Admin\ReturnController::class, 'index'])->name('returns.index');
    Route::get('returns/{transaction}', [Admin\ReturnController::class, 'create'])->name('returns.create');
    Route::post('returns/{transaction}', [Admin\ReturnController::class, 'store'])->name('returns.store');

    Route::get('history', [Admin\HistoryController::class, 'index'])->name('history');
    Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports');
});

// ---------- Borrower ----------
Route::middleware(['auth', 'active', 'role:borrower'])->prefix('borrower')->name('borrower.')->group(function () {
    Route::get('/', [Borrower\DashboardController::class, 'index'])->name('dashboard');
    Route::get('equipment', [Borrower\EquipmentController::class, 'index'])->name('equipment');
    Route::get('equipment/{equipment}', [Borrower\EquipmentController::class, 'show'])->name('equipment.show');

    Route::get('requests', [Borrower\RequestController::class, 'index'])->name('requests.index');
    Route::get('requests/create', [Borrower\RequestController::class, 'create'])->name('requests.create');
    Route::post('requests', [Borrower\RequestController::class, 'store'])->name('requests.store');
    Route::get('requests/{borrowingRequest}', [Borrower\RequestController::class, 'show'])->name('requests.show');
    Route::patch('requests/{borrowingRequest}/cancel', [Borrower\RequestController::class, 'cancel'])->name('requests.cancel');

    Route::get('history', [Borrower\HistoryController::class, 'index'])->name('history');
});
