@extends('layouts.login-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <h2>会員登録</h2>
    <div class="input-box">
        <form action="{{ route('register') }}" method="post">
            @csrf
            <div class="user-name-input-box">
                <label for="user_name">名前</label>
                <input type="text" id="user_name" name="user_name">
                @error('user_name')
                    <p class="error-message" style="color: red;">{{ $message }}</p>
                @enderror
            </div>
            <div class="mail-input-box">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email">
                @error('email')
                    <p class="error-message" style="color: red;">{{ $message }}</p>
                @enderror
            </div>
            <div class="password-input-box">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password">
                @error('password')
                    <p class="error-message" style="color: red;">{{ $message }}</p>
                @enderror
            </div>
            <div class="password-confirm-input-box">
                <label for="password-confirm">パスワード</label>
                <input type="password" id="password-confirm" name="password_confirmation">
            </div>
            <button>登録する</button>
            <a href="/login" class="login-btn">ログインする</a>
        </form>
    </div>
@endsection