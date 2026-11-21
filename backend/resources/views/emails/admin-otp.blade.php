<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $code }} is your CareerPathAI verification code</title>
</head>
<body style="margin:0; padding:0; background-color:#fafafa; -webkit-text-size-adjust:100%; text-size-adjust:100%;">
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
        Use this code to finish signing in. It expires in 10 minutes.
    </div>
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fafafa;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" border="0" style="max-width:480px; width:100%; background-color:#ffffff; border-radius:20px; border:1px solid #f0f0f0;">

                    <tr>
                        <td align="center" style="padding:36px 40px 8px 40px;">
                            <img src="{{ $message->embed(public_path('images/careerpath_ai.png')) }}" width="200" height="64" alt="CareerPathAI" style="display:block; width:200px; max-width:200px; height:64px; border:0; outline:none;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 40px 0 40px;">
                            <h1 style="margin:0; font-family:Poppins,Arial,Helvetica,sans-serif; font-size:22px; font-weight:700; color:#111111; text-align:center;">
                                Verify it's you
                            </h1>
                            <p style="margin:10px 0 0 0; font-family:Poppins,Arial,Helvetica,sans-serif; font-size:14px; line-height:22px; color:#555555; text-align:center;">
                                Enter this code to finish signing in to the CareerPathAI Admin Panel.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 40px 0 40px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="background-color:#FDF2FF; border:1px solid #FFD2F7; border-radius:14px; padding:22px 16px;">
                                        <span style="font-family:'Courier New',Courier,monospace; font-size:34px; font-weight:700; letter-spacing:10px; color:#890080;">
                                            {{ $code }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 40px 0 40px;">
                            <p style="margin:0; font-family:Poppins,Arial,Helvetica,sans-serif; font-size:13px; line-height:20px; color:#888888; text-align:center;">
                                This code expires in <strong style="color:#555555;">10 minutes</strong> and can only be used once.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 40px 0 40px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="border-top:1px solid #f0f0f0; font-size:0; line-height:0;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 40px 36px 40px;">
                            <p style="margin:0; font-family:Poppins,Arial,Helvetica,sans-serif; font-size:12px; line-height:19px; color:#9a9a9a; text-align:center;">
                                Didn't request this code? You can safely ignore this email — your account is still secure.
                            </p>
                        </td>
                    </tr>

                </table>

                <table role="presentation" width="480" cellpadding="0" cellspacing="0" border="0" style="max-width:480px; width:100%;">
                    <tr>
                        <td align="center" style="padding:24px 40px 0 40px;">
                            <p style="margin:0; font-family:Poppins,Arial,Helvetica,sans-serif; font-size:12px; line-height:18px; color:#b0b0b0; text-align:center;">
                                This is an automated message from CareerPathAI. Please do not reply.<br>
                                &copy; {{ date('Y') }} CareerPathAI. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>
