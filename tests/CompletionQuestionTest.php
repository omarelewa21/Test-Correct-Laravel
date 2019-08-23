<?php
namespace Tests;

class CompletionQuestionTest extends TestCase {

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

	/**
	 * A basic functional test example.
     * @test
	 *
	 * @return void
	 */
	public function should_add_valid_completionQuestion()
	{

        $this->post('/test_question', static::getAuthRequestData([
            "question"               => "<p>aaa [1] aaa</p>\r\n",
            "type"                   => "CompletionQuestion",
            "score"                  => "5",
            "order"                  => 0,
            "answers"                => [
                "1" => "bbb"
            ],
            "maintain_position"      => "0",
            "subtype"                => "completion",
            "discuss"                => "1",
            "decimal_score"          => "0",
            "add_to_database"        => "1",
            "attainments"            => [],
            "note_type"              => "NONE",
            "is_open_source_content" => 0,
            "tags"                   => [],
            "rtti"                   => "null",
            "test_id"                => "1166"
        ]));

        // is not correct should be 201, 200 should be reserved for requests that are successful but did not persist new data.
        $this->assertResponseStatus(200);

        $response = json_decode($this->response->getContent());

        //   $this->assertEquals(3, $response->order);
        $this->assertEquals('1166', $response->test_id);
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
        $this->assertEquals('CompletionQuestion', $question->type);

        $author = $question->authors[0];
        $this->assertEquals(static::USER_TEACHER, $author->username);


        $this->delete(
            sprintf(
                '/test_question/%d/completion_question_answer?user=%s',
                $magicId,
                static::USER_TEACHER
            )
        );

        $response = json_decode($this->response->getContent());
        $this->assertEquals([], $response);
//        dd($response);

//        $response->


    }
}
