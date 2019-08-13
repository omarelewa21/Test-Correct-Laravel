<?php
namespace Tests;


class ExampleTest extends TestCase
{

    /**
     * A basic functional test example.
     *
     * @return void
     */
//    use \Illuminate\Foundation\Testing\DatabaseTransactions;
    public function testBasicExample()
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

//    /** @test */
//    public function when_logged_in_as_pvries31_get_the_teacher_analysis_report_for_user_910_should_not_give_a_error()
//    {
//        $uri = 'http://test-correct.test/user/910?session_hash=dmG7qGQuTExW2MWQ8dk9L9V6Zui7NzUy2EWmNJkoh7qMhSJZFYSPsJgKgqQjkOZIqqeRatXKlRSPNn4D7pyre910&signature=7981868a5c4c07ec5fefeb1ba0e6ef4e10fa7401fb1e533390164ccedd9d0e9f&user=p.vries%4031.com&with%5B0%5D=teacherCom';
//
//        $attr = [
//            'with'         => [
//                'teacherComparison',
//                'teacherSchoolClassAverages'
//            ],
//            'session_hash' => 'dmG7qGQuTExW2MWQ8dk9L9V6Zui7NzUy2EWmNJkoh7qMhSJZFYSPsJgKgqQjkOZIqqeRatXKlRSPNn4D7pyre910',
//            'user'         => 'p.vries@31.com'
//        ];
//
//        $this->get(
//            $uri,
//            $attr
//        );
//        $this->assertTrue(true);
//
//        dd($this->response);
//    }
}
