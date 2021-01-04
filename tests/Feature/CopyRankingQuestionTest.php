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
use Tests\Traits\TestTrait;
use Tests\Traits\RankingQuestionTrait;
use Illuminate\Support\Facades\DB;

class CopyRankingQuestionTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use RankingQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;

    public function setUp(): void
    {
    	//$this->clearDB();
    	parent::setUp();
    }

    public function tearDown(): void
    {
    	//$this->clearDB();
    	
    	parent::tearDown();
    }

   
    /** @test */
    public function it_should_copy_questionsForRangschik1()
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
        $attributes = $this->getAttributesForEditQuestion7($this->originalTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editRankingQuestion($copyQuestion->uuid,$attributes);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)>0);
    }

    /** @test */
    public function it_should_copy_questionsForRangschikAndKeepTheOriginalOriginal()
    {
        $this->setupScenario7();
        $tests = Test::where('name','TToets van GM7')->get();
        $this->assertTrue(count($tests)==1);

        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->rankingQuestionAnswers));

        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM7')->get();
        $this->assertTrue(count($tests)==1);

        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);

        $attributes = $this->getAttributesForEditQuestion7($this->originalTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editRankingQuestion($copyQuestion->uuid,$attributes);
        $this->checkCopyQuestionsAfterEdit($this->copyTestId,$originalQuestionArray);

        $originalQuestions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($originalQuestions)==1);

        $testQuestion = $originalQuestions->first();
        $answers = $testQuestion->question->rankingQuestionAnswers;
        foreach ($answers as $key => $answerObj) {
            switch ($key) {
                case '0':
                    $this->assertEquals('a', $answerObj->answer);
                    break;
                case '1':
                    $this->assertEquals('b', $answerObj->answer);
                    break;
                case '2':
                    $this->assertEquals('c', $answerObj->answer);
                    break;
            }
        }

        // $attributes = $this->getScenario7GetAttributes();
        // $response = $this->getTestQuestionsByGet($attributes);
        // $answers = $response[0]['question']['ranking_question_answers'];
        // foreach ($answers as $key => $answerArray) {
        //     switch ($key) {
        //         case '0':
        //             $this->assertEquals('aa', $answerArray['answer']);
        //             break;
        //         case '1':
        //             $this->assertEquals('bb', $answerArray['answer']);
        //             break;
        //         case '2':
        //             $this->assertEquals('cc', $answerArray['answer']);
        //             break;
        //     }
        // }
    }

    private function setupScenario7(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForQuestion7($this->originalTestId);
        $this->createRankingQuestion($attributes);
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
                                                                                                            "answer"=> "aa"
                                                                                                            ],
                                                                                                            [
                                                                                                            "order"=> "2",
                                                                                                            "answer"=> "bb"
                                                                                                            ],
                                                                                                            [
                                                                                                            "order"=> "3",
                                                                                                            "answer"=> "cc"
                                                                                                            ]
                                                                                                        ],$this->getRestOfAnswerArray(3,10)),
                                                                            ]);
        unset($attributes["test_id"]);
        return $attributes;
    }

    private function getAttributesForQuestion7($testId){
        return [    
                    "type"=> "RankingQuestion",
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
                                    "answer"=> "a"
                                    ],
                                    [
                                    "order"=> "2",
                                    "answer"=> "b"
                                    ],
                                    [
                                    "order"=> "3",
                                    "answer"=> "c"
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

    private function getScenario7GetAttributes(){
        return $this->getGetAttributes($this->originalTestId);
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

