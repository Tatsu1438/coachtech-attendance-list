@extends('layouts.admin-menu-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/administrator/approve.css') }}">
@endsection

@section('content')
    <div class="detail-list">
        <div class="title-box">
            <div class="line-1"></div>
            <h2 class="detail">勤怠詳細</h2>
        </div>
        <form action="{{ route('admin.request.permitted', $approve_request->id) }}" method="post">
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
                            <span class="year">{{ optional($attendance)->work_year }}</span>
                            <span class="month-day">{{ optional($attendance)->work_month_day }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="working-time">
                            <span class="year">{{ optional($approve_request->clock_in)->format('H:i') }}</span>〜
                            <span class="month-day">{{ optional($approve_request->clock_out)->format('H:i') }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        <div class="break-time">
                            <span class="year"> {{ optional($approve_request->break_start)->format('H:i') }}</span>〜
                            <span class="month-day">{{ optional($approve_request->break_end)->format('H:i') }}</span>
                        </div>
                    </td>
                </tr>
                @foreach($approve_request->breaks as $break)
                    <tr>
                        <th>休憩{{ $break->break_number }}</th>
                        <td>
                            <div class="break-time">
                                <p style="margin-left: 20px;">{{ $break->break_start?->format('H:i') }}</p>〜
                                <p style="margin-right: 20px;">{{ $break->break_end?->format('H:i') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <th>備考</th>
                    <td>
                        <p style="margin-left: 20px;">{{ $approve_request->request_reason }}</p>
                    </td>
                </tr>
            </table>
            <div class="submit-btn-box">
                @if ($approve_request->request_status !== '承認済み')
                    <button type="submit">承認</button>
                @else
                    <span class="approved-label">承認済み</span>
                @endif
            </div>
        </form>
    </div>
@endsection