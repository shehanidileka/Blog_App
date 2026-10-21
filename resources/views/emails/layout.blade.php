<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', config('app.name'))</title>
</head>
<body style="margin:0;padding:0;background:#f4f5ff;font-family:Arial,Helvetica,sans-serif;color:#1f2340;">
 
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5ff;padding:32px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0"
               style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;">

          <tr>
            <td align="center" bgcolor="#4e4c72"
                style="background:#4e4c72;background-image:linear-gradient(135deg,#4e4c72,#6a679c);padding:32px 24px;">
              <div style="font-size:28px;font-weight:700;color:#ffffff;letter-spacing:.3px;">{{ config('app.name') }}</div>
              <div style="font-size:14px;color:#e6e5f5;margin-top:6px;">@yield('tagline')</div>
            </td>
          </tr>

          {{-- Body --}}
          <tr>
            <td style="padding:32px 36px;font-size:15px;line-height:1.6;">
              @yield('content')
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td align="center" style="background:#f8f9ff;padding:20px 36px;font-size:12px;color:#8a8fb0;">
              &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>