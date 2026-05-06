<?php

return [
    'verify_ttl_minutes' => (int) env('AUTH_OTP_VERIFY_TTL_MINUTES', 10),
    'reset_ttl_minutes' => (int) env('AUTH_OTP_RESET_TTL_MINUTES', 60),
    'otp_length' => 6,
    'max_verify_attempts' => (int) env('AUTH_OTP_MAX_ATTEMPTS', 5),
    'verify_lockout_seconds' => (int) env('AUTH_OTP_LOCKOUT_SECONDS', 900),
];
