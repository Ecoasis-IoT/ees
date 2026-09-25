<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign-in code</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Lato,Arial,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f4;">
    <tr>
      <td align="center" style="padding:30px 10px;">
        <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">
          <tr>
            <td style="background:#3D8881;padding:28px 20px;text-align:center;">
              <p style="margin:0;font-size:24px;letter-spacing:1px;color:#ffffff;font-weight:bold;">SIGN-IN CODE</p>
              <p style="margin:8px 0 0 0;font-size:14px;color:#e8f3f2;">{$app_name}</p>
            </td>
          </tr>
          <tr>
            <td style="padding:32px 36px;color:#333333;font-size:15px;line-height:1.55;">
              <p style="margin:0 0 16px 0;">Use this code to finish signing in. It expires in 15 minutes.</p>
              <p style="margin:0 0 16px 0;text-align:center;font-size:32px;letter-spacing:8px;font-weight:bold;color:#3D8881;">{$code}</p>
              <p style="margin:0;color:#555555;">If you did not try to sign in, you can ignore this email.</p>
            </td>
          </tr>
          <tr>
            <td style="background:#e8f3f2;padding:18px 20px;text-align:center;">
              <p style="margin:0;font-size:12px;color:#3D8881;">Ecoasis Energy &mdash; {$app_name}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
