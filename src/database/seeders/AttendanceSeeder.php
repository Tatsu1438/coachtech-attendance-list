<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceRequest;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $date = Carbon::today();

        foreach ($users as $user) {

            $status = '修正なし';

            if (in_array($user->email, ['sample@example.com', 'tanaka@example.com'])) {
                $status = '承認待ち';
            }

            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date->toDateString(),
                'clock_in' => $date->copy()->setTime(9, 0)->toTimeString(),
                'clock_out' => $date->copy()->setTime(19, 0)->toTimeString(),
                'break_start' => '09:30:00',
                'break_end' => '09:45:00',
                'break_time' => null,
                'total_time' => '09:00:00',
                'request_status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}