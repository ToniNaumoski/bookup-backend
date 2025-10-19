<!DOCTYPE html>
<html lang="mk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($action === 'approve')
            Вашата апликација за бизнис е одобрена
        @elseif($action === 'reject')
            Вашата апликација за бизнис е одбиена
        @elseif($action === 'request_changes')
            Потребни се промени во вашата апликација
        @else
            Ажурирање за вашата апликација за бизнис
        @endif
    </title>
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
        .status-box {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid;
        }
        .status-approved {
            background-color: #d1fae5;
            border-left-color: #10b981;
            color: #065f46;
        }
        .status-rejected {
            background-color: #fee2e2;
            border-left-color: #ef4444;
            color: #991b1b;
        }
        .status-changes {
            background-color: #fef3c7;
            border-left-color: #f59e0b;
            color: #92400e;
        }
        .business-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
        }
        .business-info h3 {
            margin-top: 0;
            color: #1e293b;
            font-size: 16px;
        }
        .message-box {
            background-color: #f1f5f9;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #64748b;
        }
        .remarks-box {
            background-color: #fef7ff;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #a855f7;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">RezervirajOnline.мк</div>
            <h1 class="title">
                @if($action === 'approve')
                    Вашата апликација за бизнис е одобрена! 🎉
                @elseif($action === 'reject')
                    Вашата апликација за бизнис е одбиена
                @elseif($action === 'request_changes')
                    Потребни се промени во вашата апликација
                @else
                    Ажурирање за вашата апликација за бизнис
                @endif
            </h1>
        </div>

        <div class="content">
            <p>Здраво {{ $business->user->name }},</p>

            <div class="status-box @if($action === 'approve') status-approved @elseif($action === 'reject') status-rejected @else status-changes @endif">
                <strong>
                    @if($action === 'approve')
                        ✅ Вашата апликација е успешно одобрена!
                    @elseif($action === 'reject')
                        ❌ Вашата апликација е одбиена.
                    @elseif($action === 'request_changes')
                        ⚠️ Потребни се дополнителни промени пред одобрување.
                    @else
                        Статусот на вашата апликација е ажуриран.
                    @endif
                </strong>
            </div>

            <div class="business-info">
                <h3>Информации за бизнисот:</h3>
                <p><strong>Име:</strong> {{ $business->name }}</p>
                <p><strong>Категорија:</strong> {{ $business->main_category }} @if($business->sub_category) - {{ $business->sub_category }} @endif</p>
                <p><strong>Локација:</strong> {{ $business->city }}@if($business->municipality), {{ $business->municipality }}@endif</p>
                <p><strong>Адреса:</strong> {{ $business->street }} {{ $business->street_number }}</p>
            </div>

            @if($adminMessage)
                <div class="message-box">
                    <h3>Порака од администраторот:</h3>
                    <p>{{ $adminMessage }}</p>
                    @if($admin)
                        <p style="font-size: 12px; color: #64748b; margin-top: 10px;">
                            <em>Од: {{ $admin->name }}</em>
                        </p>
                    @endif
                </div>
            @endif

            @if($remarks)
                <div class="remarks-box">
                    <h3>Забелешки за подобрување:</h3>
                    <p>{{ $remarks }}</p>
                </div>
            @endif

            @if($action === 'request_changes')
                <p>Ве молиме да ги направите потребните промени и повторно да ја поднесете вашата апликација. Можете да го ажурирате вашиот бизнис преку вашиот профил.</p>

                <div style="text-align: center;">
                    <a href="{{ url('https://rezervirajonline.mk') }}" class="button">Ажурирај бизнис</a>
                </div>
            @elseif($action === 'approve')
                <p>Честитки! Вашиот бизнис е сега активен на нашата платформа. Клиентите можат да го најдат вашиот бизнис и да резервираат термини.</p>

                <div style="text-align: center;">
                    <a href="{{ url('https://rezervirajonline.mk') }}" class="button">Одете во контролната табла</a>
                </div>
            @elseif($action === 'reject')
                <p>За жал, вашата апликација не беше одобрена. Можете да се обидете повторно со нова апликација или да контактирате со нашата поддршка за повеќе информации.</p>
            @endif

            <p>Ако имате било какви прашања, слободно контактирајте со нашата поддршка.</p>

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