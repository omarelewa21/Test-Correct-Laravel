<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:04
 */

namespace tcCore\Traits\Dev;

use tcCore\Test;

trait TestTrait
{
    private function getTestAttributes(array $overrides = []): array
    {
        return array_merge([
            'name'                   => 'Test Title',
            'abbreviation'           => 'TT',
            'test_kind_id'           => '3',
            'subject_id'             => '1',
            'education_level_id'     => '1',
            'education_level_year'   => '1',
            'period_id'              => '1',
            'shuffle'                => '0',
            'is_open_source_content' => '1',
            'introduction'           => 'Hello this is the intro txt',
            "school_classes"         => ["1"],
            'subject_id'             => '1',
        ], $overrides);
    }

    private function addTestAndReturnTestId(array $overrides = []): int
    {
        $response = $this->post(
            'api-c/test',
            static::getTeacherOneAuthRequestData(
                $this->getTestAttributes()
            )
        );

        $response->assertStatus(200);

        return $response->decodeResponseJson()['id'];
    }

    private function reorderQuestion($attributes,$question)
    {
        $response = $this->put(
            sprintf('api-c/test_question/%s/reorder', $question->uuid),
            static::getTeacherOneAuthRequestData($attributes)
        );
        $response->assertStatus(200);

        return $response->decodeResponseJson()['id'];
    }

    private function getTestQuestionsByGet($attributes){
        $response = $this->get(static::authTeacherOneGetRequest('/api-c/test_question', $attributes));
        $response->assertStatus(200);

        return $response->decodeResponseJson();
    }

    private function duplicateTest($testId){
        $test = Test::find($testId);
        $response = $this->post(
            '/api-c/test/'.$test->uuid.'/duplicate',
            static::getTeacherOneAuthRequestData(
                ['status'=>0]
            )
        );
        $response->assertStatus(200);
        $testId = $response->decodeResponseJson()['id'];
        $this->copyTestId = $testId;
    }

    private function createTLCTest($attributes){
        $response = $this->post(
            'api-c/test',
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
        $testId = $response->decodeResponseJson()['id'];
        $this->originalTestId = $testId;
    }

    private function originalAndCopyShareQuestion(){
        $result = $this->compareOriginalAndCopyQuestion();
        $this->assertTrue(count($result)==0);
    }

    private function originalAndCopyDifferFromQuestion(){
        $result = $this->compareOriginalAndCopyQuestion();
        $this->assertTrue(count($result)>0);
    }

    private function originalAndCopyShareGroupQuestion($var = false){
        if($var){
            dump($var);
        }
        $result = $this->compareOriginalAndCopyGroupQuestion();
        $this->assertTrue(count($result)==0);
    }

    private function originalAndCopyDifferFromGroupQuestion($var = false){
        if($var){
            dump($var);
        }
        $result = $this->compareOriginalAndCopyGroupQuestion();
        $this->assertTrue(count($result)>0);
    }

    private function compareOriginalAndCopyGroupQuestion()
    {
        $testQuestions = Test::find($this->originalTestId)->testQuestions;
        $originalQuestionArray = $this->extractQuestionIdsFromGroupQuestion($testQuestions);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $this->extractQuestionIdsFromGroupQuestion($copyQuestions);
        return array_diff($originalQuestionArray, $copyQuestionArray);
    }

    private function originalAndCopyShareGroup($var = false){
        if($var){
            dump($var);
        }
        $testQuestions = Test::find($this->originalTestId)->testQuestions;
        $originalQuestionArray = $testQuestions->pluck('question_id')->toArray();
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
    }

    private function extractQuestionIdsFromGroupQuestion($testQuestions){
        if(is_null($testQuestions)){
            return [];
        }
        $groupTestQuestions = $testQuestions->filter(function ($testQuestion, $key) {
                                                    return $testQuestion->question->type == 'GroupQuestion';
                                                });
        $groupQuestions = $groupTestQuestions->map(function ($groupTestQuestion, $key) {
                                                return $groupTestQuestion->question;
                                            });
        $questionArray = [];
        foreach ($groupQuestions as $key => $groupQuestion) {
            foreach ($groupQuestion->groupQuestionQuestions as $key => $groupQuestionQuestion) {
                $questionArray[] = $groupQuestionQuestion->question_id;
            }
        }
        return $questionArray;
    }

    /**
     * @return array
     */
    private function compareOriginalAndCopyQuestion(): array
    {
        $questions = Test::find($this->originalTestId)->testQuestions;
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        return $result;
    }
}