<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use tcCore\Test;
use tcCore\Question;
use tcCore\MatchingQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\MultipleChoiceQuestionTrait;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\MatchingQuestionTrait;
use Illuminate\Support\Facades\DB;

class CopyCombineerTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use MatchingQuestionTrait;
    use MultipleChoiceQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;


    /** @test */
    public function it_should_copy_questionsForCombineer()
    {
        $this->setupScenario10();
        $tests = Test::where('name','TToets van GM10')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM10')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $attributes = $this->getAttributesForEditCopyQuestion10($this->originalTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editMatchingQuestion($copyQuestion->uuid,$attributes);
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $this->assertTrue(count($result)>=0);
    }

    /** @test */
    public function it_should_copy_questionsForCombineerAndKeepTheOriginalOriginal()
    {
        $this->setupScenario10();
        $tests = Test::where('name','TToets van GM10')->get();
        $this->assertTrue(count($tests)==1);

        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->matchingQuestionAnswers));

        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM10')->get();
        $this->assertTrue(count($tests)==1);

        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);

        $attributes = $this->getAttributesForEditCopyQuestion10($this->originalTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editMatchingQuestion($copyQuestion->uuid,$attributes);
        $this->checkCopyQuestionsAfterEdit($this->copyTestId,$originalQuestionArray);
        $originalQuestions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($originalQuestions)==1);

        $testQuestion = $originalQuestions->first();
        $answers = $testQuestion->question->matchingQuestionAnswers;
        foreach ($answers as $key => $answerObj) {
        	switch ($key) {
        		case '0':
        			$this->assertEquals('aa', $answerObj->answer);
        			break;
        		case '1':
        			$this->assertEquals('bb', $answerObj->answer);
        			break;
        		case '2':
        			$this->assertEquals('cc', $answerObj->answer);
        			break;
        		case '3':
        			$this->assertEquals('dd', $answerObj->answer);
        			break;
        		case '4':
        			$this->assertEquals('ee', $answerObj->answer);
        			break;
        		case '5':
        			$this->assertEquals('ff', $answerObj->answer);
        			break;
        	}
        }
    }

    /** @test */
    public function it_should_not_copy_questionsForCombineerWhenAntwoordOptiesAreTheSame()
    {
        $this->setupScenario10();
        $tests = Test::where('name','TToets van GM10')->get();
        $this->assertTrue(count($tests)==1);

        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->matchingQuestionAnswers));

        $originalQuestionArray = $questions->pluck('question_id')->toArray();

        $tests = Test::where('name','Kopie #1 TToets van GM10')->get();
        $this->assertTrue(count($tests)==1);

        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);

        $attributes = $this->getAttributesForQuestion10($this->originalTestId);
        unset($attributes["test_id"]);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        
        $this->editMatchingQuestion($copyQuestion->uuid,$attributes);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();

        $result = array_diff($originalQuestionArray, $copyQuestionArray);

        $this->assertTrue(count($result)==0);

        
    }

    /** @test */
    public function it_should_copy_questionsForCombineerAndCopyTheOriginalOriginalAfterEdit()
    {
        $this->setupScenario10();
        $tests = Test::where('name','TToets van GM10')->get();
        $this->assertTrue(count($tests)==1);

        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->matchingQuestionAnswers));
        $originalQuestion = $questions->first->question();
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM10')->get();
        $this->assertTrue(count($tests)==1);

        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);

        $attributes = $this->getAttributesForEditCopyQuestion10($this->originalTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        
        $this->editMatchingQuestion($copyQuestion->uuid,$attributes);
        $this->checkCopyQuestionsAfterEdit($this->copyTestId,$originalQuestionArray);

        $originalQuestionsAfterCopyEdit = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($originalQuestionsAfterCopyEdit)==1);
        $this->assertEquals($originalQuestion->id, $originalQuestionsAfterCopyEdit->first()->id);
        $attributes = $this->getAttributesForEditOriginalQuestion10($this->originalTestId);
        $this->editMatchingQuestion($originalQuestion->uuid,$attributes);
        $originalQuestionsAfterOriginalEdit = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($originalQuestionsAfterCopyEdit)==1);
        $this->assertNotEquals($originalQuestionsAfterOriginalEdit->first()->question->id, $originalQuestion->id);
    }



   

    private function setupScenario10(){
    	$attributes = $this->getAttributesForTest10();
    	unset($attributes['school_classes']);
    	$this->createTLCTest($attributes);
    	$attributes = $this->getAttributesForQuestion10($this->originalTestId);
        $this->createMatchingQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
    }


    private function getAttributesForTest10(){

        return $this->getTestAttributes([
                    "name"=> "TToets van GM10",
                    "abbreviation"=> "TTGM10",
                    "test_kind_id"=> "3",
                    "subject_id"=> "6",
                    "education_level_id"=> "1",
                    "education_level_year"=> "1",
                    "period_id"=> "1",
                    "shuffle"=> "0",
                    "is_open_source_content"=> "1",
                    "introduction"=> "intro",
                ]);
    }

    

    private function getAttributesForEditCopyQuestion10($testId){
    	$attributes = array_merge($this->getAttributesForQuestion10($testId),[	
    																		"answers"=> array_merge([
                                                                                                        [
                                                                                                        'order'=> '0',
                                                                                                        'left'=> 'aa2',
                                                                                                        'right'=> 'bb2'
                                                                                                        ],
                                                                                                        [
                                                                                                        'order'=> '1',
                                                                                                        'left'=> 'cc2',
                                                                                                        'right'=> 'dd2'
                                                                                                        ],
                                                                                                        [
                                                                                                        'order'=> '2',
                                                                                                        'left'=> 'ee2',
                                                                                                        'right'=> 'ff2'
                                                                                                        ]
                                                                                                    ],$this->getRestOfAnswerArray(3,49))

																				
																			,

    																	]);
		unset($attributes["test_id"]);
		return $attributes;
    }

    private function getAttributesForEditOriginalQuestion10($testId){
        $attributes = array_merge($this->getAttributesForQuestion10($testId),[  
                                                                            "answers"=> array_merge([
                                                                                                        [
                                                                                                        'order'=> '0',
                                                                                                        'left'=> 'aa4',
                                                                                                        'right'=> 'bb4'
                                                                                                        ],
                                                                                                        [
                                                                                                        'order'=> '1',
                                                                                                        'left'=> 'cc4',
                                                                                                        'right'=> 'dd4'
                                                                                                        ],
                                                                                                        [
                                                                                                        'order'=> '2',
                                                                                                        'left'=> 'ee4',
                                                                                                        'right'=> 'ff4'
                                                                                                        ]
                                                                                                    ],$this->getRestOfAnswerArray(3,49))
                                                                            ,

                                                                        ]);
        unset($attributes["test_id"]);
        return $attributes;
    }

    private function getAttributesForQuestion10($testId){
    	return [	
    				'type'=> 'MatchingQuestion',
                    'score'=> '5',
                    'question'=> '<p>intro</p> ',
                    'order'=> 0,
                    'maintain_position'=> '0',
                    'discuss'=> '1',
                    'subtype'=> 'Matching',
                    'decimal_score'=> '0',
                    'add_to_database'=> 1,
                    'attainments'=> [
                    ],
                    'note_type'=> 'NONE',
                    'is_open_source_content'=> 1,
					"answers"=> array_merge([
                                                [
                                                'order'=> '1',
                                                'left'=> 'aa',
                                                'right'=> 'bb'
                                                ],
                                                [
                                                'order'=> '2',
                                                'left'=> 'cc',
                                                'right'=> 'dd'
                                                ],
                                                [
                                                'order'=> '3',
                                                'left'=> 'ee',
                                                'right'=> 'ff'
                                                ]
                                            ],$this->getRestOfAnswerArray(3,49))
					,
					'tags'=> [
                    ],
                    'rtti'=> null,
                    'bloom'=> null,
                    'miller'=> null,
                    'test_id'=> $testId,
                    'session_hash'=> 'FE9rzeTbhOWPs4XUyeg48Z6QgIXaaGTYnChLTRZWvY9E218GZgCYpAXezrNbYDJrzL9e437MJlksLKu9eD0591486',
                    'user'=> 'd1@test-correct.nl',
                    'closeable'=> 0,
				];
    }

    private function getScenario10GetAttributes(){
    	return $this->getGetAttributes($this->originalTestId);
    }
    
    private function getGetAttributes($testId){
    	return [
			"filter"=> [
						"test_id"=> $testId
						],
			"mode"=> "all",
			"order"=> [
						"order"=> "asc"
						]
		];
    }

    private function checkCopyQuestionsAfterEdit($copyTestId,$originalQuestionArray){
		$copyQuestions = Test::find($copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);

        $this->assertTrue(count($result)>0);

        $copyQuestion = Test::find($copyTestId)->testQuestions->first();
        $answers = $copyQuestion->question->matchingQuestionAnswers;
        foreach ($answers as $key => $answerObj) {
        	switch ($key) {
        		case '0':
        			$this->assertEquals('aa2', $answerObj->answer);
        			break;
        		case '1':
        			$this->assertEquals('bb2', $answerObj->answer);
        			break;
        		case '2':
        			$this->assertEquals('cc2', $answerObj->answer);
        			break;
        		case '3':
        			$this->assertEquals('dd2', $answerObj->answer);
        			break;
        		case '4':
        			$this->assertEquals('ee2', $answerObj->answer);
        			break;
        		case '5':
        			$this->assertEquals('ff2', $answerObj->answer);
        			break;
        	}
        }
    }
}



//     
// scenario 10:
//  inloggen d1@test-correct.nl
//  Itembank->Schoollocatie->Toets construeren
//  Toets:  Titel: Toets van GM6
//          Afkorting: TGM6
//          Introductie tekst: intro
//          Rest: default
//          Rubriceer vraag aanmaken: default velden
//          Toets dupliceren
//          rubriceer vraag aanpassen: Antwoord tekstueel veranderen
//          originele vraag openen antwoorden tekstueel aanpassen
//  Resultaat: vraag id originele toets


