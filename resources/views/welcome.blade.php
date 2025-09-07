<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /* Basic styles */
                body {
                    font-family: 'Instrument Sans', sans-serif;
                    background-color: #FDFDFC;
                    color: #1b1b18;
                    padding: 1.5rem;
                    min-height: 100vh;
                }
                .container {
                    max-width: 1024px;
                    width: 100%;
                    margin: 0 auto;
                }
                a {
                    color: #FF4433;
                    text-decoration: none;
                }
            </style>
        @endif
    </head>
    <body>
        <header>
            @if (Route::has('login'))
                <nav>
                    @auth
                        <a href="{{ url('/dashboard') }}">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        <div class="container">
            <main>
                <div>
                    <h1>Let's get started</h1>
                    <p>Laravel has an incredibly rich ecosystem. We suggest starting with the following.</p>
                    <ul>
                        <li>
                            Read the <a href="https://laravel.com/docs" target="_blank">documentation</a>
                        </li>
                        <li>
                            Watch video tutorials at <a href="https://laracasts.com" target="_blank">Laracasts</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <!-- Simplified Laravel Logo -->
                    <div>
                        <div>
                            <svg viewBox="0 0 50 52" xmlns="http://www.w3.org/2000/svg" width="100" height="100">
                            <!-- Simplified Laravel logo shape -->
                            <path d="M25 0 L50 15 L50 40 L25 52 L0 40 L0 15 Z" fill="#FF2D20"/>
                            </svg>
                        </div>
                        <h2>Laravel</h2>
                        <p>The PHP Framework for Web Artisans</p>
                    </div>
                </div>
            </main>
        </div>

    </body>
</html>
