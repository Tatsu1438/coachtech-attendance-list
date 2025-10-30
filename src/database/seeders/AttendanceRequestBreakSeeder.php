<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceRequest;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

class AttendanceRequestBreakSeeder extends Seeder
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
            $this->command->warn('対象ユーザーが見つかりません。UserSeederを先に実行してください。');
            return;
        }

        $sampleRequest = AttendanceRequest::where('user_id', $sampleTaro->id)->first();
        $tanakaRequest = AttendanceRequest::where('user_id', $tanakaTaro->id)->first();

        if (!$sampleRequest || !$tanakaRequest) {
            $this->command->warn('AttendanceRequest が見つかりません。AttendanceRequestSeeder を先に実行してください。');
            return;
        }
        $breaks = [
            [
                'attendance_request_id' => $sampleRequest->id,
                'break_start' => '14:00:00',
                'break_end' => '14:30:00',
                'break_number' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'attendance_request_id' => $tanakaRequest->id,
                'break_start' => '18:00:00',
                'break_end' => '18:15:00',
                'break_number' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        AttendanceRequestBreak::insert($breaks);
    }
}
