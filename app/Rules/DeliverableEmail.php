<?php

namespace App\Rules;

use App\Services\EmailVerificationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DeliverableEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = app(EmailVerificationService::class)->inspect((string) $value);

        if (! $result['ok']) {
            $fail($result['message'] ?: __('users.email_invalid_format'));
        }
    }
}
