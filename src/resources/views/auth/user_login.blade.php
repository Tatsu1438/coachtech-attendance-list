@extends('layouts.login-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
    <h2>ログイン</h2>
    <div class="input-box">
        <form action="{{ route('login') }}" method="post">
            @csrf
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
            <button>ログインする</button>
            <a class="register-btn" href="/register">会員登録はこちら</a>
        </form>
    </div>
@endsection