<?php

namespace App\Providers;

use Illuminate\Validation\ValidationException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Http\Requests\Auth\UserRegisterRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;
use App\Models\Admin;
use App\Models\User;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Illuminate\Support\Facades\Validator;
use App\Actions\Fortify\CreateNewUser;


class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot()
    {
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email.$request->ip());
        });

        Fortify::loginView(function () {
            $request = request();

            if ($request->is('admin/login')) {
                return view('auth.admin_login');
            }

            return view('auth.user_login');
        });


        Fortify::authenticateUsing(function (Request $request) {

            $userLoginRequest = new UserLoginRequest();
            $validator = \Validator::make($request->all(), $userLoginRequest->rules(), $userLoginRequest->messages());
            $validator->validate();

            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            throw ValidationException::withMessages([
                'email' => ['ログイン情報が登録されていません'],
            ]);

        });

        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
                public function toResponse($request)
                {
                    return redirect()->route('user.start.work');
                }
            });

        Fortify::registerView(function () {
            return view('auth.user_register');
        });

        Fortify::loginView(function () {
            return view('auth.user_login');
        });

        $this->app->singleton(
            \Laravel\Fortify\Contracts\CreatesNewUsers::class,
            CreateNewUser::class
        );

        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                $request->user()->sendEmailVerificationNotification();
                return redirect()->route('verification.notice');
            }
        });

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                return redirect()->route('login');
            }
        });
    }
}