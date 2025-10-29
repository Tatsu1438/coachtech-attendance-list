<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['user_name' => '田中太郎', 'email' => 'tanaka@example.com', 'password' => Hash::make('password')],
            ['user_name' => '鈴木次郎', 'email' => 'suzuki@example.com', 'password' => Hash::make('password')],
            ['user_name' => '佐藤花子', 'email' => 'sato@example.com', 'password' => Hash::make('password')],
            ['user_name' => '高橋健', 'email' => 'takahashi@example.com', 'password' => Hash::make('password')],
            ['user_name' => '伊藤美咲', 'email' => 'ito@example.com', 'password' => Hash::make('password')],
            ['user_name' => '渡辺亮', 'email' => 'watanabe@example.com', 'password' => Hash::make('password')],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}