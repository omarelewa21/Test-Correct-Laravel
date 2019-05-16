<?php

class MatchingQuestionTest extends TestCase {

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

	/**
	 * A basic functional test example.
     * @test
	 *
	 * @return void
	 */
	public function should_add_valid_matchingQuestion()
	{

	    $answers = [
            (object) ["order" => "1","left" => "al","right" => "ar"],
            (object) ["order" => "2","left"=>"bl","right"=>"br"],
            (object) ["order" => "3","left" => "cl","right" => "cr"],
            (object) ["order" => "4" ,"left"=>"dl","right"=>"dr"],
            (object) ["order" => "5","left" => "el","right" => "er"],
            (object) ["order" => "6","left" => "fl","right" => "fr"],
            (object) ["order" => "7","left" => "gl","right" => "gr"],
            (object) ["order" => "7","left" => "","right" => ""],
            (object) ["order" => "8","left" => "","right" => ""],
            (object) ["order" => "9","left" => "","right" => ""],
            (object) ["order" => "10","left" =>"","right" => ""],
            (object) ["order" => "11","left" => "","right" => ""],
            (object) ["order" => "12","left" => "","right" => ""],
            (object) ["order" => "13","left" => "","right" => ""],
            (object) ["order" => "14","left" => "","right" => ""],
            (object) ["order" => "15","left" => "","right" => ""],
        ];

        $this->post('/test_question', static::getAuthRequestData([
            "order" => 0,
            "score" => "5",
            "discuss"=>"1",
            "maintain_position" => "0",
            "decimal_score" => "0",
            "add_to_database" => "1",
            "note_type" => "NONE",
            "question" => "<p>ee<\/p>\r\n",
            "answers" => $answers,
            "attainments"=>[],
            "rtti" => "null",
            "tags" => [],
            "is_open_source_content" => 0,
            "test_id" => 1209,
            "type" => "MatchingQuestion",
        ]));

        // is not correct should be 201, 200 should be reserved for requests that are successful but did not persist new data.
        $this->assertResponseStatus(200);

        $response = json_decode($this->response->getContent());


     //   $this->assertEquals(3, $response->order);
        $this->assertEquals('1209', $response->test_id);
        $this->assertEquals('MatchingQuestion', $response->question->type);

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
        $this->assertEquals('MatchingQuestion', $question->type);

        $questionAnswers = $question->matching_question_answers;
        foreach($answers as $vlgnr => $answer){
            $leftNr = 2*$vlgnr;
            $rightNr = $leftNr+1;
            if($answer->left && $answer->right) {
                $this->assertEquals($questionAnswers[$leftNr]->answer, $answer->left);
                $this->assertEquals($questionAnswers[$rightNr]->answer, $answer->right);
            }
        }

        $this->assertTrue(!isset($questionAnswers[$rightNr+1]));

        $author = $question->authors[0];
        $this->assertEquals(static::USER_TEACHER, $author->username);

	}

