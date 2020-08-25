<?php
namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class MatrixQuestionTest extends TestCase {

    use DatabaseTransactions;

	/**
	 * A basic functional test example.
     * @test
	 *
	 * @return void
	 */
	public function should_add_valid_matrixQuestion()
	{

        $this->post('/test_question', static::getAuthRequestData([
            "question"               => "<p>a nice matrix question</p>\r\n",
            "type"                   => "MatrixQuestion",
            "score"                  => "5",
            "order"                  => 0,
            "answers"                => [
                "answers" => [
                    [
                        'answer' => 'a',
                        'order' => 1
                    ],
                    [
                        'answer' => 'b',
                        'order' => 2
                    ],
                    [
                        'answer' => 'c',
                        'order' => 3
                    ]
                ],
                "subQuestions" => [
                    [
                        'sub_question' => 'antwoord zou a moeten zijn',
                        'order' => 1,
                        'score' => 2,
                    ],
                    [
                        'sub_question' => 'vraag nummer 2 (antwoord c)',
                        'order' => 2,
                        'score' => 1,
                    ],
                    [
                        'sub_question' => 'vraag nummer 3 (antwoord b)',
                        'order' => 3,
                        'score' => 2,
                    ],
                    [
                        'sub_question' => 'vraag nummer 4 (antwoord c)',
                        'order' => 4,
                        'score' => 1,
                    ]
                ],
            ],
            "maintain_position"      => "0",
            "subtype"                => "SingleQuestion",
            "discuss"                => "1",
            "decimal_score"          => "0",
            "add_to_database"        => "1",
            "attainments"            => [],
            "note_type"              => "NONE",
            "is_open_source_content" => 0,
            "tags"                   => [],
            "rtti"                   => "null",
            "test_id"                => "1"
        ]));

        // is not correct should be 201, 200 should be reserved for requests that are successful but did not persist new data.
        $this->assertResponseStatus(200);

        $response = json_decode($this->response->getContent());

        //   $this->assertEquals(3, $response->order);
        $this->assertEquals('1', $response->test_id);
        $this->assertEquals('1', $response->discuss);

        $magicId = $response->id;

        $this->get(
            sprintf(
                '/test_question/%d?user=%s',
                $magicId,
                static::USER_TEACHER
            )
        );


        $response = json_decode($this->response->getContent());

        $question = $response->question;
        $this->assertEquals('MatrixQuestion', $question->type);

        $author = $question->authors[0];
        $this->assertEquals(static::USER_TEACHER, $author->username);

    }
}
