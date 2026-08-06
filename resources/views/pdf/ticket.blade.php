<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            margin: 0;
            padding: 0;
            font-family: Helvetica, Arial, sans-serif;
            color: #1e293b;
        }
        .header {
            background-color: #002868;
            color: #ffffff;
            padding: 28px 32px;
            text-align: center;
        }
        .header .kicker {
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #fbbf24;
            margin: 0 0 6px 0;
        }
        .header .title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .body {
            padding: 28px 32px;
        }
        .guest-label {
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #94a3b8;
            margin: 0 0 4px 0;
        }
        .guest-name {
            font-size: 24px;
            font-weight: bold;
            color: #002868;
            margin: 0 0 20px 0;
        }
        table.details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        table.details td {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }
        table.details td.label {
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 90px;
        }
        table.details td.value {
            color: #1e293b;
            font-weight: bold;
        }
        .qr-wrap {
            text-align: center;
            padding: 12px 0 4px 0;
        }
        .qr-wrap img {
            width: 160px;
            height: 160px;
        }
        .footer-note {
            text-align: center;
            font-size: 11px;
            color: #64748b;
            margin-top: 8px;
        }
        .status {
            display: inline-block;
            margin-top: 4px;
            padding: 3px 10px;
            border-radius: 10px;
            background-color: #fef2f2;
            color: #BF0A30;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="kicker">Republic of Liberia &middot; Ministry of Education</p>
        <p class="title">{{ config('event.name') }}</p>
    </div>

    <div class="body">
        <p class="guest-label">Guest</p>
        <p class="guest-name">{{ $attendee->full_name }}</p>

        <table class="details">
            <tr>
                <td class="label">Date</td>
                <td class="value">{{ date('l, F j, Y', strtotime(config('event.date'))) }}</td>
            </tr>
            <tr>
                <td class="label">Time</td>
                <td class="value">{{ date('g:i A', strtotime(config('event.start_time'))) }} &ndash; {{ date('g:i A', strtotime(config('event.end_time'))) }}</td>
            </tr>
            <tr>
                <td class="label">Venue</td>
                <td class="value">{{ config('event.venue') }}, {{ config('event.venue_address') }}</td>
            </tr>
        </table>

        <div class="qr-wrap">
            <img src="{{ $qrDataUri }}" alt="Check-in QR code">
        </div>

        <p class="footer-note">Present this ticket (printed or on your phone) at the entrance for check-in.</p>
    </div>
</body>
</html>
