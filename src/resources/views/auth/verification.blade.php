@extends('layouts.login-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/verification.css') }}">
@endsection

@section('content')
    <div class="verification-box">
        <div class="verification-box-sentence">
            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください</p>
        </div>
    </div>
    <div class="verification-btn-box">
        <a class="verification-btn" href="http://localhost:8025">
            認証はこちらから
        </a>
    </div>
    <div class="resend-box">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="resend-box-sentence">認証メールを再送する</button>
        </form>
    </div>
    @if (session('message'))
        <div class="message-box">
            <p>{{ session('message') }}</p>
        </div>
    @endif
@endsection