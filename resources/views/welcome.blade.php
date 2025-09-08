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
                                <path d="M49.626 11.564a.809.809 0 0 1 .028.209v10.972a.8.8 0 0 1-.402.694l-9.209 5.302V39.25c0 .286-.152.55-.4.694L20.42 51.01c-.044.025-.092.041-.14.058-.018.006-.035.017-.054.022a.805.805 0 0 1-.41 0c-.022-.006-.042-.018-.063-.026-.044-.016-.09-.03-.132-.054L.402 39.944A.801.801 0 0 1 0 39.25V6.334c0-.072.01-.142.028-.21.006-.023.02-.044.028-.067.015-.042.029-.085.051-.124.015-.026.037-.047.055-.071.023-.032.044-.065.071-.093.023-.023.053-.04.079-.06.029-.024.055-.05.088-.069h.001l9.61-5.533a.802.802 0 0 1 .8 0l9.61 5.533h.002c.032.02.059.045.088.068.026.02.055.038.078.06.028.029.048.062.072.094.017.024.04.045.054.071.023.04.036.082.052.124.008.023.022.044.028.068a.809.809 0 0 1 .028.209v20.559l8.008-4.611v-10.51c0-.07.01-.141.028-.208.007-.024.02-.045.028-.068.016-.042.03-.085.052-.124.015-.026.037-.047.054-.071.024-.032.044-.065.072-.093.023-.023.052-.04.078-.06.03-.024.056-.05.088-.069h.001l9.611-5.533a.801.801 0 0 1 .8 0l9.61 5.533c.034.02.06.045.09.068.025.02.054.038.077.06.028.029.048.062.072.094.018.024.04.045.054.071.023.039.036.082.052.124.009.023.022.044.028.068z" fill="#FF2D20" fill-rule="evenodd"/>
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
