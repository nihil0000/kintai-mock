<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Models\Attendance;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication (login, logout)
Route::controller(AuthenticatedSessionController::class)->group(function () {
    Route::get('/login', 'create')->name('login.create'); // Display login form
    Route::post('/login', 'store')->name('login.store'); // Authentication (login)
    Route::post('/logout', 'destroy')->name('logout.destroy'); // Logout
});

// Register user
Route::controller(RegisteredUserController::class)->group(function () {
    Route::get('/register', 'create')->name('register.create'); // Display register form
    Route::post('/register', 'store')->name('register.store');  // Register
});

// Display email verification page
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Verify email
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // verify
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Send verification notification
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Attendance
Route::middleware('auth')->controller(AttendanceController::class)->group(function () {
    Route::get('/attendance', 'create')->name('attendance.create'); // Display attendance form
    Route::post('/attendance/start', 'startWork')->name('attendance.start'); // Start work
    Route::post('/attendance/start-break', 'startBreak')->name('attendance.start_break'); // Start break
    Route::post('/attendance/end-break', 'endBreak')->name('attendance.end_break'); // End break
    Route::post('/attendance/end', 'endWork')->name('attendance.end'); // End work
    Route::get('/attendance/list', 'index')->name('attendance.index'); // Display attendance list
});

// Correction request
Route::middleware('auth')->controller(StampCorrectionRequestController::class)->group(function () {
    Route::get('/stamp_correction_request/list', 'index')->name('stamp_correction_request.index'); // Display correction request list
});

/**
 * Admin, general user
 */

// Show attendance detail
Route::middleware('auth:admins,web')
    ->get('attendance/{attendance}', function (Attendance $attendance) {

        return app(\App\Http\Controllers\AttendanceController::class)->show($attendance);
})->name('attendance.show');

// Edit attendance
Route::middleware(['auth:admins,web'])->controller(AttendanceController::class)->group(function () {
    Route::post('attendance/{attendance}/correction', 'store')->name('attendance_correction.store');
});

/**
 * Admin user
 */

// Admin authentication
Route::controller(AdminAuthenticatedSessionController::class)
    ->group(function () {
        Route::get('admin/login', 'create')->name('admin.login.create');
        Route::post('admin/login', 'store')->name('admin.login.store');
        Route::post('admin/logout', 'destroy')->name('admin.logout.destroy');
});

// Admin attendance list
Route::middleware('auth.admins')
    ->controller(AdminAttendanceController::class)
    ->group(function () {
        Route::get('admin/attendance/list', 'index')->name('admin.attendance.index');
        Route::get('admin/attendance/staff/{user}', 'show')->name('admin.attendance.show'); // Show the user attendance monthly
});

// Display staff list
Route::middleware('auth.admins')
    ->controller(AdminStaffController::class)
    ->group(function () {
        Route::get('admin/staff/list', 'index')->name('admin.staff.index');
    });
