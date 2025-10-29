<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            $date = Carbon::today();
            
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date->toDateString(),
                'clock_in' => $date->copy()->setTime(9, 0)->toTimeString(),
                'clock_out' => $date->copy()->setTime(18, 0)->toTimeString(),
                'break_time' => '01:00:00',
                'total_time' => '08:00:00',
            ]);
        }
    }
}