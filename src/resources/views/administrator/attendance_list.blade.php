@extends('layouts.admin-menu-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/administrator/attendance-list.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="date-box">
            <div class="line-1"></div>
            <h class="date">{{ $currentDate->format('Y年n月j日') }}の勤怠</h>
        </div>
        <div class="date-select">

            <form action="{{ route('attendance.day_select') }}" method="GET" class="date-select-form">
                <input type="hidden" name="date" value="{{ $yesterDay ?? '' }}">
                <button type="submit">
                    <img src="{{ asset('storage/images/sign.png') }}" alt="前日" class="btn-icon">
                    前日
                </button>
            </form>

            <form id="dayForm" action="{{ route('attendance.day_select') }}" method="GET" class="date-select-form flex items-center gap-2">
                <div class="date-and-calendar">
                        <input id="hiddenDate" type="date" name="date" value="{{ $currentDate ? $currentDate->toDateString() : '' }}"
                        style="display: none;"
                        onchange="updateDisplayDate(this.value)"
                    >

                    <img
                        src="{{ asset('storage/images/calendar.png') }}"
                        alt="カレンダー"
                        class="calendar-icon cursor-pointer w-6 h-6"
                        onclick="document.getElementById('hiddenDate').showPicker()"
                    >

                    <p id="displayDate" class="select-date text-gray-800">
                        {{ $currentDate ? $currentDate->format('Y/m/d') : '日付を選択' }}
                    </p>
                </div>
            </form>

            <script>
            function updateDisplayDate(value) {
                if (!value) return;

                const date = new Date(value);
                const formatted = date.toLocaleDateString('ja-JP');
                document.getElementById('displayDate').textContent = formatted;

                document.getElementById('dayForm').submit();
            }
            </script>

            <form action="{{ route('attendance.day_select') }}" method="GET" class="date-select-form" style=" justify-content: flex-end;">
                <input type="hidden" name="date" value="{{ $nextDay ?? '' }}">
                <button type="submit">
                    翌日
                    <img src="{{ asset('storage/images/sign.png') }}" alt="翌日" class="btn-icon-next">
                </button>
            </form>
        </div>
        <div class="table-box">
            <table>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
                @foreach($attendanceData as $data)
                    @foreach($data['attendances'] as $attendance)
                        <tr>
                            <td>{{ $data['user']->user_name }}</td>
                            <td>{{ $attendance->clock_in_time }}</td>
                            <td>{{ $attendance->clock_out_time}}</td>
                            <td>{{ $attendance->formatted_break_time }}</td>
                            <td>{{ $attendance->formatted_total_time }}</td>
                            <td><a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a></td>
                        </tr>
                @endforeach
                    @endforeach
            </table>
        </div>
    </div>
@endsection