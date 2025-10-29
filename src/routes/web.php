<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Administrator\AdministratorController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\WorkingStatusController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\UserLoginController;
use App\Http\Controllers\Auth\UserRegisterController;
use App\Http\Controllers\DaySelectController;
use Laravel\Fortify\Fortify;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;


    Route::get('/email/verify', function () {
        return view('auth.verification');
    })->name('verification.notice');


    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/user');
    })->middleware(['auth', 'signed'])->name('verification.verify');


    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', '認証メールを再送しました');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    Route::get('/mail-test', function () {
        Mail::raw('Mailhog 接続テスト', function($message) {
            $message->to('test@example.com')
                    ->subject('Mailhog Test');
        });

        return 'メール送信済み。Mailhogを確認してね！';
    });



    Route::middleware(['auth:web', 'verified'])->group(function () {
        Route::get('/auth/verification', function () {
            return view('auth.verification');
        })->name('auth.verification.notice');

        Route::get('/user', [UserController::class, 'index'])->name('user.start.work');

        Route::get('/user/list', [UserController::class, 'workList'] )->name('user.work.list');

        Route::get('/user/list/{id}', [UserController::class, 'userListDetail'])->name('user.work.list.detail');

        Route::get('/user/request', [UserController::class, 'userRequest'] )->name('user.ask.request');

        Route::put('/user/request/{id}', [WorkingStatusController::class, 'attendanceUpdate'] )->name('user.attendance.update');





        Route::post('/attendance/clock-in', [WorkingStatusController::class, 'clockIn'])->name('attendance.clock_in');
        Route::post('/attendance/break-start', [WorkingStatusController::class, 'breakStart'])->name('attendance.break_start');
        Route::post('/attendance/break-end', [WorkingStatusController::class, 'breakEnd'])->name('attendance.break_end');
        Route::post('/attendance/clock-out', [WorkingStatusController::class, 'clockOut'])->name('attendance.clock_out');


    });


/*
|--------------------------------------------------------------------------
| 管理者向けルート
|--------------------------------------------------------------------------
*/

    Route::get('/admin/login', function () {
        return view('auth.admin_login');
    })->name('admin.login');


    Route::post('/admin/login', [AdminLoginController::class, 'authenticate'])->name('admin.login.post');


    Route::middleware(['auth:admin'])->group(function () {

        Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');


        Route::get('/admin/home', [AdministratorController::class, 'attendanceList'])->name('admin.attendance.list');

        Route::get('/attendance/{id}', [AdministratorController::class, 'listDetail'])->name('admin.attendance.detail');

        Route::put('/attendance/{id}', [WorkingStatusController::class, 'adminUpDate'])->name('admin.attendance.update');

        Route::get('/staff_list', [AdministratorController::class, 'staffList'])->name('admin.staff.list');

        Route::get('/staff_list/{id}', [AdministratorController::class, 'staffDetail'])->name('admin.staff.detail');

        Route::get('/staff_list/{id}/csv', [AdministratorController::class, 'exportCsv'])->name('admin.staff.export_csv');


        Route::get('/attendance', [DaySelectController::class, 'daySelect'])->name('attendance.day_select');

        Route::get('/request_list', [AdministratorController::class, 'requestList'])->name('admin.request.list');

        Route::get('/request_list/{id}', [AdministratorController::class, 'requestApprove'])->name('admin.request.approve');

        Route::put('/request_list/{id}/approve', [AdministratorController::class, 'requestPermitted'])->name('admin.request.permitted');


    });

    Route::get('/month-select/{id}', [DaySelectController::class, 'monthSelect'])->name('common.month_select');




Route::get('/dev-logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->name('dev.logout');