    /**
     * A basic functional test example.
     * @test
     *
     * @return void
     */
    public function should_reorder_correct_on_update()
    {

        $answers = $this->getAnswers();

        $this->post('/test_question', static::getAuthRequestData([
            "order" => 0,
            "score" => "5",
            "discuss"=>"1",
            "maintain_position" => "0",
            "decimal_score" => "0",
            "add_to_database" => "1",
            "note_type" => "NONE",
            "question" => "<p>ee<\/p>\r\n",
            "answers" => $answers,
            "attainments"=>[],
            "rtti" => "null",
            "tags" => [],
            "is_open_source_content" => 0,
            "test_id" => 1209,
            "type" => "MatchingQuestion",
        ]));

        // is not correct should be 201, 200 should be reserved for requests that are successful but did not persist new data.
        $this->assertResponseStatus(200);

        $response = json_decode($this->response->getContent());

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
        $this->assertEquals('MatchingQuestion', $question->type);

        $questionAnswers = $question->matching_question_answers;
        foreach($answers as $vlgnr => $answer){
            $leftNr = 2*$vlgnr;
            $rightNr = $leftNr+1;
            if($answer->left && $answer->right) {
                $this->assertEquals($questionAnswers[$leftNr]->answer, $answer->left);
                $this->assertEquals($questionAnswers[$rightNr]->answer, $answer->right);
            }
        }

        $this->assertTrue(!isset($questionAnswers[$rightNr+1]));

        // reorder by resending the list in reverse order
        $this->put('/test_question/'.$magicId, static::getAuthRequestData([
            "order" => 0,
            "score" => "5",
            "discuss"=>"1",
            "maintain_position" => "0",
            "decimal_score" => "0",
            "add_to_database" => "1",
            "note_type" => "NONE",
            "question" => "<p>ee<\/p>\r\n",
            "answers" => $this->getReversedOrderAnswers(),
            "attainments"=>[],
            "rtti" => "null",
            "tags" => [],
            "is_open_source_content" => 0,
            "test_id" => 1209,
            "type" => "MatchingQuestion",
        ]));

        // is not correct should be 201, 200 should be reserved for requests that are successful but did not persist new data.
        $this->assertResponseStatus(200);

        $this->get(
            sprintf(
                '/test_question/%d?user=%s',
                $magicId,
                static::USER_TEACHER
            )
        );


        $response = json_decode($this->response->getContent());

        $question = $response->question;

        $questionAnswers = $question->matching_question_answers;
        foreach($this->getReversedOrderAnswers() as $vlgnr => $answer){
            $leftNr = 2*$vlgnr;
            $rightNr = $leftNr+1;
            if($answer->left && $answer->right) {
                $this->assertEquals($questionAnswers[$leftNr]->answer, $answer->left);
                $this->assertEquals($questionAnswers[$rightNr]->answer, $answer->right);
            }
        }

        $this->assertTrue(!isset($questionAnswers[$rightNr+1]));

    }

    protected function getAnswers(){
        return [
            (object) ["order" => "1","left" => "al","right" => "ar"],
            (object) ["order" => "2","left"=>"bl","right"=>"br"],
            (object) ["order" => "3","left" => "cl","right" => "cr"],
            (object) ["order" => "4" ,"left"=>"dl","right"=>"dr"],
            (object) ["order" => "5","left" => "el","right" => "er"],
            (object) ["order" => "6","left" => "fl","right" => "fr"],
            (object) ["order" => "7","left" => "gl","right" => "gr"],
            (object) ["order" => "7","left" => "","right" => ""],
            (object) ["order" => "8","left" => "","right" => ""],
            (object) ["order" => "9","left" => "","right" => ""],
            (object) ["order" => "10","left" =>"","right" => ""],
            (object) ["order" => "11","left" => "","right" => ""],
            (object) ["order" => "12","left" => "","right" => ""],
            (object) ["order" => "13","left" => "","right" => ""],
            (object) ["order" => "14","left" => "","right" => ""],
            (object) ["order" => "15","left" => "","right" => ""],
        ];
    }

    protected function getReversedOrderAnswers(){
        return [
            (object) ["order" => "1","left" => "gl","right" => "gr"],
            (object) ["order" => "2","left" => "fl","right" => "fr"],
            (object) ["order" => "3","left" => "el","right" => "er"],
            (object) ["order" => "4" ,"left"=>"dl","right"=>"dr"],
            (object) ["order" => "5","left" => "cl","right" => "cr"],
            (object) ["order" => "6","left"=>"bl","right"=>"br"],
            (object) ["order" => "7","left" => "al","right" => "ar"],
            (object) ["order" => "7","left" => "","right" => ""],
            (object) ["order" => "8","left" => "","right" => ""],
            (object) ["order" => "9","left" => "","right" => ""],
            (object) ["order" => "10","left" =>"","right" => ""],
            (object) ["order" => "11","left" => "","right" => ""],
            (object) ["order" => "12","left" => "","right" => ""],
            (object) ["order" => "13","left" => "","right" => ""],
            (object) ["order" => "14","left" => "","right" => ""],
            (object) ["order" => "15","left" => "","right" => ""],
        ];

    }

}
