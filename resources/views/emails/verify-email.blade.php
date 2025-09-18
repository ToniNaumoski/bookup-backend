<!DOCTYPE html>
<html lang="mk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Верифицирајте ја вашата емаил адреса</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">RezervirajOnline.мк</div>
            <h1 class="title">Верифицирајте ја вашата емаил адреса</h1>
        </div>

        <div class="content">
            <p>Здраво {{ $user->name }},</p>

            <p>Ви благодариме што се регистриравте на RezervirajOnline.мк! За да го комплетирате процесот на регистрација и да можете да се најавите во вашиот профил, ве молиме верифицирајте ја вашата емаил адреса.</p>

            <p>Кликнете на копчето подолу за да ја верифицирате вашата емаил адреса:</p>

            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Верифицирај емаил адреса</a>
            </div>

            <div class="warning">
                <strong>Важно:</strong> Овој линк ќе биде валиден само 60 минути. Ако линкот истече, ќе треба да побарате нов линк за верификација.
            </div>

            <p>Ако имате проблеми со кликнувањето на копчето, можете да го копирате и залепите следниот линк во вашиот веб прелистувач:</p>

            <p style="word-break: break-all; background-color: #f3f4f6; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px;">
                {{ $verificationUrl }}
            </p>

            <p>Ако не сте се регистрирале на RezervirajOnline.мк, игнорирајте го овој емаил.</p>

            <p>Со почит,<br>
            Тимот на RezervirajOnline.мк</p>
        </div>

        <div class="footer">
            <p>Ова е автоматски генерирана порака. Ве молиме не одговарајте на овој емаил.</p>
            <p>&copy; 2025 RezervirajOnline.мк. Сите права задржани.</p>
        </div>
    </div>
</body>
</html>