@extends('emails.layout')

@section('title', 'Weekly summary')
@section('tagline', $periodFrom->format('M d') . ' – ' . $periodTo->format('M d, Y'))

@section('content')
  <h2 style="margin:0 0 12px;font-size:22px;color:#1f2340;">Hi {{ $adminName }} 👋</h2>
  <p style="margin:0 0 24px;">Here is what happened on <strong>{{ config('app.name') }}</strong> this week.</p>

  {{-- ===== Numbers ===== --}}
  @php
    $cards = [
      'New posts'     => $stats['posts'],
      'New comments'  => $stats['comments'],
      'New reactions' => $stats['reactions'],
      'New users'     => $stats['users'],
    ];
  @endphp

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
    @foreach(array_chunk($cards, 2, true) as $row)
      <tr>
        @foreach($row as $label => $s)
          @php $diff = $s['value'] - $s['prev']; @endphp
          <td width="50%" style="padding:6px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5ff;border-radius:12px;">
              <tr>
                <td style="padding:16px;">
                  <div style="font-size:12px;color:#6b6f93;">{{ $label }}</div>
                  <div style="font-size:30px;font-weight:700;color:#1f2340;line-height:1.2;">{{ $s['value'] }}</div>
                  <div style="font-size:12px;color:{{ $diff > 0 ? '#16a34a' : ($diff < 0 ? '#dc2626' : '#8a8fb0') }};">
                    @if($diff > 0) ▲ +{{ $diff }} @elseif($diff < 0) ▼ {{ $diff }} @else – no change @endif
                    vs last week
                  </div>
                </td>
              </tr>
            </table>
          </td>
        @endforeach
      </tr>
    @endforeach
  </table>

  <p style="margin:0 0 24px;font-size:13px;color:#6b6f93;">
    Total views on all posts: <strong style="color:#1f2340;">{{ number_format($stats['views']) }}</strong>
  </p>

  {{-- ===== Pending posts ===== --}}
  @if($stats['pending'] > 0)
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fff7e6;border-radius:12px;margin:0 0 24px;">
      <tr>
        <td style="padding:14px 16px;font-size:14px;color:#8a5a00;">
          ⏳ <strong>{{ $stats['pending'] }}</strong> {{ \Illuminate\Support\Str::plural('post', $stats['pending']) }} waiting for your approval.
        </td>
      </tr>
    </table>
  @endif

  {{-- ===== Top posts ===== --}}
  <h3 style="margin:0 0 10px;font-size:16px;color:#1f2340;">Top posts this week</h3>

  @forelse($topPosts as $i => $post)
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px;border-bottom:1px solid #eceefa;">
      <tr>
        <td width="32" valign="top" style="padding:8px 0;font-size:18px;font-weight:700;color:#6a679c;">{{ $i + 1 }}</td>
        <td style="padding:8px 0;">
          <a href="{{ route('posts.show', $post) }}" style="color:#1f2340;font-weight:700;text-decoration:none;font-size:14px;">{{ $post->title }}</a>
          <div style="font-size:12px;color:#8a8fb0;">
            by {{ $post->user?->name ?? 'Unknown' }} · {{ $post->week_comments }} comments · {{ $post->week_reactions }} reactions
          </div>
        </td>
      </tr>
    </table>
  @empty
    <p style="margin:0 0 8px;font-size:14px;color:#8a8fb0;">No comments or reactions this week.</p>
  @endforelse

  {{-- ===== Button ===== --}}
  <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin-top:24px;">
    <tr>
      <td align="center" bgcolor="#6a679c" style="border-radius:10px;">
        <a href="{{ route('admin.dashboard') }}"
           style="display:inline-block;padding:14px 30px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:10px;background:#6a679c;">
          Open admin panel
        </a>
      </td>
    </tr>
  </table>
@endsection