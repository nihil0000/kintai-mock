<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- css -->
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">

    @vite(['resources/js/app.js'])

    <title>kintai</title>
</head>

<body class="min-h-screen flex flex-col font-noto-sans bg-[#F0EFF2]">
    <header class="bg-black text-white py-4">
        <div class="max-w-[1200px] mx-auto flex flex-col xl:flex-row gap-4 xl:gap-0 items-center justify-between">

            <!-- logo -->
            @if (Request::routeIs('verification.notice'))
                <a href="" class="self-center xl:self-auto">
                    <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" class="h-8">
                </a>
            @else
                <a href="{{ route('attendance.create') }}" class="self-center xl:self-auto cursor-pointer">
                    <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" class="h-8">
                </a>
            @endif

            <nav class="flex items-center space-x-5">
                @auth
                    @unless (Request::routeIs('login.create') || Request::routeIs('register.create') || Request::routeIs('verification.notice'))
                        <a href="{{ route('attendance.create') }}" class="hover:underline text-sm md:text-base whitespace-nowrap">
                            勤怠
                        </a>

                        <a href="{{ route('attendance.index') }}" class="hover:underline text-sm md:text-base whitespace-nowrap">
                            勤怠一覧
                        </a>

                        <a href="{{ route('stamp_correction_request.index') }}" class="hover:underline text-sm md:text-base whitespace-nowrap">
                            申請
                        </a>

                        <!-- show only for authenticated users -->
                        <a href="{{ route('logout.destroy') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="hover:underline text-sm md:text-base whitespace-nowrap">
                            ログアウト
                        </a>

                        <form action="{{ route('logout.destroy') }}" id="logout-form" method="post" class="hidden">
                            @csrf
                        </form>
                    @endunless
                @endauth
            </nav>
        </div>
    </header>

    @yield('content')

</body>

</html>
