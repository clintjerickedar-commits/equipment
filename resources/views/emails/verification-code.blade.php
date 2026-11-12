<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verify Your Email</title>
</head>
<body style="margin:0;padding:0;background:#F5F7FB;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#F5F7FB;padding:32px 12px;">
  <tr><td align="center">
    <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="max-width:560px;width:100%;background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(20,43,74,.10);">
      <tr><td style="background:#0A0A0A;padding:28px 32px;">
        <table role="presentation" cellspacing="0" cellpadding="0"><tr>
          <td style="background:#D10000;border-radius:10px;width:40px;height:40px;text-align:center;font-size:20px;color:#fff;">&#9635;</td>
          <td style="padding-left:12px;color:#FFFFFF;font-size:17px;font-weight:700;">Equipment Borrowing<br><span style="font-size:12px;font-weight:400;color:#B5B5B5;">Management System</span></td>
        </tr></table>
      </td></tr>
      <tr><td style="padding:36px 32px 12px;">
        <h1 style="margin:0 0 12px;font-size:22px;color:#0A0A0A;">Welcome to the Equipment Borrowing System!</h1>
        <p style="margin:0 0 8px;font-size:15px;line-height:1.6;">Hello {{ $user->name }},</p>
        <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#44506A;">Welcome aboard, and thank you for registering with the Equipment Borrowing Management System. To verify your email address and finish creating your account, please use the following verification code:</p>
        <div style="background:#FFF1F1;border:1px solid #FFD0D0;border-radius:14px;padding:22px;text-align:center;">
          <div style="font-family:'Courier New',monospace;font-size:38px;font-weight:700;letter-spacing:12px;color:#D10000;padding-left:12px;">{{ $code }}</div>
        </div>
        <p style="margin:20px 0 0;font-size:14px;color:#44506A;text-align:center;">This code will expire after <strong>{{ $minutes }} minutes</strong>.</p>
      </td></tr>
      <tr><td style="padding:8px 32px 32px;">
        <p style="margin:16px 0 0;font-size:13px;line-height:1.6;color:#64748B;">If you did not create this account, please ignore this email. Never share this code with anyone.</p>
      </td></tr>
      <tr><td style="background:#F8FAFD;padding:18px 32px;border-top:1px solid #E6EAF2;font-size:12px;color:#8190A8;text-align:center;">
        Thank you,<br>Equipment Borrowing Management System
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
