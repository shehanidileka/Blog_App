@extends('emails.layout')

@section('title', 'SMTP test')
@section('tagline', 'Email settings check')

@section('content')
  <h2 style="margin:0 0 12px;font-size:22px;color:#1f2340;">Hello {{ $recipientName }} 👋</h2>

  <p style="margin:0 0 20px;">
    Great news! Your <strong style="color:#6a679c;">SMTP settings are working</strong>.
    This is a test email sent from <strong>{{ config('app.name') }}</strong>.
  </p>

  @if(count($details))
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
           style="background:#f4f5ff;border-radius:12px;margin:0 0 24px;">
      @foreach($details as $label => $value)
        <tr>
          <td style="padding:10px 16px;font-size:13px;color:#6b6f93;width:40%;">{{ $label }}</td>
          <td style="padding:10px 16px;font-size:13px;font-weight:700;color:#1f2340;">{{ $value }}</td>
        </tr>
      @endforeach
    </table>
  @endif

  <table role="presentation" cellpadding="0" cellspacing="0" align="center">
    <tr>
      <td align="center" bgcolor="#6a679c" style="border-radius:10px;">
        <a href="{{ url('/') }}"
           style="display:inline-block;padding:14px 30px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:10px;background:#6a679c;">
          Open {{ config('app.name') }}
        </a>
      </td>
    </tr>
  </table>
@endsection