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
use Illuminate\Support\Facades\DB;

class CopyTestTest extends TestCase
{
    use DatabaseTransactions;

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
        $this->editQuestion($copyQuestion->uuid,$attributes);
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
        $this->assertTrue(!is_null($questions->first()->question->getQuestionInstance()->matchingQuestionAnswers));
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM4')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $attributes = $this->getAttributesForEditQuestion4($this->originalTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editQuestion($copyQuestion->uuid,$attributes);
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $this->assertTrue(count($result)>=0);
        $originalQuestions = Test::find($this->originalTestId)->testQuestions;
        foreach ($originalQuestions as $key => $testQuestion) {
        	dd($testQuestion->question->getQuestionInstance()->matchingQuestionAnswers);
        }
    }

    private function setupScenario4(){
    	$this->createTLCTest();
    	$attributes = $this->getAttributesForQuestion4($this->originalTestId);
        $this->createQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
    }

    private function createTLCTest(){
    	$response = $this->post(
            'api-c/test',
            static::getTeacherOneAuthRequestData(
                $this->getAttributesForTest4()
            )
        );
        $response->assertStatus(200);
        $testId = $response->decodeResponseJson()['id'];
        $this->originalTestId = $testId;
    }

    private function createQuestion($attributes){
    	$response = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
        $questionId = $response->decodeResponseJson()['id'];
        $this->originalQuestionId = $questionId;
    }

    private function editQuestion($uuid,$attributes){
    	$response = $this->put(
            'api-c/test_question/'.$uuid,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
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

    private function clearDB(){
    	$tests = Test::where('name','TToets van GM4')->get();
    	foreach ($test as $key => $test) {
    		$questions = $test->questions;
    		foreach ($questions as $key => $question) {
    			# code...
    		}
    	}
    	$sql = "delete from tests where name='TToets van GM4'";
    	DB::delete($sql);
    }

    private function getAttributesForTest4(){

        return $this->getAttributes([
            'name'                   => 'TToets van GM4',
            'abbreviation'           => 'TTGM4',
            'subject_id'             => '6',
            'introduction'           => 'intro',
        ]);
    
    }

    private function getAttributes($overrides = [])
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
        ], $overrides);
    }

    private function getAttributesForEditQuestion4($testId){
    	$attributes = array_merge($this->getAttributesForTest4($testId),[	"question"=> "<p>GM42</p>",
    																		"answers"=> [
																				[
																					"order"=> "1",
																					"left"=> "aa2",
																					"right"=> "bb2"
																				],
																				[
																					"order"=> "2",
																					"left"=> "cc2",
																					"right"=> "dd2"
																				],
																				[
																					"order"=> "3",
																					"left"=> "ee2",
																					"right"=> "ff2"
																				],
																				[
																					"order"=> "3",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "4",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "5",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "6",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "7",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "8",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "9",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "10",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "11",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "12",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "13",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "14",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "15",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "16",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "17",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "18",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "19",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "20",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "21",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "22",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "23",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "24",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "25",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "26",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "27",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "28",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "29",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "30",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "31",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "32",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "33",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "34",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "35",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "36",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "37",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "38",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "39",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "40",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "41",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "42",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "43",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "44",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "45",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "46",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "47",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "48",
																					"left"=> "",
																					"right"=> ""
																				],
																				[
																					"order"=> "49",
																					"left"=> "",
																					"right"=> ""
																				]
																			],

    																	]);
		unset($attributes["test_id"]);
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
					"answers"=> [
						[
							"order"=> "1",
							"left"=> "aa",
							"right"=> "bb"
						],
						[
							"order"=> "2",
							"left"=> "cc",
							"right"=> "dd"
						],
						[
							"order"=> "3",
							"left"=> "ee",
							"right"=> "ff"
						],
						[
							"order"=> "3",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "4",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "5",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "6",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "7",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "8",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "9",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "10",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "11",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "12",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "13",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "14",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "15",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "16",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "17",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "18",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "19",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "20",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "21",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "22",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "23",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "24",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "25",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "26",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "27",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "28",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "29",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "30",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "31",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "32",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "33",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "34",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "35",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "36",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "37",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "38",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "39",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "40",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "41",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "42",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "43",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "44",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "45",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "46",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "47",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "48",
							"left"=> "",
							"right"=> ""
						],
						[
							"order"=> "49",
							"left"=> "",
							"right"=> ""
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

// scenario 10:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM10
// 			Afkorting: TGM10
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

