<?php

namespace Tests\Unit\Http\Controllers\TestTakes\Normalize;

class NormalizeSavingModeTest extends NormalizeTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizeScoreRequest = $this->testTakeFactory->normalizeScoreRequestExamples("0");
    }
}
