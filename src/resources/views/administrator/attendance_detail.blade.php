@extends('layouts.admin-menu-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/administrator/detail.css') }}">
@endsection

@section('content')
    <div class="detail-list">
        <div class="title-box">
            <div class="line-1"></div>
            <h2 class="detail">勤怠詳細</h2>
        </div>
        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="post">
        @csrf
        @method('PUT')

            <table>
                <tr>
                    <th>名前</th>
                    <td>
                        <div class="user-name">{{ $user->user_name }}</div>
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        <div class="day-box">
                            <span class="year">{{ $attendance->work_year }}</span>
                            <span class="month-day">{{ $attendance->work_month_day }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="working-time">
                            @if(isset($attendanceRequest) && $attendanceRequest->request_status === '承認待ち')
                                <p style="margin-left: 20px;">{{ $attendanceRequest->clock_in?->format('H:i') }}</p>〜
                                <p style="margin-right: 20px;">{{ $attendanceRequest->clock_out?->format('H:i') }}</p>
                            @else
                                <input type="text" name="clock_in" value="{{ $attendance->clock_in_time }}"> 〜 
                                <input type="text" name="clock_out" value="{{ $attendance->clock_out_time }}">
                            @endif
                        </div>
                        <div class="error">
                            @error('clock_in')
                                <div class="error-text-top">{{ $message }}</div>
                            @enderror
                            @error('clock_out')
                                <div class="error-text-bottom">{{ $message }}</div>
                            @enderror
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        <div class="break-time">
                            @if(isset($attendanceRequest) && $attendanceRequest->request_status === '承認待ち')
                                <p style="margin-left: 20px;">{{ $attendanceRequest->break_start?->format('H:i') }}</p>〜
                                <p style="margin-right: 20px;">{{ $attendanceRequest->break_end?->format('H:i') }}</p>
                            @else
                                <input type="text" name="break_start" value="{{ $attendance->formatted_break_start }}"> 〜
                                <input type="text" name="break_end" value="{{ $attendance->formatted_break_end }}">
                            @endif
                        </div>
                        <div class="error">
                            @error('break_start')
                                <div class="error-text-top">{{ $message }}</div>
                            @enderror
                            @error('break_end')
                                <div class="error-text-bottom">{{ $message }}</div>
                            @enderror
                        </div>
                    </td>
                </tr>
                </tr>
                @foreach($breaks as $index => $break)
                    @php
                        // AttendanceRequestBreak のデータを取得（承認待ちの申請データ）
                        $requestBreak = $attendanceRequest?->breaks?->firstWhere('break_number', $index + 2);
                    @endphp

                    <tr>
                        <th>休憩{{ $index + 2 }}</th>
                        <td>
                            <div class="break-time">
                                @if($attendanceRequest && $attendanceRequest->request_status === '承認待ち' && $requestBreak)
                                    <p style="margin-left: 20px;">{{ $requestBreak->break_start?->format('H:i') }}</p>〜
                                    <p style="margin-right: 20px;">{{ $requestBreak->break_end?->format('H:i') }}</p>
                                @else
                                    {{-- AttendanceBreak の値をinputに表示 --}}
                                    <input type="text" name="break_start_{{ $index + 2 }}" 
                                        value="{{ $break?->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}">
                                    〜
                                    <input type="text" name="break_end_{{ $index + 2 }}" 
                                        value="{{ $break?->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}">
                                @endif
                            </div>
                            <div class="error">
                                @error('break_start')
                                    <div class="error-text-top">{{ $message }}</div>
                                @enderror
                                @error('break_end')
                                    <div class="error-text-bottom">{{ $message }}</div>
                                @enderror
                            </div>
                        </td>
                    </tr>
                @endforeach
                <tr>
                <tr>
                    <th>備考</th>
                    <td>
                        @if(isset($attendanceRequest) && $attendanceRequest->request_status === '承認待ち')
                            <p class="pending-text" style="margin-left: 20px;">{{ $attendanceRequest->request_reason }}</p>
                        @else
                            <textarea name="request_reason" rows="3"></textarea>
                        @endif
                        <div class="error">
                            @error('request_reason')
                                <div class="error-text-top">{{ $message }}</div>
                            @enderror
                        </div>
                    </td>
                </tr>
            </table>
            <div class="submit-btn-box">
                @if(isset($attendanceRequest) && $attendanceRequest->request_status === '承認待ち')
                    <p class="alert-message" style="color: red; font-weight: bold; margin: 10px 0;">
                        *承認待ちのため修正はできません。
                    </p>
                @else
                    <button type="submit">修正</button>
                @endif
            </div>
        </form>
    </div>
@endsection