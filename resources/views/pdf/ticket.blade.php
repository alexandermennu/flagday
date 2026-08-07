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
            background-color: #1b2652;
            border-bottom-left-radius: 28px;
            border-bottom-right-radius: 28px;
            padding: 26px 24px 20px;
            text-align: center;
        }
        .check-badge {
            display: inline-block;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #ffffff;
            text-align: center;
            margin-bottom: 10px;
        }
        .check-badge img { width: 20px; height: 20px; margin-top: 9px; }
        .header .title {
            font-size: 19px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 5px 0;
        }
        .header .subtitle {
            font-size: 11px;
            font-weight: bold;
            color: #c7cbe0;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
        }

        .body { padding: 22px 26px 6px; }

        .conf-id-label {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #1b2652;
            margin: 0 0 8px 0;
        }
        .conf-id-box {
            text-align: center;
            background-color: #f7f4ef;
            border-radius: 8px;
            padding: 12px;
            font-size: 19px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #b91c1c;
            margin: 0 0 20px 0;
        }

        table.info-qr { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.info-qr td.info-col { width: 58%; vertical-align: top; padding-right: 14px; }
        table.info-qr td.qr-col { width: 42%; vertical-align: top; text-align: center; border-left: 1px solid #e2e8f0; padding-left: 14px; }

        table.info-row { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.info-row td { padding: 9px 0; border-bottom: 1px solid #eef0f3; vertical-align: middle; }
        table.info-row td.icon-cell { width: 26px; }
        .info-icon {
            display: inline-block;
            width: 22px;
            height: 22px;
            border-radius: 5px;
            background-color: #1b2652;
            text-align: center;
        }
        .info-icon img { width: 13px; height: 13px; margin-top: 4.5px; }
        .info-label { font-size: 9px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #94a3b8; margin: 0; }
        .info-value { font-size: 12.5px; font-weight: bold; color: #1b2652; margin: 2px 0 0 0; }

        .qr-frame { display: inline-block; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px; }
        .qr-frame img { width: 108px; height: 108px; }
        .qr-caption { font-size: 9.5px; color: #475569; margin-top: 8px; line-height: 1.4; }

        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 2px 0 14px 0; }

        table.details-row { width: 100%; border-collapse: collapse; }
        table.details-row td { width: 25%; text-align: center; vertical-align: top; padding: 0 3px; }
        .detail-icon img { width: 18px; height: 18px; }
        .detail-label { font-size: 8px; font-weight: bold; letter-spacing: 0.75px; text-transform: uppercase; color: #94a3b8; margin: 5px 0 3px 0; }
        .detail-value { font-size: 10px; font-weight: bold; color: #1b2652; line-height: 1.35; margin: 0; }

        .footer { background-color: #1b2652; color: #ffffff; padding: 20px 22px; margin-top: 16px; }
        table.footer-table { width: 100%; border-collapse: collapse; }
        table.footer-table td { vertical-align: middle; }
        .footer-logo-col { width: 56px; }
        .footer-emblem { width: 46px; height: 46px; border-radius: 50%; background-color: #ffffff; text-align: center; }
        .footer-emblem img { width: 32px; height: 32px; margin-top: 7px; }
        .footer-center-col { width: 48%; text-align: center; padding: 0 10px; }
        .footer-event-name { font-size: 10.5px; font-weight: bold; letter-spacing: 0.75px; margin: 0 0 6px 0; text-transform: uppercase; }
        .footer-tagline { font-size: 8.5px; font-style: italic; color: #d4af37; margin: 0; line-height: 1.45; }
        .footer-contact-col { width: 34%; text-align: right; }
        .footer-contact-heading { font-size: 9px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; color: #d4af37; margin: 0 0 6px 0; }
        .footer-contact-line { font-size: 8px; color: #e2e5f0; margin: 0 0 3px 0; line-height: 1.5; }
        .footer-contact-line:last-child { margin-bottom: 0; }
    </style>
</head>
<body>
    @php
        $icon = fn (string $name, string $color) => \App\Services\IconFactory::dataUri($name, $color);
        $edition = \Illuminate\Support\Number::ordinal(date('Y', strtotime(config('event.date'))) - 1847);
    @endphp

    <div class="header">
        <div class="check-badge">
            <img src="{{ $icon('check', '#1b2652') }}" alt="">
        </div>
        <p class="title">Attendance Confirmed.</p>
        <p class="subtitle">Your Spot Is Secured</p>
    </div>

    <div class="body">
        <p class="conf-id-label">Confirmation ID</p>
        <div class="conf-id-box">{{ $attendee->confirmation_id }}</div>

        <table class="info-qr">
            <tr>
                <td class="info-col">
                    <table class="info-row">
                        <tr>
                            <td class="icon-cell">
                                <span class="info-icon"><img src="{{ $icon('person', '#ffffff') }}" alt=""></span>
                            </td>
                            <td>
                                <p class="info-label">Issued To</p>
                                <p class="info-value">{{ $attendee->full_name }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="icon-cell">
                                <span class="info-icon"><img src="{{ $icon('building', '#ffffff') }}" alt=""></span>
                            </td>
                            <td>
                                <p class="info-label">Organization</p>
                                <p class="info-value">{{ $attendee->organization }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="icon-cell">
                                <span class="info-icon"><img src="{{ $icon('briefcase', '#ffffff') }}" alt=""></span>
                            </td>
                            <td>
                                <p class="info-label">Position</p>
                                <p class="info-value">{{ $attendee->position }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="qr-col">
                    <div class="qr-frame">
                        <img src="{{ $qrDataUri }}" alt="Check-in QR code">
                    </div>
                    <p class="qr-caption">Please present this digital invitation at the event entrance.</p>
                </td>
            </tr>
        </table>

        <hr class="divider">

        <table class="details-row">
            <tr>
                <td>
                    <div class="detail-icon"><img src="{{ $icon('calendar', '#1b2652') }}" alt=""></div>
                    <p class="detail-label">Date</p>
                    <p class="detail-value">
                        {{ date('F j, Y', strtotime(config('event.date'))) }}<br>
                        ({{ date('l', strtotime(config('event.date'))) }})
                    </p>
                </td>
                <td>
                    <div class="detail-icon"><img src="{{ $icon('clock', '#1b2652') }}" alt=""></div>
                    <p class="detail-label">Time</p>
                    <p class="detail-value">{{ date('g:i A', strtotime(config('event.start_time'))) }}<br>Prompt</p>
                </td>
                <td>
                    <div class="detail-icon"><img src="{{ $icon('pin', '#1b2652') }}" alt=""></div>
                    <p class="detail-label">Venue</p>
                    <p class="detail-value">{{ config('event.venue') }}<br>{{ config('event.venue_address') }}</p>
                </td>
                <td>
                    <div class="detail-icon"><img src="{{ $icon('tie', '#1b2652') }}" alt=""></div>
                    <p class="detail-label">Dress Code</p>
                    <p class="detail-value">{{ config('event.dress_code') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-logo-col">
                    <div class="footer-emblem">
                        <img src="{{ \App\Services\IconFactory::emblemDataUri('moe') }}" alt="Ministry of Education">
                    </div>
                </td>
                <td class="footer-center-col">
                    <p class="footer-event-name">{{ $edition }} National Flag Day Celebration</p>
                    <p class="footer-tagline">&ldquo;{{ config('event.theme') }}&rdquo;</p>
                </td>
                <td class="footer-contact-col">
                    <p class="footer-contact-heading">Contact Us</p>
                    <p class="footer-contact-line">{{ config('event.contact_phone') }}</p>
                    <p class="footer-contact-line">{{ config('event.contact_phone_secondary') }}</p>
                    <p class="footer-contact-line">WhatsApp: {{ config('event.contact_whatsapp') }}</p>
                    <p class="footer-contact-line">{{ config('event.contact_email') }}</p>
                    <p class="footer-contact-line">{{ config('event.contact_website') }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
