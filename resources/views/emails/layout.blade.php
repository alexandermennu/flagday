<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('subject', config('event.name'))</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f1f5f9; font-family: Helvetica, Arial, sans-serif; color: #1e293b; }
        .wrapper { width: 100%; padding: 24px 16px; }
        .card { max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; }
        .header { background-color: #1b2652; padding: 26px 32px; text-align: center; }
        .header .kicker { font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #d4af37; margin: 0 0 6px 0; }
        .header .title { font-size: 18px; font-weight: bold; color: #ffffff; margin: 0; text-transform: uppercase; }
        .body { padding: 32px; }
        .body h1 { font-size: 19px; font-weight: bold; color: #1b2652; margin: 0 0 16px 0; }
        .body p { font-size: 14px; line-height: 1.65; color: #334155; margin: 0 0 16px 0; }
        .body p:last-child { margin-bottom: 0; }
        .details { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details td { padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        .details td.label { color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; width: 90px; }
        .details td.value { color: #1e293b; font-weight: bold; }
        .calendar-link { font-size: 13px; font-weight: bold; color: #1b2652 !important; text-decoration: none; }
        .calendar-link::before { content: "＋ "; }
        .emblems { text-align: center; margin: 24px 0 6px 0; }
        .emblems img { width: 44px; height: 44px; margin: 0 10px; }
        .emblem-captions { text-align: center; font-size: 10px; color: #94a3b8; margin: 0 0 20px 0; }
        .emblem-captions span { display: inline-block; width: 110px; }
        .signature { font-size: 12px; line-height: 1.7; color: #64748b; margin: 0; }
        .signature strong { color: #1b2652; }
        .footer { text-align: center; padding: 16px 32px 0; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <p class="kicker">Republic of Liberia &middot; Ministry of Education</p>
                <p class="title">{{ config('event.name') }}</p>
            </div>
            <div class="body">
                @yield('content')
            </div>
        </div>
        <p class="footer">
            &copy; {{ date('Y') }} Government of the Republic of Liberia, Ministry of Education.
        </p>
    </div>
</body>
</html>
