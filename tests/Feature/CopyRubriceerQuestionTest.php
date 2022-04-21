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

class CopyRubriceerQuestionTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use MatchingQuestionTrait;
    use MultipleChoiceQuestionTrait;

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
    public function it_should_copy_questionsForRubriceer()
    {
        $this->setupScenario4();
        $tests = Test::where('name','TToets van GM4')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM4')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $attributes = $this->getAttributesForEditQuestion4($this->originalTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editMatchingQuestion($copyQuestion->uuid,$attributes);
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $this->assertTrue(count($result)>=0);
    }

    /** @test */
    public function it_should_copy_questionsForRubriceerAndKeepTheOriginalOriginal()
    {
        $this->setupScenario4();
        $tests = Test::where('name','TToets van GM4')->get();
        $this->assertTrue(count($tests)==1);

        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->matchingQuestionAnswers));

        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM4')->get();
        $this->assertTrue(count($tests)==1);

        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);

        $attributes = $this->getAttributesForEditQuestion4($this->originalTestId);
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
                    $this->assertEquals('gg', $answerObj->answer);
                    break;
                case '3':
                    $this->assertEquals('cc', $answerObj->answer);
                    break;
                case '4':
                    $this->assertEquals('dd', $answerObj->answer);
                    break;
                case '5':
                    $this->assertEquals('hh', $answerObj->answer);
                    break;
                case '6':
                    $this->assertEquals('ee', $answerObj->answer);
                    break;
                case '7':
                    $this->assertEquals('ff', $answerObj->answer);
                    break;
                case '8':
                    $this->assertEquals('ii', $answerObj->answer);
                    break;
            }
        }
    }

    /** @test */
    public function it_should_not_copy_questionsForRubriceerWhenAntwoordOptiesAreTheSame()
    {
        $this->setupScenario4();
        $tests = Test::where('name','TToets van GM4')->get();
        $this->assertTrue(count($tests)==1);

        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->matchingQuestionAnswers));

        $originalQuestionArray = $questions->pluck('question_id')->toArray();

        $tests = Test::where('name','Kopie #1 TToets van GM4')->get();
        $this->assertTrue(count($tests)==1);

        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);

        $attributes = $this->getAttributesForEditQuestion4_live($this->copyTestId);
        unset($attributes["test_id"]);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        
        $this->editMatchingQuestion($copyQuestion->uuid,$attributes);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();

        $result = array_diff($originalQuestionArray, $copyQuestionArray);

        $this->assertTrue(count($result)==0);

        
    }



   

    private function setupScenario4(){
        $attributes = $this->getAttributesForTest4();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForQuestion4($this->originalTestId);
        $this->createMatchingQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
    }


    private function getAttributesForTest4(){

        return $this->getTestAttributes([
            'name'                   => 'TToets van GM4',
            'abbreviation'           => 'TTGM4',
            'subject_id'             => '6',
            'introduction'           => 'intro',
        ]);
    
    }

    

    private function getAttributesForEditQuestion4($testId){
        $attributes = array_merge($this->getAttributesForQuestion4($testId),[   
                                                                            "answers"=> array_merge([
                                                                                                        [
                                                                                                            "order"=> "0",
                                                                                                            "left"=> "aa2",
                                                                                                            "right"=> "bb2\ngg2"
                                                                                                        ],
                                                                                                        [
                                                                                                            "order"=> "1",
                                                                                                            "left"=> "",
                                                                                                            "right"=> ""
                                                                                                        ],
                                                                                                        [
                                                                                                            "order"=> "2",
                                                                                                            "left"=> "cc2",
                                                                                                            "right"=> "dd2\nhh2"
                                                                                                        ],
                                                                                                        [
                                                                                                            "order"=> "3",
                                                                                                            "left"=> "",
                                                                                                            "right"=> ""    
                                                                                                        ],
                                                                                                        [
                                                                                                            "order"=> "4",
                                                                                                            "left"=> "ee2",
                                                                                                            "right"=> "ff2\nii2"
                                                                                                        ]
                                                                                                    ],$this->getRestOfAnswerArray(4,49))

                                                                                
                                                                            ,

                                                                        ]);
        unset($attributes["test_id"]);
        return $attributes;
    }

    private function getAttributesForEditQuestion4_live($testId){
        $attributes = array_merge($this->getAttributesForQuestion4($testId),[   
                                                                            "answers"=> array_merge([
                                                                                                        "1" => [
                                                                                                            "order" => "1",
                                                                                                            "left" => "aa",
                                                                                                            "right" => "bb\ngg"
                                                                                                        ],
                                                                                                        "2" => [
                                                                                                            "order" => "2",
                                                                                                            "left" => "cc",
                                                                                                            "right" => "dd\nhh"
                                                                                                        ],
                                                                                                        "3" => [
                                                                                                            "order" => "3",
                                                                                                            "left" => "ee",
                                                                                                            "right" => "ff\nii"
                                                                                                        ]
                                                                                                    ],$this->getRestOfAnswerArray(4,49))

                                                                                
                                                                            ,

                                                                        ]);
        unset($attributes["test_id"]);
        unset($attributes["subtype"]);
        unset($attributes["order"]);
        return $attributes;
    }

    private function getAttributesForQuestion4($testId){
        return [    
                    "type"=> "MatchingQuestion",
                    "score"=> "5",
                    "question"=> "<p>GM4</p> ",
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
                                                    "left"=> "aa",
                                                    "right"=> "bb\ngg"
                                                ],
                                                [
                                                    "order"=> "2",
                                                    "left"=> "cc",
                                                    "right"=> "dd\nhh"
                                                ],
                                                [
                                                    "order"=> "3",
                                                    "left"=> "ee",
                                                    "right"=> "ff\nii"
                                                ],
                                            ],$this->getRestOfAnswerArray(4,49))
                    ,
                    "tags"=> [
                    ],
                    "rtti"=> "R",
                    "bloom"=> "Onthouden",
                    "miller"=> "Weten",
                    "test_id"=> $testId,
                ];
    }

    private function getScenario4GetAttributes(){
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
                    $this->assertEquals('gg2', $answerObj->answer);
                    break;
                case '3':
                    $this->assertEquals('cc2', $answerObj->answer);
                    break;
                case '4':
                    $this->assertEquals('dd2', $answerObj->answer);
                    break;
                case '5':
                    $this->assertEquals('hh2', $answerObj->answer);
                    break;
                case '6':
                    $this->assertEquals('ee2', $answerObj->answer);
                    break;
                case '7':
                    $this->assertEquals('ff2', $answerObj->answer);
                    break;
                case '8':
                    $this->assertEquals('ii2', $answerObj->answer);
                    break;
            }
        }
    }
}


