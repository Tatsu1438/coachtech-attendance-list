@extends('layouts.user-menu-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/stamping.css') }}">
@endsection

@section('content')
    <div class="status-box">
        <div class="stamp-box">
            <p class="status">{{$status}}</p>
        </div>
        <div class="date-box">
            <p class="date"> {{ $date }} </p>
        </div>
        <div class="time-box">
            <p class="time">{{ $time }}</p>
        </div>
        <div class="user-status">
            @if($status === '勤務外')
                <form action="{{ route('attendance.clock_in') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-start">出勤</button>
                </form>

            @elseif($status === '出勤中')

            <div class="button-group">
                <form action="{{ route('attendance.clock_out') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-end">退勤</button>
                </form>

                <form action="{{ route('attendance.break_start') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-break">休憩入</button>
                </form>
            </div>

            @elseif($status === '休憩中')
                <form action="{{ route('attendance.break_end') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-break-end">休憩戻</button>
            </form>

            @elseif($status === '退勤済')
                <p>お疲れさまでした。</p>
            @endif
        </div>
    </div>

@endsection