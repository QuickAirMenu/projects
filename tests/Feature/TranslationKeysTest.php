<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class TranslationKeysTest extends TestCase
{
    public function test_finance_view_keys_exist_in_both_locales(): void
    {
        foreach (['ar', 'en'] as $locale) {
            foreach (['new_quote', 'quote_status_label'] as $key) {
                $this->assertTrue(
                    Lang::has('finance.'.$key, $locale),
                    "Missing finance.$key for locale $locale"
                );
            }
        }
    }
}
