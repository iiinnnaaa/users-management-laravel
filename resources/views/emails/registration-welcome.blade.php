<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #111;">
    <p>{{ __('Hello') }} {{ $user->name }},</p>
    <p>{{ __('Your account was created successfully.') }}</p>
    <p>{{ __('You can log in with this email address using the password you chose at registration.') }}</p>
    <p style="margin-top: 2rem; font-size: 0.875rem; color: #555;">{{ config('app.name') }}</p>
</body>
</html>
