@extends('emails.layouts.app')

@section('subject', 'Your sign-in code')

@section('content')
  <h1 style="font-size:22px; font-weight:800; color:#1C4532; margin:0 0 8px;">Your sign-in code</h1>
  <p style="margin: 0 0 12px;">Hi {{ $user->name }},</p>
  <p style="margin: 0 0 12px;">Use the code below to finish signing in to your account.</p>
  <div style="margin:16px 0 12px; padding:16px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; text-align:center;">
    <div style="font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size:28px; font-weight:800; letter-spacing:6px; color:#0F172A;">
      {{ $code }}
    </div>
    <p class="muted" style="margin:10px 0 0; font-size:12px; color:#64748b;">This code expires in {{ $expiresIn }} minutes.</p>
  </div>
  <p class="muted" style="margin-top:18px;">If you didn’t try to sign in, you can safely ignore this email.</p>
@endsection
