<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use tcCore\Question;
use tcCore\QuestionAuthor;
use tcCore\SchoolClass;
use tcCore\User;
use Tests\TestCase;


class DeleteUserWithRoleTeacherTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function removedAuthorShouldBeVisibleInQuestionsTest()
    {
        $this->setUpScenario1();
        $listData = [
            'sort' => '',
            'results' => 60,
            'page' => 1,
            'order' => ['id' => 'desc'],
        ];
        $response = $this->get(static::authTeacherTwoGetRequest('/api-c/question',$listData));
        $response->assertStatus(200);
        $questions = $response->decodeResponseJson()['data'];
        $questionsOfTeacherOne = QuestionAuthor::withTrashed()->where('user_id',1486)->pluck('question_id')->toArray();
        $pass = false;
        foreach($questions as $key=>$question){
            if(in_array($question['id'],$questionsOfTeacherOne)){
                $pass = true;
                $this->assertTrue(count($question['authors'])>0);
            }
        }
        $this->assertTrue($pass);
    }

    /** @test */
    public function removedAuthorShouldBeVisibleInQuestionInfoTest()
    {
        $this->setUpScenario1();
        $questionsOfTeacherOne = QuestionAuthor::withTrashed()->where('user_id',1486)->pluck('question_id')->toArray();
        $pass = false;
        foreach($questionsOfTeacherOne as $key=>$questionId){
            $question = Question::find($questionId);
            $listData = [];
            $response = $this->get(static::authTeacherTwoGetRequest('/api-c/question/'.$question->uuid,$listData));
            $response->assertStatus(200);
            $pass = true;
            $question = $response->decodeResponseJson();
            $this->assertTrue(count($question['authors'])>0);
        }
        $this->assertTrue($pass);
    }

    private function setUpScenario1()
    {
        $this->addClassToTeacher2();
        $this->removeTeacher1();

    }

    private function addClassToTeacher2()
    {
        $user = User::where('username',static::USER_TEACHER_TWO)->first();
        $class = SchoolClass::find(11);
        $teacherResponse = $this->post(
            'api-c/teacher',
            static::getSchoolBeheerderAuthRequestData([
                    "class_id"=> $class->uuid,
                    "user_id"=> $user->uuid,
                    "subject_id"=> "1",
                ]
            )
        );
        $teacherResponse->assertStatus(200);
        Auth::logout();
    }

    private function removeTeacher1()
    {
        $user = User::where('username',static::USER_TEACHER)->first();
        $teacherResponse = $this->delete(
            'api-c/user/'.$user->uuid,
            static::getSchoolBeheerderAuthRequestData([]
            )
        );
        $teacherResponse->assertStatus(200);
        Auth::logout();
    }
}


