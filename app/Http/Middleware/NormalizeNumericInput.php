<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeNumericInput
{
    protected array $skipKeys = ['password', 'password_confirmation', 'current_password'];

    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            $request->merge($this->normalize($request->all()));
        }

        return $next($request);
    }

    protected function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array((string) $key, $this->skipKeys, true)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->normalize($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->digits($value);
            }
        }

        return $data;
    }

    protected function digits(string $value): string
    {
        $map = [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٫' => '.', '٬' => '', '،' => ',',
        ];

        return strtr($value, $map);
    }
}