// scenario 1:
//  inloggen d1@test-correct.nl
//  Itembank->Schoollocatie->Toets construeren
//  Toets:  Titel: Toets van GM
//          Afkorting: TGM
//          Introductie tekst: intro
//          Rest: default
//          Meerkeuze vraag aanmaken: default velden
//          Vraaggroep aanmaken:default velden
//          juist/onjuist vraag aanmaken
//          Toets dupliceren
//          juist/onjuist vraag aanpassen
//  Resultaat: vraag originele toets niet gewijzigd
//          Meerkeuze vraag antwoorden tekstueel aanpassen
//  Resultaat: vraag originele toets niet gewijzigd
//  Carlo: 


// scenario 2:
//  inloggen d1@test-correct.nl
//  Itembank->Schoollocatie->Toets construeren
//  Toets:  Titel: Toets van GM2
//          Afkorting: TGM2
//          Introductie tekst: intro
//          Rest: default
//          Meerkeuze vraag aanmaken: default velden
//          Vraaggroep aanmaken:vastzetten true
//          juist/onjuist vraag aanmaken
//          Toets dupliceren
//          juist/onjuist vraag aanpassen
//  Resultaat: vraag originele toets niet gewijzigd

// scenario 3:
//  inloggen d1@test-correct.nl
//  Itembank->Schoollocatie->Toets construeren
//  Toets:  Titel: Toets van GM3
//          Afkorting: TGM3
//          Introductie tekst: intro
//          Rest: default
//          Meerkeuze vraag aanmaken: default velden
//          Vraaggroep aanmaken:default
//          juist/onjuist vraag aanmaken:   RTTI: R
//                                          Bloom: Onthouden
//                                          Miller: Weten
//                                          Antwoord: juist
//          Toets dupliceren
//          juist/onjuist vraag aanpassen: Antwoord: onjuist
//  Resultaat: vraag originele toets niet gewijzigd

