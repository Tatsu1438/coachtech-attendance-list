<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/user-menu-layout.css') }}">
    @yield('css')
</head>
<body>
    <header>
        <div class="img-box">
            <img src="{{ asset('storage/images/CoachTech.png') }}" alt="">
        </div>
        <nav class="nav-box">
            <ul>
                <li><a href="{{ route('user.start.work') }}">勤怠</a></li>
                <li><a href="{{ route('user.work.list') }}">勤怠一覧</a></li>
                <li><a href="{{ route('user.ask.request') }}">申請</a></li>
                <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a></li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </ul>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>