<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #111;">
    <p>{{ __('Hello') }} {{ $user->name }},</p>
    <p>{{ __('Your verification code is:') }}</p>
    <p style="font-size: 1.5rem; font-weight: bold; letter-spacing: 0.25em;">{{ $otp }}</p>
    <p>{{ __('This code expires in :minutes minutes.', ['minutes' => config('auth_tokens.verify_ttl_minutes')]) }}</p>
    <p style="margin-top: 2rem; font-size: 0.875rem; color: #555;">{{ config('app.name') }}</p>
</body>
</html>