// scenario 4:
//  inloggen d1@test-correct.nl
//  Itembank->Schoollocatie->Toets construeren
//  Toets:  Titel: Toets van GM4
//          Afkorting: TGM4
//          Introductie tekst: intro
//          Rest: default
//          Rubriceer vraag aanmaken: RTTI: R
//                                          Bloom: Onthouden
//                                          Miller: Weten
            
//          Toets dupliceren
//          rubriceer vraag aanpassen: Antwoord tekstueel veranderen
//  Resultaat: vraag originele toets gewijzigd

// scenario 5:
//  inloggen d1@test-correct.nl
//  Itembank->Schoollocatie->Toets construeren
//  Toets:  Titel: Toets van GM5
//          Afkorting: TGM5
//          Introductie tekst: intro
//          Rest: default
//          Meerkeuze vraag aanmaken: RTTI: R
//                                          Bloom: Onthouden
//                                          Miller: Weten
            
//          Toets dupliceren
//          meerkeuze vraag aanpassen: Antwoord tekstueel veranderen
//  Resultaat: vraag originele toets niet gewijzigd

// scenario 6:
//  inloggen d1@test-correct.nl
//  Itembank->Schoollocatie->Toets construeren
//  Toets:  Titel: Toets van GM6
//          Afkorting: TGM6
//          Introductie tekst: intro
//          Rest: default
//          Rubriceer vraag aanmaken: default velden
//          Toets dupliceren
//          rubriceer vraag aanpassen: Antwoord tekstueel veranderen
//  Resultaat: vraag originele toets gewijzigd
//  conclusie: onafhankelijk van taxonomie

// scenario 7:
//  inloggen d1@test-correct.nl
//  Itembank->Schoollocatie->Toets construeren
//  Toets:  Titel: Toets van GM7
//          Afkorting: TGM7
//          Introductie tekst: intro
//          Rest: default
//          Rangschik vraag aanmaken: default velden
//          Toets dupliceren
//          Rangschik vraag aanpassen: Antwoord tekstueel veranderen
//  Resultaat: vraag originele toets gewijzigd

// scenario 8:
//  inloggen d1@test-correct.nl
//  Itembank->Schoollocatie->Toets construeren
//  Toets:  Titel: Toets van GM8
//          Afkorting: TGM8
//          Introductie tekst: intro
//          Rest: default
//          Combinatie vraag aanmaken: default velden
//          Toets dupliceren
//          Combinatie vraag aanpassen: Antwoord tekstueel veranderen
//  Resultaat: vraag originele toets gewijzigd

// scenario 9:
//  inloggen d1@test-correct.nl
//  Itembank->Schoollocatie->Toets construeren
//  Toets:  Titel: Toets van GM9
//          Afkorting: TGM9
//          Introductie tekst: intro
//          Rest: default
//          Combinatie vraag aanmaken:  RTTI: R
//                                      Bloom: Onthouden
//                                      Miller: Weten
//          Toets dupliceren
//          Combinatie vraag aanpassen: Antwoord tekstueel veranderen
//                                      Antwoord optie toevoegen
//  Resultaat: vraag originele toets gewijzigd: andere antwoorden, antwoord optie toegevoegd
//          Combinatie vraag aanpassen: Naam tekstueel veranderen
//  Resultaat: vraag originele toets: naam niet gewijzigd
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


