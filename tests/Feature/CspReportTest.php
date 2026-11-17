<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CspReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csp_report_is_logged_and_accepted(): void
    {
        Log::spy();

        $this->postJson('/csp-report', [
            'csp-report' => [
                'document-uri' => 'https://example.test/dashboard',
                'violated-directive' => 'script-src',
                'blocked-uri' => 'https://evil.example/x.js',
            ],
        ])->assertNoContent();

        Log::shouldHaveReceived('warning')->withArgs(function (string $message, array $context) {
            return $message === 'csp-report' && $context['directive'] === 'script-src';
        })->once();
    }

    public function test_csp_header_points_to_report_endpoint(): void
    {
        $response = $this->get(route('login'))->assertOk();
        $this->assertStringContainsString('report-uri /csp-report', (string) $response->headers->get('Content-Security-Policy-Report-Only'));
    }
}
