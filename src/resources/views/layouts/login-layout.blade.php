<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/login-layout.css') }}">
    @yield('css')
</head>
<body>
    <header>
        <div class="img-box">
            <img src="{{ asset('storage/images/CoachTech.png') }}" alt="">
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>