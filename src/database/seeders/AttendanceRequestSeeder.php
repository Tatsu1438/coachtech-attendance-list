<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceRequest;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

class AttendanceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $sampleTaro = User::where('email', 'sample@example.com')->first();
        $tanakaTaro = User::where('email', 'tanaka@example.com')->first();

        if (!$sampleTaro || !$tanakaTaro) {
            $this->command->warn('対象ユーザーが見つかりませんでした。UserSeederを先に実行してください。');
            return;
        }

        $sampleAttendance = Attendance::where('user_id', $sampleTaro->id)->first();
        $tanakaAttendance = Attendance::where('user_id', $tanakaTaro->id)->first();

        if (!$sampleAttendance || !$tanakaAttendance) {
            $this->command->warn('出勤データが見つかりません。AttendanceSeederを先に実行してください。');
            return;
        }


        $requests = [
            [
                'user_id' => $sampleTaro->id,
                'attendance_id' => $sampleAttendance->id,
                'request_status' => '承認待ち',
                'request_reason' => '出勤時間を間違えて入力しました。',
                'clock_in' => "10:00:00",
                'clock_out' => "22:00:00",
                'break_start' => '11:30:00',
                'break_end' => '12:30:00',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => $tanakaTaro->id,
                'attendance_id' => $tanakaAttendance->id,
                'request_status' => '承認待ち',
                'request_reason' => '退勤打刻漏れのため申請。',
                'clock_in' => "13:00:00",
                'clock_out' => "23:00:00",
                'break_start' => '15:30:00',
                'break_end' => '16:30:00',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        AttendanceRequest::insert($requests);
    }
}
