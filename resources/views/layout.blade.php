<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    
    <style>
        @yield('style')
        /* ====== ШРИФТ ====== */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    padding-top: 72px; /* чтобы контент не прятался под шапкой */
    background: #f6f6f7;
}

/* ====== HEADER ====== */
header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;

    background: #ffffff;
    border-bottom: 1px solid #e5e5e5;

    padding: 14px 24px;

    display: flex;
    justify-content: flex-end;
    align-items: center;

    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);

    z-index: 1000;
}

/* ====== NAVIGATION ====== */
header nav {
    display: flex;
    gap: 12px;
    align-items: center;
}

/* ССЫЛКИ */
header nav a {
    padding: 9px 18px;
    border-radius: 8px;

    font-size: 14px;
    font-weight: 500;

    text-decoration: none;

    color: #333;
    background: #ffffff;

    border: 1px solid #e0e0e0;

    transition: 0.2s ease;
}

/* Ховер */
header nav a:hover {
    background: #f2f2f2;
    border-color: #d0d0d0;
}

/* Акцентная кнопка (Dashboard) */
header nav a[href*="dashboard"] {
    border-color: #c7c7c7;
    background: #fafafa;
}

header nav a[href*="dashboard"]:hover {
    background: #f0f0f0;
}

/* Мобильная адаптация */
@media (max-width: 600px) {
    header {
        padding: 12px 16px;
    }

    header nav a {
        padding: 7px 14px;
        font-size: 13px;
    }
}

    </style>
</head>
<body>
    <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
    <div>
        @yield('main_content')
    </div>
</body>
</html>