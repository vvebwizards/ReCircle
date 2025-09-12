@extends('emails.layouts.app')

@section('subject', 'Verify Your Email Address')

@section('content')
  <h1 style="font-size:22px; font-weight:800; color:#1C4532; margin:0 0 8px;">Verify your email</h1>
  <p style="margin: 0 0 12px;">Hi {{ $user->name }},</p>
  <p style="margin: 0 0 12px;">Thanks for joining ReCircle. Please confirm your email address to activate your account.</p>
  <p style="margin: 0 0 18px;">This link will expire in {{ $expiresIn }} minutes.</p>
  <table role="presentation" cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td>
        <a class="btn" href="{{ $verifyUrl }}" target="_blank" rel="noopener">Verify Email</a>
      </td>
    </tr>
  </table>
  <div class="divider"></div>
  <p class="muted" style="margin:0 0 6px;">If the button above doesn’t work, copy and paste this URL into your browser:</p>
  <p style="word-break: break-all; font-size:12px; color:#334155;">{{ $verifyUrl }}</p>
  <p class="muted" style="margin-top:18px;">If you didn’t create an account, you can safely ignore this email.</p>
@endsection
