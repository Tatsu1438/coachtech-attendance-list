<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class DaySelectController extends Controller
{

    public function daySelect(Request $request)
    {
        $selectDate = $request->input('date', Carbon::today()->toDateString());
        $currentDate = Carbon::parse($selectDate);

        $yesterDay = $currentDate->copy()->subDay()->toDateString();
        $nextDay = $currentDate->copy()->addDay()->toDateString();


        $users = User::all();

        $attendanceData = $users->map(function ($user) use ($currentDate) {
            $attendances = $user->attendances()
                ->whereDate('work_date', $currentDate)
                ->get();
            return [
                'user' => $user,
                'attendances' => $attendances,
            ];
        });

        return view('administrator.attendance_list', compact('currentDate', 'yesterDay', 'nextDay', 'attendanceData'));
    }

    public function monthSelect(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $dateInput = $request->input('date');

        $currentMonth = $dateInput ? Carbon::parse($dateInput . '-01') : Carbon::now();
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth     = $currentMonth->copy()->addMonth()->format('Y-m');

        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate   = $currentMonth->copy()->endOfMonth();
        $weekdays  = ['日','月','火','水','木','金','土'];

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });

        $totalAttendances = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayStr = $date->format('Y-m-d');
            $attendance = $attendances[$dayStr] ?? null;

            $totalAttendances[] = (object)[
                'id' => $attendance?->id,
                'formatted_date' => $attendance ? $attendance->formatted_date : $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')',
                'clock_in_time' => $attendance?->clock_in_time ?? '',
                'clock_out_time' => $attendance?->clock_out_time ?? '',
                'formatted_break_time' => $attendance?->formatted_break_time ?? '',
                'formatted_total_time' => $attendance?->formatted_total_time ?? '',
            ];
        }

        if (auth('admin')->check()) {
            return view('administrator.staff_detail', compact('user', 'currentMonth', 'previousMonth', 'nextMonth', 'totalAttendances'));
        } else {
            return view('user.work_list', compact('user', 'currentMonth', 'previousMonth', 'nextMonth', 'totalAttendances'));
        }
    }
}
