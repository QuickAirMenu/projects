<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_and_terms_render_in_both_locales(): void
    {
        foreach (['ar', 'en'] as $locale) {
            $this->withSession(['locale' => $locale])->get(route('privacy'))
                ->assertOk()
                ->assertSee(__('legal.privacy.title', [], $locale))
                ->assertSee(__('legal.privacy.sections')[0]['heading'], false);

            $this->withSession(['locale' => $locale])->get(route('terms'))
                ->assertOk()
                ->assertSee(__('legal.terms.title', [], $locale));
        }
    }

    public function test_auth_footer_contains_legal_links_and_controls(): void
    {
        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringContainsString('auth-footer', $html);
        $this->assertStringContainsString(route('privacy'), $html);
        $this->assertStringContainsString(route('terms'), $html);
        $this->assertStringContainsString('data-auth-theme-toggle', $html);
        $this->assertStringContainsString('fa-language', $html);
        $this->assertTrue(strpos($html, 'auth-footer') < strpos($html, 'data-auth-theme-toggle'));
    }
}
