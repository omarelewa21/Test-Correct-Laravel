<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use tcCore\Test;
use tcCore\Question;
use tcCore\MulipleChoiceQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestTrait;
use Tests\Traits\MultipleChoiceQuestionTrait;
use Illuminate\Support\Facades\DB;

class MulipleChoiceQuestionTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use MultipleChoiceQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;

     /** @test */
     public function can_create_test_and_mc_question(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForQuestion7($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->multipleChoiceQuestionAnswers));
     }

     /** @test */
     public function can_create_test_and_mc_question_with_answers(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForQuestion7($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->multipleChoiceQuestionAnswers));
        $this->assertEquals(3, count($questions->first()->question->multipleChoiceQuestionAnswers));
     }


     /** @test */
     public function can_edit_answers_mc_question(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForQuestion7($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $question = Test::find($this->originalTestId)->testQuestions->first();
        $attributes = $this->getAttributesForEditQuestion7($this->originalTestId);
        $this->editMultipleChoiceQuestion($question->uuid,$attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $number = count($questions->first()->question->multipleChoiceQuestionAnswers);
        $this->assertEquals(3,$number);
        $answer = $questions->first()->question->multipleChoiceQuestionAnswers->first();
        $this->assertEquals('aa', $answer->answer);
     }

     /** @test */
     public function can_create_test_and_mc_arq_question(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForARQQuestion($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->multipleChoiceQuestionAnswers));
     }

     /** @test */
     public function can_create_test_and_mc_arq_question_with_answers(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForARQQuestion($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->multipleChoiceQuestionAnswers));
        $this->assertEquals(5, count($questions->first()->question->multipleChoiceQuestionAnswers));
     }

     /** @test */
     public function can_create_test_and_mc_arq_question_with_answers_containing_zero_value(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForARQQuestionWithZeroAnswer($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->multipleChoiceQuestionAnswers));
        $this->assertEquals(5, count($questions->first()->question->multipleChoiceQuestionAnswers));
     }
   
    /** @test */
    public function it_should_not_copy_questions_for_mc_if_nothing_is_changed()
    {
        $this->setupScenario7();
        $tests = Test::where('name','TToets van GM7')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM7')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $attributes = $this->getAttributesForQuestion7($this->copyTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editMultipleChoiceQuestion($copyQuestion->uuid,$attributes);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
    }   
    

    private function setupScenario7(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForQuestion7($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
    }

    private function getAttributesForTest7(){

        return $this->getTestAttributes([
            'name'                   => 'TToets van GM7',
            'abbreviation'           => 'TTGM7',
            'subject_id'             => '6',
            'introduction'           => 'intro',
        ]);
    
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


    private function getAttributesForEditQuestion7($testId){
        $attributes = array_merge($this->getAttributesForQuestion7($testId),[   "answers"=> array_merge([
                                                                                                            [
                                                                                                            "order"=> "1",
                                                                                                            "answer"=> "aa",
                                                                                                            "score"=> "10"
                                                                                                            ],
                                                                                                            [
                                                                                                            "order"=> "2",
                                                                                                            "answer"=> "bb",
                                                                                                            "score"=> "0"
                                                                                                            ],
                                                                                                            [
                                                                                                            "order"=> "3",
                                                                                                            "answer"=> "cc",
                                                                                                            "score"=> "0"
                                                                                                            ]
                                                                                                        ],$this->getRestOfAnswerArray(3,10)),
                                                                            ]);
        unset($attributes["test_id"]);
        return $attributes;
    }

    private function getAttributesForQuestion7($testId){
        return [    
                    "type"=> "MultipleChoiceQuestion",
                    "score"=> "5",
                    "question"=> "<p>GM7</p> ",
                    "order"=> 0,
                    "maintain_position"=> "0",
                    "discuss"=> "1",
                    "subtype"=> "Classify",
                    "decimal_score"=> "0",
                    "add_to_database"=> 1,
                    "attainments"=> [
                    ],
                    "note_type"=> "NONE",
                    "is_open_source_content"=> 1,
                    "answers"=> array_merge([
                                [
                                    "order"=> "1",
                                    "answer"=> "a",
                                    "score"=> "10"
                                    ],
                                    [
                                    "order"=> "2",
                                    "answer"=> "b",
                                    "score"=> "0"
                                    ],
                                    [
                                    "order"=> "3",
                                    "answer"=> "c",
                                    "score"=> "0"
                                    ]
                                ],$this->getRestOfAnswerArray(3,10)),
                    "tags"=> [
                    ],
                    "rtti"=> "R",
                    "bloom"=> "Onthouden",
                    "miller"=> "Weten",
                    "test_id"=> $testId,
                ];
    }

    private function getAttributesForARQQuestion($testId){
        return [    
                    "type"=> "MultipleChoiceQuestion",
                    "score"=> "150",
                    "question"=> "<p>GM7</p> ",
                    "order"=> 0,
                    "maintain_position"=> "0",
                    "discuss"=> "1",
                    "subtype"=> "ARQ",
                    "decimal_score"=> "0",
                    "add_to_database"=> 1,
                    "attainments"=> [
                    ],
                    "note_type"=> "NONE",
                    "is_open_source_content"=> 1,
                    "answers"=> [
                                    [
                                    "score"=> "10"
                                    ],
                                    [
                                    "score"=> "20"
                                    ],
                                    [
                                    "score"=> "30"
                                    ],
                                    [
                                    "score"=> "40"
                                    ],
                                    [
                                    "score"=> "50"
                                    ]
                                ],
                    "tags"=> [
                    ],
                    "rtti"=> "R",
                    "bloom"=> "Onthouden",
                    "miller"=> "Weten",
                    "test_id"=> $testId,
                ];
    }

    private function getAttributesForARQQuestionWithZeroAnswer($testId){
        return [    
                    "type"=> "MultipleChoiceQuestion",
                    "score"=> "150",
                    "question"=> "<p>GM7</p> ",
                    "order"=> 0,
                    "maintain_position"=> "0",
                    "discuss"=> "1",
                    "subtype"=> "ARQ",
                    "decimal_score"=> "0",
                    "add_to_database"=> 1,
                    "attainments"=> [
                    ],
                    "note_type"=> "NONE",
                    "is_open_source_content"=> 1,
                    "answers"=> [
                                    [
                                    "score"=> "10"
                                    ],
                                    [
                                    "score"=> "20"
                                    ],
                                    [
                                    "score"=> "30"
                                    ],
                                    [
                                    "score"=> "40"
                                    ],
                                    [
                                    "score"=> "0"
                                    ]
                                ],
                    "tags"=> [
                    ],
                    "rtti"=> "R",
                    "bloom"=> "Onthouden",
                    "miller"=> "Weten",
                    "test_id"=> $testId,
                ];
    }

    private function getScenario5GetAttributes(){
        return $this->getGetAttributes($this->originalTestId);
    }
    

    private function checkCopyQuestionsAfterEdit($copyTestId,$originalQuestionArray){
		$copyQuestions = Test::find($copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);

        $this->assertTrue(count($result)>0);

        $copyQuestion = Test::find($copyTestId)->testQuestions->first();
        $answers = $copyQuestion->question->multipleChoiceQuestionAnswers;
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
        	}
        }
    }
}


