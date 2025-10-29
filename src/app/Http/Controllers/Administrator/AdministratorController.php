<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class AdministratorController extends Controller
{
    public function attendanceList()
    {
        $currentDate = \Carbon\Carbon::today();
        $users = User::all();

        $attendanceData = [];
        foreach ($users as $user) {
            $attendanceData[] = [
                'user' => $user,
                'attendances' => Attendance::where('user_id', $user->id)
                                        ->whereDate('work_date', $currentDate)
                                        ->get(),
            ];
        }

        return view('administrator.attendance_list', [
            'attendanceData' => $attendanceData,
            'currentDate' => $currentDate,
        ]);
    }

    public function listDetail($id)
    {
        $attendance = Attendance::findOrFail($id);
        $user = $attendance->user;

        $attendanceRequest = AttendanceRequest::with('breaks')
        ->where('attendance_id', $attendance->id)
        ->orderByDesc('created_at')
        ->first();

        $breaks = $attendance->breaks()->orderBy('id')->get();

        return view('administrator.attendance_detail', compact('user', 'attendance', 'attendanceRequest', 'breaks'));
    }

    public function exportCsv($id)
    {
        $user = User::findOrFail($id);
        $attendances = $user->attendances()->get();

        $fileName = $user->user_name . '_勤怠_' . Carbon::now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->formatted_date,
                    $attendance->clock_in_time,
                    $attendance->clock_out_time,
                    $attendance->formatted_break_time,
                    $attendance->formatted_total_time,
                ]);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }


    public function staffList()
    {
        $users = user::all();
        return view('administrator.staff_list', compact('users') );
    }

    public function staffDetail(Request $request, $userId, $yearMonth = null)
    {
        $dateInput = $request->input('date');

        if (empty($dateInput) || $dateInput === '1970-01') {
            $currentMonth = Carbon::now()->startOfMonth();
        } else {
            $currentMonth = Carbon::parse($dateInput . '-01');
        }

        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth     = $currentMonth->copy()->addMonth()->format('Y-m');

        $yearMonth = $currentMonth->format('Y-m');

        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate   = $currentMonth->copy()->endOfMonth();
        $weekdays  = ['日', '月', '火', '水', '木', '金', '土'];


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

        return view('administrator.staff_detail', compact('user', 'totalAttendances','currentMonth','previousMonth','nextMonth'));

    }

    public function requestList()
    {
        $users = user::all();

        $requests = AttendanceRequest::with(['user', 'attendance'])
        ->whereIn('request_status', ['承認待ち', '承認済み'])
        ->orderBy('created_at', 'desc')
        ->get();


        return view('administrator.user_request', compact('users','requests'));
    }

    public function requestApprove($id)
    {
        $approve_request = AttendanceRequest::with('user')->findOrFail($id);
        $user = $approve_request->user;
        $attendance = $approve_request->attendance;
        $breaks = $attendance?->breaks ?? collect(); 

        return view('administrator.approve', compact('approve_request', 'user','attendance', 'breaks'));
    }

    public function requestPermitted($id)
    {
        $requestData = AttendanceRequest::findOrFail($id);
        $attendance = Attendance::findOrFail($requestData->attendance_id);

        $attendance->update([
            'clock_in' => $requestData->clock_in,
            'clock_out' => $requestData->clock_out,
            'break_start' => $requestData->break_start,
            'break_end' => $requestData->break_end,
            'second_break_start' => $requestData->second_break_start,
            'second_break_end' => $requestData->second_break_end,
            'request_reason' => $requestData->request_reason,
            'request_status' => '承認済み',
        ]);

        $requestData->update([
            'request_status' => '承認済み'
        ]);

        $requestData->update([
        'request_status' => '承認済み'
    ]);


        foreach ($requestData->breaks as $requestBreak) {
            $attendanceBreak = $attendance->breaks()->firstOrNew([
                'attendance_id' => $attendance->id,
                'break_number' => $requestBreak->break_number
            ]);
            $attendanceBreak->break_start = $requestBreak->break_start;
            $attendanceBreak->break_end = $requestBreak->break_end;
            $attendanceBreak->save();
        }

    $totalSeconds = 0;

        if ($attendance->break_start && $attendance->break_end) {
            $start = Carbon::parse($attendance->break_start);
            $end = Carbon::parse($attendance->break_end);
            $totalSeconds += $end->diffInSeconds($start);
        }

        $breaks = $attendance->breaks()->whereNotNull('break_start')->whereNotNull('break_end')->get();
        foreach ($breaks as $b) {
            $start = Carbon::parse($b->break_start);
            $end = Carbon::parse($b->break_end);
            $totalSeconds += $end->diffInSeconds($start);
        }

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $attendance->break_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);


        if ($attendance->clock_in && $attendance->clock_out) {
            $start = Carbon::parse($attendance->clock_in);
            $end = Carbon::parse($attendance->clock_out);
            $diffInSeconds = $end->diffInSeconds($start) - $totalSeconds;

            $hours = floor($diffInSeconds / 3600);
            $minutes = floor(($diffInSeconds % 3600) / 60);
            $seconds = $diffInSeconds % 60;

            $attendance->total_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        $attendance->save();

        return redirect()->route('admin.request.approve', ['id' => $requestData->id])->with('success', '申請を承認しました');
    }

}
