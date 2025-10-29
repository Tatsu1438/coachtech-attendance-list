@extends('layouts.user-menu-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/work-list.css') }}">
@endsection

@section('content')
    <div class="work-list-menu">
        <div class="work-list-box">
            <div class="line-1"></div>
            <h2 class="date">勤怠一覧</h2>
        </div>
        <div class="date-select">
            <form action="{{ route('common.month_select', ['id' => $user->id]) }}" method="GET" class="date-select-form">
                <input type="hidden" name="date" value="{{ $previousMonth ?? '' }}">
                <button type="submit">
                    <img src="{{ asset('storage/images/sign.png') }}" alt="前日" class="btn-icon">
                    前月
                </button>
            </form>

            <form id="monthForm" action="{{ route('common.month_select', ['id' => $user->id]) }}" method="GET" class="date-select-form">
                <div class="date-and-calendar">
                    <input id="hiddenDate" type="month" name="date" value="{{ $currentMonth->format('Y-m') ?? '' }}" style="display:none;" onchange="updateDisplayDate(this.value)">

                    <img src="{{ asset('storage/images/calendar.png') }}" alt="カレンダー" class="calendar-icon cursor-pointer w-6 h-6" onclick="document.getElementById('hiddenDate').showPicker()">

                    <p id="displayDate" class="select-date text-gray-800">
                        {{ $currentMonth->format('Y/m') ?? '' }}
                    </p>
                </div>
            </form>

            <script>
            function updateDisplayDate(value) {
                const [year, month] = value.split('-');
                document.getElementById('displayDate').textContent = `${year}/${month}`;
                document.getElementById('hiddenDate').value = value;
                document.getElementById('monthForm').submit();
            }
            </script>

            <form action="{{ route('common.month_select', ['id' => $user->id]) }}" method="GET" class="date-select-form" style=" justify-content: flex-end;">
                <input type="hidden" name="date" value="{{ $nextMonth ?? '' }}">
                <button type="submit">
                    翌月
                    <img src="{{ asset('storage/images/sign.png') }}" alt="翌日" class="btn-icon-next">
                </button>
            </form>
        </div>
        <div class="table-box">
            <table>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
                @foreach($totalAttendances as $attendance)
                    <tr>
                        <td>{{ $attendance->formatted_date }}</td>
                        <td>{{ $attendance->clock_in_time }}</td>
                        <td>{{ $attendance->clock_out_time }}</td>
                        <td>{{ $attendance->formatted_break_time }}</td>
                        <td>{{ $attendance->formatted_total_time }}</td>
                        <td>
                            @if($attendance->id)
                                <a href="{{ route('user.work.list.detail', $attendance->id) }}">詳細</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection