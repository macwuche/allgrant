{{-- Wraps an admin-authored email-inbox message (compose or reply) in a branded,
     responsive header/footer before it goes out to Resend. See email.md section 12
     for the build plan and the reasoning behind each choice below -- this is the
     one place that plan's HTML lives.

     This view is rendered server-side purely to produce the HTML string sent as
     `html` in the Resend API payload (EmailInboxController::deliver()). It is NOT
     what gets stored in emails.html_body or shown in our own thread view -- that
     stays exactly what the admin typed, unwrapped.

     Table-based, inline-styled "fluid-hybrid" layout so it degrades correctly with
     zero reliance on @media support (Outlook desktop's Word engine has none), and
     the @media block on top of that is a progressive enhancement for clients that
     do support it. Do not swap the accent bar for a CSS gradient -- Outlook
     desktop does not render linear-gradient() backgrounds at all. --}}
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<title>{{ $subject }}</title>
<!--[if mso]>
<noscript>
<xml>
<o:OfficeDocumentSettings>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
</noscript>
<style>table, td { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }</style>
<![endif]-->
<style>
    body, table, td { font-family: Arial, Helvetica, sans-serif; }
    body { margin: 0; padding: 0; background: #f1f2f6; }
    img { border: 0; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
    table { border-collapse: collapse; }
    a { color: {{ $brandColor }}; }
    @media (max-width: 600px) {
        .email-container { width: 100% !important; }
        .email-px { padding-left: 20px !important; padding-right: 20px !important; }
        .email-py { padding-top: 24px !important; padding-bottom: 24px !important; }
        .email-logo { max-height: 32px !important; }
        .email-body-text { font-size: 15px !important; line-height: 24px !important; }
    }
</style>
</head>
<body style="margin:0;padding:0;background:#f1f2f6;">
<div style="display:none;max-height:0;overflow:hidden;">{{ $preheader ?? '' }}</div>
<center>
<!--[if mso]>
<table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td>
<![endif]-->
<table role="presentation" class="email-container" width="600" align="center" cellpadding="0" cellspacing="0" border="0"
       style="width:600px;max-width:600px;margin:0 auto;">
    <tr>
        <td style="padding:24px 0;" class="email-py">&nbsp;</td>
    </tr>

    {{-- Header: solid brand-color accent bar + centered logo, white card --}}
    <tr>
        <td style="background:{{ $brandColor }};height:6px;line-height:6px;font-size:0;border-radius:8px 8px 0 0;">&nbsp;</td>
    </tr>
    <tr>
        <td class="email-px" style="background:#ffffff;padding:28px 40px 20px;text-align:center;">
            @if($siteLogo)
                <a href="{{ $siteLink }}" style="text-decoration:none;">
                    <img class="email-logo" src="{{ $siteLogo }}" alt="{{ $siteTitle }}" style="max-height:40px;width:auto;">
                </a>
            @else
                <a href="{{ $siteLink }}" style="text-decoration:none;font-size:20px;font-weight:bold;color:#1f2937;">{{ $siteTitle }}</a>
            @endif
        </td>
    </tr>

    {{-- Body: the admin's own message, untouched aside from this wrapper --}}
    <tr>
        <td class="email-px" style="background:#ffffff;padding:12px 40px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="email-body-text" style="color:#333333;font-size:16px;line-height:26px;">
                        {!! $bodyHtml !!}
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Footer: dark bar, centered copyright/custom text --}}
    <tr>
        <td class="email-px" style="background:#1f2937;padding:24px 40px;text-align:center;border-radius:0 0 8px 8px;">
            <p style="margin:0;color:#9ca3af;font-size:13px;line-height:20px;">
                {!! $footerText ? nl2br(e($footerText)) : '&copy; '.date('Y').' '.e($siteTitle).'. All rights reserved.' !!}
            </p>
            @if($siteLink)
                <p style="margin:8px 0 0;">
                    <a href="{{ $siteLink }}" style="color:{{ $brandColor }};font-size:13px;text-decoration:none;">{{ $siteLink }}</a>
                </p>
            @endif
        </td>
    </tr>

    <tr>
        <td style="padding:24px 0;" class="email-py">&nbsp;</td>
    </tr>
</table>
<!--[if mso]>
</td></tr></table>
<![endif]-->
</center>
</body>
</html>
