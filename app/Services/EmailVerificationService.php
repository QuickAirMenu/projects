<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class EmailVerificationService
{
    public const COMMON_DOMAINS = [
        'gmail.com', 'googlemail.com', 'outlook.com', 'hotmail.com', 'live.com', 'msn.com',
        'yahoo.com', 'icloud.com', 'me.com', 'protonmail.com', 'proton.me', 'aol.com',
        'zoho.com', 'yandex.com', 'gmx.com', 'mail.com',
    ];

    public function inspect(string $email): array
    {
        $email = trim($email);
        $result = [
            'email' => $email,
            'valid_format' => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
            'domain' => null,
            'has_mx' => null,
            'suggestion' => null,
            'ok' => false,
            'message' => null,
        ];

        if (! $result['valid_format']) {
            $result['message'] = __('users.email_invalid_format');

            return $result;
        }

        $domain = strtolower((string) substr(strrchr($email, '@'), 1));
        $result['domain'] = $domain;

        $suggestion = $this->suggestionFor($domain);
        if ($suggestion !== null) {
            $result['suggestion'] = $suggestion;
        }

        if (! (bool) config('mail.email_domain_check', true)) {
            $result['ok'] = $suggestion === null;
            $result['message'] = $result['ok'] ? null : __('users.email_suggestion', ['email' => $this->replaceDomain($email, $suggestion)]);

            return $result;
        }

        $result['has_mx'] = $this->domainAcceptsMail($domain);

        if (! $result['has_mx']) {
            $result['ok'] = false;
            $result['message'] = __('users.email_domain_unreachable', ['domain' => $domain]);

            return $result;
        }

        if ($suggestion !== null) {
            $result['ok'] = false;
            $result['message'] = __('users.email_suggestion', ['email' => $this->replaceDomain($email, $suggestion)]);

            return $result;
        }

        $result['ok'] = true;

        return $result;
    }

    public function suggestionFor(string $domain): ?string
    {
        $domain = strtolower($domain);

        if (in_array($domain, self::COMMON_DOMAINS, true)) {
            return null;
        }

        $best = null;
        $distance = 3;

        foreach (self::COMMON_DOMAINS as $common) {
            $d = levenshtein($domain, $common);
            if ($d < $distance) {
                $distance = $d;
                $best = $common;
            }
        }

        return $best;
    }

    public function replaceDomain(string $email, ?string $domain): string
    {
        if (! $domain) {
            return $email;
        }

        $at = strrpos($email, '@');

        return $at === false ? $email : substr($email, 0, $at + 1).$domain;
    }

    protected function domainAcceptsMail(string $domain): bool
    {
        return (bool) Cache::remember('email_domain_ok:'.$domain, now()->addHour(), function () use ($domain) {
            if (function_exists('checkdnsrr')) {
                if (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA')) {
                    return true;
                }
            }

            if (function_exists('dns_get_record')) {
                $records = @dns_get_record($domain, DNS_MX | DNS_A | DNS_AAAA);

                return is_array($records) && $records !== [];
            }

            return true;
        });
    }
}
