<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AttendanceRequest;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $users = [
            [
                'user_name' => 'サンプル太郎',
                'email' => 'sample@example.com',
                'password' => Hash::make('sample12345'),
                'email_verified_at' => $now,
                'two_factor_confirmed_at' => $now,
            ],
            [
                'user_name' => '田中太郎',
                'email' => 'tanaka@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
                'two_factor_confirmed_at' => $now,
            ],
            [
                'user_name' => '鈴木次郎',
                'email' => 'suzuki@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
                'two_factor_confirmed_at' => $now,
            ],
            [
                'user_name' => '佐藤花子',
                'email' => 'sato@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
                'two_factor_confirmed_at' => $now,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}