<?php

namespace Tests\Feature;

use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_framework_boots_for_feature_tests(): void
    {
        $this->assertTrue(true);
    }
}
