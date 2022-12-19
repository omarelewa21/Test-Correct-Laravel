<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use Tests\ScenarioLoader;
use Tests\TestCase;

class MatrixQuestionTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    /**
     * A basic functional test example.
     * @test
     *
     * @return void
     */
    public function should_add_valid_matrixQuestion()
    {
        $testId = FactoryTest::create(ScenarioLoader::get('teacher1'))->getTestId();
        $response = $this->post(
            'api-c/test_question',
            static::getAuthRequestData([
                "question"               => "<p>a nice matrix question</p>\r\n",
                "type"                   => "MatrixQuestion",
                "score"                  => "5",
                "order"                  => 0,
                "answers"                => [
                    "answers"      => [
                        [
                            'answer' => 'a',
                            'order'  => 0
                        ],
                        [
                            'answer' => 'b',
                            'order'  => 1
                        ],
                        [
                            'answer' => 'c',
                            'order'  => 2
                        ]
                    ],
                    "subQuestions" => [
                        [
                            'sub_question' => 'antwoord zou a moeten zijn',
                            'order'        => 0,
                            'score'        => 2,
                            'answers'      => [0],
                        ],
                        [
                            'sub_question' => 'vraag nummer 2 (antwoord c)',
                            'order'        => 1,
                            'score'        => 1,
                            'answers'      => [2],
                        ],
                        [
                            'sub_question' => 'vraag nummer 3 (antwoord b)',
                            'order'        => 2,
                            'score'        => 2,
                            'answers'      => [1],
                        ],
                        [
                            'sub_question' => 'vraag nummer 4 (antwoord c)',
                            'order'        => 3,
                            'score'        => 1,
                            'answers'      => [2],
                        ]
                    ],
                ],
                "maintain_position"      => "0",
                "subtype"                => "SingleChoice",
                "discuss"                => "1",
                "decimal_score"          => "0",
                "add_to_database"        => "1",
                "attainments"            => [],
                "note_type"              => "NONE",
                "is_open_source_content" => 0,
                "tags"                   => [],
                "closeable"              => 0,

                "test_id" => $testId
            ], ScenarioLoader::get('teacher1')));

        $responseData = json_decode($response->getContent());

        // is not correct should be 201, 200 should be reserved for requests that are successful but did not persist new data.
        $response->assertSuccessful();

        //   $this->assertEquals(3, $response->order);
        $this->assertEquals('1', $responseData->test_id);
        $this->assertEquals('1', $responseData->discuss);

        $magicId = $responseData->id;

        $response = $this->get(
            sprintf(
                '/test_question/%d?user=%s',
                $magicId,
                static::USER_TEACHER
            )
        );


        $responseData = json_decode($response->getContent());

        $question = $responseData->question;
        $this->assertEquals('MatrixQuestion', $question->type);

        $author = $question->authors[0];
        $this->assertEquals(static::USER_TEACHER, $author->username);

    }
}