// scenario 1:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM
// 			Afkorting: TGM
// 			Introductie tekst: intro
// 			Rest: default
// 			Meerkeuze vraag aanmaken: default velden
// 			Vraaggroep aanmaken:default velden
// 			juist/onjuist vraag aanmaken
// 			Toets dupliceren
// 			juist/onjuist vraag aanpassen
// 	Resultaat: vraag originele toets niet gewijzigd
// 			Meerkeuze vraag antwoorden tekstueel aanpassen
// 	Resultaat: vraag originele toets niet gewijzigd
// 	Carlo: 


// scenario 2:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM2
// 			Afkorting: TGM2
// 			Introductie tekst: intro
// 			Rest: default
// 			Meerkeuze vraag aanmaken: default velden
// 			Vraaggroep aanmaken:vastzetten true
// 			juist/onjuist vraag aanmaken
// 			Toets dupliceren
// 			juist/onjuist vraag aanpassen
// 	Resultaat: vraag originele toets niet gewijzigd

// scenario 3:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM3
// 			Afkorting: TGM3
// 			Introductie tekst: intro
// 			Rest: default
// 			Meerkeuze vraag aanmaken: default velden
// 			Vraaggroep aanmaken:default
// 			juist/onjuist vraag aanmaken: 	RTTI: R
// 											Bloom: Onthouden
// 											Miller: Weten
// 											Antwoord: juist
// 			Toets dupliceren
// 			juist/onjuist vraag aanpassen: Antwoord: onjuist
// 	Resultaat: vraag originele toets niet gewijzigd

// scenario 4:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM4
// 			Afkorting: TGM4
// 			Introductie tekst: intro
// 			Rest: default
// 			Rubriceer vraag aanmaken: RTTI: R
// 											Bloom: Onthouden
// 											Miller: Weten
			
// 			Toets dupliceren
// 			rubriceer vraag aanpassen: Antwoord tekstueel veranderen
// 	Resultaat: vraag originele toets gewijzigd

// scenario 5:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM5
// 			Afkorting: TGM5
// 			Introductie tekst: intro
// 			Rest: default
// 			Meerkeuze vraag aanmaken: RTTI: R
// 											Bloom: Onthouden
// 											Miller: Weten
			
// 			Toets dupliceren
// 			meerkeuze vraag aanpassen: Antwoord tekstueel veranderen
// 	Resultaat: vraag originele toets niet gewijzigd

// scenario 6:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM6
// 			Afkorting: TGM6
// 			Introductie tekst: intro
// 			Rest: default
// 			Rubriceer vraag aanmaken: default velden
// 			Toets dupliceren
// 			rubriceer vraag aanpassen: Antwoord tekstueel veranderen
// 	Resultaat: vraag originele toets gewijzigd
// 	conclusie: onafhankelijk van taxonomie

// scenario 7:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM7
// 			Afkorting: TGM7
// 			Introductie tekst: intro
// 			Rest: default
// 			Rangschik vraag aanmaken: default velden
// 			Toets dupliceren
// 			Rangschik vraag aanpassen: Antwoord tekstueel veranderen
// 	Resultaat: vraag originele toets gewijzigd

// scenario 8:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM8
// 			Afkorting: TGM8
// 			Introductie tekst: intro
// 			Rest: default
// 			Combinatie vraag aanmaken: default velden
// 			Toets dupliceren
// 			Combinatie vraag aanpassen: Antwoord tekstueel veranderen
// 	Resultaat: vraag originele toets gewijzigd

// scenario 9:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM9
// 			Afkorting: TGM9
// 			Introductie tekst: intro
// 			Rest: default
// 			Combinatie vraag aanmaken: 	RTTI: R
// 										Bloom: Onthouden
// 										Miller: Weten
// 			Toets dupliceren
// 			Combinatie vraag aanpassen: Antwoord tekstueel veranderen
// 										Antwoord optie toevoegen
// 	Resultaat: vraag originele toets gewijzigd: andere antwoorden, antwoord optie toegevoegd
// 			Combinatie vraag aanpassen: Naam tekstueel veranderen
// 	Resultaat: vraag originele toets: naam niet gewijzigd

