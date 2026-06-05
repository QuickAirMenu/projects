<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SmokeTest extends TestCase
{
    public function test_basic_math_is_correct(): void
    {
        $this->assertSame(4, 2 + 2);
    }
}
