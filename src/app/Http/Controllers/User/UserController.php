<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\AttendanceRequest;

class UserController extends Controller
{
    public function index()
    {
        Carbon::setLocale('ja');
        $user = auth('web')->user();
        $now = Carbon::now();
        $today = $now->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->latest('id')
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            $status = '勤務外';
        } elseif ($attendance->clock_in && !$attendance->clock_out) {
            if ($attendance->break_start && !$attendance->break_end) {
                $status = '休憩中';
            } else {
                $ongoingBreak = $attendance->breaks()->whereNull('break_end')->first();
                if ($ongoingBreak) {
                    $status = '休憩中';
                } else {
                    $status = '出勤中';
                }
            }
        } elseif ($attendance->clock_out) {
            $status = '退勤済';
        } else {
            $status = '勤務外';
        }

        $user->update(['status' => $status]);

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $date = $now->year . '年' . $now->month . '月' . $now->day . '日(' . $weekdays[$now->dayOfWeek] . ')';
        $time = $now->format('H:i');

        return view('user.stamping', compact('status', 'date', 'time', 'attendance'));
    }

    public function workList()
    {


        $yearMonth = $yearMonth ?? Carbon::now()->format('Y-m');
        $currentMonth = Carbon::parse($yearMonth . '-01');
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $startDate = Carbon::parse($yearMonth . '-01');
        $endDate = $startDate->copy()->endOfMonth();
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        $userId = auth()->id();
        $user = User::findOrFail($userId);

        $attendances = Attendance::where('user_id', $userId)
        ->whereBetween('work_date', [$startDate, $endDate])
        ->get()
        ->keyBy(function($item) {
            return $item->work_date->format('Y-m-d');
        });

        $totalAttendances = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayStr = $date->format('Y-m-d');
            $attendance = $attendances[$dayStr] ?? null;

            $totalAttendances[] = (object)[
                'id' => $attendance?->id,
                'formatted_date' => $attendance ? $attendance->formatted_date : $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')',
                'clock_in_time' => $attendance->clock_in_time ?? '',
                'clock_out_time' => $attendance->clock_out_time ?? '',
                'formatted_break_time' => $attendance->formatted_break_time ?? '',
                'formatted_total_time' => $attendance->formatted_total_time ?? '',
            ];
        }

        return view('user.work_list', compact('user', 'totalAttendances','currentMonth','previousMonth','nextMonth'));

    }

    public function userListDetail($id)
    {
        $attendance = Attendance::findOrFail($id);
        $user = $attendance->user;

        $attendanceRequest = AttendanceRequest::with('breaks')
        ->where('attendance_id', $id)
        ->where('user_id', $user->id)
        ->latest()
        ->first();

        $breaks = $attendance->breaks()->orderBy('id')->get();

        return view('user.work_list_detail', compact('user','attendance', 'attendanceRequest', 'breaks'));
    }


    public function userRequest()
    {
        $userId = auth('web')->id();

        $requests = AttendanceRequest::with(['user', 'attendance'])
            ->where('user_id', $userId)
            ->whereIn('request_status', ['承認待ち', '承認済み'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.work_request', compact('requests'));
    }
}

