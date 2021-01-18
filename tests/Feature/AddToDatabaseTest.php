<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\Question;
use tcCore\DrawingQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestTrait;
use Tests\Traits\DrawingQuestionTrait;
use Illuminate\Support\Facades\DB;

class AddToDatabaseTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use DrawingQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;
    

    /** @test */
    public function it_should_not_copy_questions_when_add_to_database_is_changed_drawing()
    {
        $this->setupScenario4();
        $tests = Test::where('name','Test Title')->get();
        $this->assertTrue(count($tests)==1);

        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertEquals('DrawingQuestion',$questions->first()->question->type);

        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 Test Title')->get();
        $this->assertTrue(count($tests)==1);
        $this->originalAndCopyShareQuestion();
        $attributes = $this->getAttributesForEditQuestion4($this->copyTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editDrawingQuestion($copyQuestion->uuid,$attributes);
        
        $this->originalAndCopyShareQuestion();
        
    }

    private function originalAndCopyShareQuestion(){
    	$questions = Test::find($this->originalTestId)->testQuestions;
    	$originalQuestionArray = $questions->pluck('question_id')->toArray();
    	$copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        
        $this->assertTrue(count($result)==0);
    }

    private function setupScenario4(){
    	$attributes = $this->getTestAttributes();
    	unset($attributes['school_classes']);
    	$this->createTLCTest($attributes);
    	$attributes = $this->getAttributesForDrawingQuestion($this->originalTestId);
        $this->createDrawingQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
    }

    private function getAttributesForEditQuestion4($testId){
    	return array_merge($this->getAttributesForDrawingQuestion($testId),['add_to_database'=>0]);
    }

    private function duplicateTestGM($testId){
    	$testCountInit = Test::count();
    	$testQuestionCountInit = TestQuestion::count();
    	$questionCountInit = Question::count();
        $test = Test::find($testId);
        $response = $this->post(
            '/api-c/test/'.$test->uuid.'/duplicate',
            static::getTeacherOneAuthRequestData(
                ['status'=>0]
            )
        );
        $this->assertEquals(++$testCountInit, Test::count());
    	$this->assertEquals(++$testQuestionCountInit, TestQuestion::count());
    	$this->assertEquals($questionCountInit, Question::count());
        $response->assertStatus(200);
        $testId = $response->decodeResponseJson()['id'];
        $this->copyTestId = $testId;
    }
}

//  Scenario 1
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM1
// 			Afkorting: TGM1
// 			Introductie tekst: intro
// 			Rest: default
// 			Meerkeuze vraag aanmaken en opslaan: openbaar maken blijft aangevinkt
// 			Meerkeuze vraag opnieuw openen en openbaar maken uitvinken en opslaan
// 	GM: op mijn pc blijft de uniek ID hetzelfde (dev en testing database)

//  Scenario 2
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM2
// 			Afkorting: TGM2
// 			Introductie tekst: intro
// 			Rest: default
// 			Meerkeuze vraag aanmaken en opslaan: openbaar maken blijft aangevinkt
// 			Toets dupliceren
// 			meerkeuze vraag (kopie) aanpassen: Antwoord tekstueel veranderen
// 			Meerkeuze vraag (origineel) openen en openbaar maken uitvinken en opslaan
// 	GM: op mijn pc blijft de uniek ID hetzelfde (dev en testing database)

//  Scenario 3
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM3
// 			Afkorting: TGM3
// 			Introductie tekst: intro
// 			Rest: default
// 			ARQ vraag aanmaken en opslaan: openbaar maken blijft aangevinkt
// 			Toets dupliceren
// 			ARQ vraag (kopie) aanpassen: openbaar maken uitvinken
// 			GM: op mijn pc blijft de uniek ID hetzelfde en is in de originele vraag openbaar maken ook uitgevinkt  (dev en testing database)

//  Scenario 4
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM4
// 			Afkorting: TGM4
// 			Introductie tekst: intro
// 			Rest: default
// 			Teken vraag aanmaken en opslaan: openbaar maken blijft aangevinkt
// 			Toets dupliceren
// 			Teken vraag (kopie) aanpassen: openbaar maken uitvinken
// 			GM: op mijn pc veranderd de uniek ID van de kopie na opslaan

//  Scenario 5
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM5
// 			Afkorting: TGM5
// 			Introductie tekst: intro
// 			Rest: default
// 			Teken vraag aanmaken en opslaan: openbaar maken blijft aangevinkt
// 			Toets dupliceren
// 			Teken vraag (kopie) aanpassen: openbaar maken uitvinken 
// 			Teken vraag (origineel) openen en openbaar maken uitvinken en opslaan
// 			GM: op mijn pc veranderd de uniek ID van het origineel na opslaan

// scenario 6:
// 	inloggen d1@test-correct.nl
// 	Itembank->Schoollocatie->Toets construeren
// 	Toets: 	Titel: Toets van GM6
// 			Afkorting: TGM6
// 			Introductie tekst: intro
// 			Rest: default
// 			Meerkeuze vraag aanmaken: default velden
// 			Vraaggroep aanmaken:default velden
// 			Meerkeuze vraag aanmaken en opslaan: openbaar maken blijft aangevinkt
// 			Meerkeuze vraag openen en openbaar maken uitvinken en opslaan
// 			GM: op mijn pc veranderd de uniek ID van het origineel na opslaan



