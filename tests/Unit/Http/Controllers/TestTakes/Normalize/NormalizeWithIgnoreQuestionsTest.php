<?php

namespace Tests\Unit\Http\Controllers\TestTakes\Normalize;

class NormalizeWithIgnoreQuestionsTest extends NormalizeTest
{
    protected $preview = 1;

    protected function setUp(): void
    {
        parent::setUp();
        
        $ignoreQuestions = $this->testTakeFactory->test->testQuestions->random(2)->pluck('question_id')->toArray();
        $this->normalizeScoreRequest = (object) [
            'pppExample' => [
                "ignore_questions" => $ignoreQuestions,
                "preview"          => $this->preview,
                "ppp"              => "1", //amount correct needed per point, 1.5 is 15 'score' needed for a 10
            ],
            'eppExample' => [
                "ignore_questions" => $ignoreQuestions,
                "preview"          => $this->preview,
                "epp"              => "1",
            ],
            'WanAvgExample' => [
                "ignore_questions" => $ignoreQuestions,
                "preview"          => $this->preview,
                "wanted_average"   => "7.5",
            ],
            'n_termExample' => [
                "ignore_questions" => $ignoreQuestions,
                "preview"          => $this->preview,
                "n_term"           => "1",
            ],
            'CesuurExample' => [
                "ignore_questions" => $ignoreQuestions,
                "preview"          => $this->preview,
                "n_term"           => "1",
                "pass_mark"        => "50",
            ]
        ];
    }
}
