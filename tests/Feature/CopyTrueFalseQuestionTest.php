<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use tcCore\Test;
use tcCore\Question;
use tcCore\RankingQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\MultipleChoiceQuestionTrait;
use Illuminate\Support\Facades\DB;

class CopyTrueFalseQuestionTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use MultipleChoiceQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;

   
    /** @test */
    public function it_should_copy_questionsForJuistOnjuist()
    {
        $this->setupScenario1();
        $tests = Test::where('name','TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $attributes = $this->getAttributesForEditQuestion1($this->copyTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editMultipleChoiceQuestion($copyQuestion->uuid,$attributes);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)>0);
    }

    /** @test */
    public function it_should_not_copy_questions_for_juist_onjuist_when_answers_are_the_same()
    {
        $this->setupScenario1();
        $tests = Test::where('name','TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $attributes = $this->getAttributesForQuestion1($this->copyTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editMultipleChoiceQuestion($copyQuestion->uuid,$attributes);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
    }
    

    private function setupScenario1(){
        $attributes = $this->getAttributesForTest1();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForQuestion1($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
    }


    private function getAttributesForTest1(){

        return $this->getTestAttributes([
            'name'                   => 'TToets van GM1',
            'abbreviation'           => 'TTGM1',
            'subject_id'             => '6',
            'introduction'           => 'intro',
        ]);
    
    }
        
    


    private function getAttributesForEditQuestion1($testId){
        $attributes = array_merge($this->getAttributesForQuestion1($testId),[   "answers"=> [
                                                                                                [
                                                                                                "answer"=> "Juist",
                                                                                                "score"=> 0,
                                                                                                "order"=> 0
                                                                                                ],
                                                                                                [
                                                                                                "answer"=> "Onjuist",
                                                                                                "score"=> "5",
                                                                                                "order"=> 0
                                                                                                ]
                                                                                            ],
                                                                            ]);
        unset($attributes["test_id"]);
        return $attributes;
    }

    private function getAttributesForQuestion1($testId){
        return [    
                    "type"=> "MultipleChoiceQuestion",
                    "score"=> "5",
                    "question"=> "<p>GM1</p> ",
                    "order"=> 0,
                    "maintain_position"=> "0",
                    "discuss"=> "1",
                    "subtype"=> "TrueFalse",
                    "decimal_score"=> "0",
                    "add_to_database"=> 1,
                    "attainments"=> [
                    ],
                    "note_type"=> "NONE",
                    "is_open_source_content"=> 1,
                    "answers"=> [
                                    [
                                    "answer"=> "Juist",
                                    "score"=> "5",
                                    "order"=> 0
                                    ],
                                    [
                                    "answer"=> "Onjuist",
                                    "score"=> 0,
                                    "order"=> 0
                                    ]
                                ],
                    "tags"=> [
                    ],
                    "rtti"=> "R",
                    "bloom"=> "Onthouden",
                    "miller"=> "Weten",
                    "test_id"=> $testId,
                    "closeable"=> 0,
                ];
    }


    

    private function checkCopyQuestionsAfterEdit($copyTestId,$originalQuestionArray){
		$copyQuestions = Test::find($copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);

        $this->assertTrue(count($result)>0);

        $copyQuestion = Test::find($copyTestId)->testQuestions->first();
        $answers = $copyQuestion->question->rankingQuestionAnswers;
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

