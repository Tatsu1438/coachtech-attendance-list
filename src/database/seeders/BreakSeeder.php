<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceRequest;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

class BreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            $date = Carbon::today();
            
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start' => '17:00:00',
                'break_end' => '17:45:00',
                'break_number' => 2,
            ]);
        }

        foreach ($attendances as $attendance) {
            $totalSeconds = 0;

            if ($attendance->break_start && $attendance->break_end) {
                $mainStart = Carbon::parse($attendance->break_start);
                $mainEnd = Carbon::parse($attendance->break_end);
                $totalSeconds += $mainEnd->diffInSeconds($mainStart);
            }

            $breaks = $attendance->breaks()
                ->whereNotNull('break_start')
                ->whereNotNull('break_end')
                ->get();

            foreach ($breaks as $b) {
                $start = Carbon::parse($b->break_start);
                $end = Carbon::parse($b->break_end);
                $totalSeconds += $end->diffInSeconds($start);
            }

            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;

            $attendance->break_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            $attendance->save();
        }
    }
}
