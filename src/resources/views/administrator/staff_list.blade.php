@extends('layouts.admin-menu-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/administrator/staff-list.css') }}">
@endsection

@section('content')
    <div class="staff-list">
        <div class="title-box">
            <div class="line-1"></div>
            <h2 class="staff">スタッフ一覧</h2>
        </div>
        <div class="table-box">
            <table>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->user_name  }}</td>
                        <td>{{ $user->email }}</td>
                        <td><a href="{{ route('admin.staff.detail', $user->id) }}">詳細</a></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection