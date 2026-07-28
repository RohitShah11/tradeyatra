<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Reset your TradeYatra password</title>
</head>
<body style="margin:0; padding:0; background:#eef4f5; font-family:Arial, Helvetica, sans-serif; color:#172a30;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Your secure TradeYatra password reset link is ready.
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background:#eef4f5;">
        <tr>
            <td align="center" style="padding:36px 16px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:600px; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 14px 40px rgba(6,31,38,.10);">
                    <tr>
                        <td style="padding:0; height:6px; background:linear-gradient(90deg,#18c7c3 0%,#18c7c3 54%,#ff6b16 54%,#ff8a1f 100%); font-size:0;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="padding:30px 38px 26px; background:#071b21;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="48" height="48" align="center" valign="middle" style="width:48px; height:48px;">
                                                    <img src="{{ $message->embed(public_path('images/branding/tradeyatra-icon-v2.png')) }}" width="48" height="48" alt="TradeYatra" style="display:block; width:48px; height:48px; border:0; object-fit:contain;">
                                                </td>
                                                <td style="padding-left:13px;">
                                                    <div style="font-size:22px; line-height:26px; font-weight:800; color:#ffffff;">TradeYatra</div>
                                                    <div style="padding-top:3px; font-size:11px; line-height:15px; letter-spacing:1.5px; text-transform:uppercase; color:#8eabb2;">Your private trading journal</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align="right" valign="middle">
                                        <span style="display:inline-block; padding:7px 11px; border:1px solid #26434a; border-radius:999px; color:#9eb5ba; font-size:11px; font-weight:700;">SECURE ACCOUNT EMAIL</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px 38px 16px;">
                            <div style="display:inline-block; padding:7px 11px; border-radius:999px; background:#fff1e8; color:#d85208; font-size:11px; font-weight:800; letter-spacing:.9px; text-transform:uppercase;">Password assistance</div>
                            <h1 style="margin:18px 0 12px; font-size:30px; line-height:38px; color:#0a252c; letter-spacing:-.6px;">Reset your password</h1>
                            <p style="margin:0; font-size:16px; line-height:26px; color:#536b72;">Hello {{ $userName }},</p>
                            <p style="margin:10px 0 0; font-size:16px; line-height:26px; color:#536b72;">
                                We received a request to reset the password for your TradeYatra account. Use the button below to choose a new secure password.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:20px 38px 26px;">
                            <a href="{{ $resetUrl }}" style="display:inline-block; padding:15px 28px; border-radius:12px; background:#ff6412; color:#ffffff; font-size:16px; line-height:20px; font-weight:800; text-decoration:none; box-shadow:0 8px 20px rgba(255,100,18,.25);">
                                Reset my password &rarr;
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 38px 26px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#e9fbfa; border:1px solid #bdecea; border-radius:13px;">
                                <tr>
                                    <td width="48" valign="top" style="padding:17px 0 17px 18px; color:#08a9a5; font-size:22px;">&#9201;</td>
                                    <td style="padding:16px 18px 16px 8px;">
                                        <div style="font-size:14px; line-height:20px; font-weight:800; color:#12373e;">This link expires in {{ $expireMinutes }} minutes</div>
                                        <div style="padding-top:3px; font-size:13px; line-height:20px; color:#58747a;">For your security, the link can only be used to reset the account associated with this email.</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 38px 34px;">
                            <p style="margin:0; font-size:14px; line-height:23px; color:#657b81;">
                                If you did not request a password reset, you can safely ignore this email. Your current password will remain unchanged.
                            </p>
                            <div style="height:1px; margin:25px 0; background:#e4ecee;"></div>
                            <p style="margin:0 0 8px; font-size:12px; line-height:18px; color:#789096;">Button not working? Copy and paste this secure link into your browser:</p>
                            <p style="margin:0; padding:12px 14px; border-radius:9px; background:#f4f7f8; color:#168b8c; font-size:11px; line-height:18px; word-break:break-all;">
                                <a href="{{ $resetUrl }}" style="color:#168b8c; text-decoration:none;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:22px 30px; background:#f7fafb; border-top:1px solid #e6edef;">
                            <p style="margin:0; font-size:12px; line-height:19px; color:#82959a;">
                                &copy; {{ date('Y') }} TradeYatra &nbsp;&bull;&nbsp; Shark + Delta trading journal
                            </p>
                            <p style="margin:4px 0 0; font-size:11px; line-height:18px; color:#9aacb0;">This is an automated security email. Please do not reply.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
