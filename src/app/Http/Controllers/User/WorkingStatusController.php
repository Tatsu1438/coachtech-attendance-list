<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\AttendanceRequest;
use App\Http\Requests\User\AttendanceModifyRequest;
use App\Models\AttendanceRequestBreak;

class WorkingStatusController extends Controller
{
    public function clockIn()
    {
        $user = Auth::user();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $now->toDateString())
            ->latest('id')
            ->first();

        if (!$attendance || $attendance->clock_out) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $now->toDateString(),
                'clock_in' => $now->toTimeString(),
            ]);
        }

        $user->status = '出勤中';
        $user->save();

        return redirect()->route('user.start.work');
    }

    public function breakStart()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $now->toDateString()],
            ['clock_in' => $now->toTimeString()]
        );

        if (is_null($attendance->break_start)) {
            $attendance->break_start = $now->toTimeString();
            $attendance->save();
        } else {
            $lastBreakNumber = $attendance->breaks()->max('break_number');

            if ($lastBreakNumber) {
                $nextBreakNumber = $lastBreakNumber + 1;
            } else {
                $nextBreakNumber = 2;
            }

            $attendance->breaks()->create([
                'break_start' => $now->toTimeString(),
                'break_number' => $nextBreakNumber,
            ]);
        }

        $user->status = '休憩中';
        $user->save();
        return redirect()->route('user.start.work');
    }

    public function breakEnd()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $now->toDateString())
            ->first();

        if ($attendance && $attendance->break_start && !$attendance->break_end) {
            $attendance->break_end = $now->format('H:i:s');
            $attendance->save();
        } else {
            $latestBreak = $attendance->breaks()
                ->whereNull('break_end')
                ->latest('id')
                ->first();

            if ($latestBreak) {
                $latestBreak->update(['break_end' => $now->toTimeString()]);
            }
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
        $attendance->save();

        $user->status = '出勤中';
        $user->save();

        return redirect()->route('user.start.work');
    }

    public function clockOut()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $attendance = Attendance::where('user_id', $user->id)
        ->where('work_date', $now->toDateString())
        ->first();

        if ($attendance) {

            if ($attendance->clock_in) {
                $attendance->clock_out = $now->format('H:i:s');
                $start = Carbon::parse($attendance->clock_in);
                $end = Carbon::parse($attendance->clock_out);
                $diffInSeconds = $end->diffInSeconds($start);

                $hours = floor($diffInSeconds / 3600);
                $minutes = floor(($diffInSeconds % 3600) / 60);
                $seconds = $diffInSeconds % 60;

                $attendance->total_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }


            $user->status = '退勤済';
            $user->save();

            $attendance->save();
        }

        return redirect()->route('user.start.work');
    }

    public function attendanceUpdate(AttendanceModifyRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $userId = auth()->id();

        $existingRequest = AttendanceRequest::where('attendance_id', $id)
        ->where('user_id', $userId)
        ->where('request_status', '承認待ち')
        ->first();

        $validated = $request->validated();

        $data = [
            'attendance_id' => $attendance->id,
            'user_id'       => $userId,
            'clock_in'      => $request->clock_in,
            'clock_out'     => $request->clock_out,
            'break_start'   => $request->break_start,
            'break_end'     => $request->break_end,
            'second_break_start' => $request->second_break_start,
            'second_break_end'   => $request->second_break_end,
            'request_reason'     => $request->request_reason,
            'request_status'     => '承認待ち',
        ];

        if ($existingRequest) {
            $existingRequest->update($data);
            $requestModel = $existingRequest;
        } else {
            $requestModel = AttendanceRequest::create($data);
        }

        for ($i = 2; $i <= 4; $i++) {
            $start = $request->input("break_start_{$i}");
            $end   = $request->input("break_end_{$i}");

            if ($start && $end) {
                AttendanceRequestBreak::updateOrCreate(
                    [
                        'attendance_request_id' => $requestModel->id,
                        'break_number' => $i,
                    ],
                    [
                        'break_start' => $start,
                        'break_end'   => $end,
                    ]
                );
            } else {
                // 入力がなければ削除
                AttendanceRequestBreak::where('attendance_request_id', $requestModel->id)
                    ->where('break_number', $i)
                    ->delete();
            }
        }

        return redirect()->route('user.work.list.detail', ['id' => $attendance->id])
            ->with('success', '修正申請を送信しました（承認待ち）');
    }

    public function adminUpDate(AttendanceModifyRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $userId = $attendance->user_id;

        $existingRequest = AttendanceRequest::where('attendance_id', $id)
            ->where('user_id', $userId)
            ->where('request_status', '承認待ち')
            ->first();

        $validated = $request->validated();

        $data = [
            'attendance_id' => $attendance->id,
            'user_id'       => $userId,
            'clock_in'      => $request->clock_in,
            'clock_out'     => $request->clock_out,
            'break_start'   => $request->break_start,
            'break_end'     => $request->break_end,
            'second_break_start' => $request->second_break_start,
            'second_break_end'   => $request->second_break_end,
            'request_reason'     => $request->request_reason,
            'request_status'     => '承認待ち',
        ];

        if ($existingRequest) {
            $existingRequest->update($data);
            $requestModel = $existingRequest;
        } else {
            $requestModel = AttendanceRequest::create($data);
        }

        for ($i = 2; $i <= 4; $i++) {
            $start = $request->input("break_start_{$i}");
            $end   = $request->input("break_end_{$i}");

            if ($start && $end) {
                AttendanceRequestBreak::updateOrCreate(
                    [
                        'attendance_request_id' => $requestModel->id,
                        'break_number' => $i,
                    ],
                    [
                        'break_start' => $start,
                        'break_end'   => $end,
                    ]
                );
            } else {
                AttendanceRequestBreak::where('attendance_request_id', $requestModel->id)
                    ->where('break_number', $i)
                    ->delete();
            }
        }


        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id])
            ->with('success', '修正申請を送信しました（承認待ち）');
    }
}
