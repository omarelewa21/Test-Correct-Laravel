<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\Question;
use tcCore\GroupQuestion;
use tcCore\DrawingQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\DrawingQuestionTrait;
use tcCore\Traits\Dev\MultipleChoiceQuestionTrait;
use tcCore\Traits\Dev\GroupQuestionTrait;
use Illuminate\Support\Facades\DB;

/**
 * @group ignore
 */
class AddToDatabaseTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use DrawingQuestionTrait;
    use MultipleChoiceQuestionTrait;
    use GroupQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;


    /**
     * @test
     */
    public function it_should_not_copy_questions_when_add_to_database_is_changed_drawing()
    {
        $this->setupScenario4();
        $tests = Test::where('name', 'Test Title')->get();
        $this->assertTrue(count($tests) == 1);

        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions) == 1);
        $this->assertEquals('DrawingQuestion', $questions->first()->question->type);

        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name', 'Kopie #1 Test Title')->get();
        $this->assertTrue(count($tests) == 1);
        $this->originalAndCopyShareQuestion();
        $attributes = $this->getAttributesForEditQuestion4($this->copyTestId);
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $this->editDrawingQuestion($copyQuestion->uuid, $attributes);

        $this->originalAndCopyShareQuestion();

    }

    /** @test */
    public function it_should_not_copy_questions_when_add_to_database_is_changed_mc_in_group()
    {
        $this->setupScenario1();
        $this->originalAndCopyShareGroupQuestion();
        $attributes = $this->getAttributesForEditQuestion1($this->copyTestId);
        $copyGroupTestQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $this->editMultipleChoiceQuestionInGroup($copyGroupTestQuestion->uuid, $copyQuestion->uuid, $attributes);
        $this->originalAndCopyShareGroupQuestion();

    }

    /** @test */
    public function it_should_not_copy_questions_when_add_to_database_is_changed_in_group()
    {
        $this->setupScenario1();
        $this->originalAndCopyShareGroup();
        $attributes = $this->getAttributesForEditQuestion2($this->copyTestId);
        $copyGroupTestQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $this->editGroupQuestion($copyGroupTestQuestion->uuid, $attributes);
        $this->originalAndCopyShareGroup();
    }

    private function originalAndCopyShareQuestion()
    {
        $questions = Test::find($this->originalTestId)->testQuestions;
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);

        $this->assertTrue(count($result) == 0);
    }

    private function setupScenario4()
    {
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForDrawingQuestion($this->originalTestId);
        $this->createDrawingQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
    }

    private function setupScenario1()
    {
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForGroupQuestion($this->originalTestId);
        $groupTestQuestionId = $this->createGroupQuestion($attributes);
        $groupTestQuestion = TestQuestion::find($groupTestQuestionId);
        $attributes = $this->getAttributesForMultipleChoiceQuestion($this->originalTestId);
        $this->createMultipleChoiceQuestionInGroup($attributes, $groupTestQuestion->uuid);
        $this->duplicateTest($this->originalTestId);

        $this->checkScenario1Success('Test Title', $this->originalTestId);
        $this->checkScenario1Success('Kopie #1 Test Title', $this->copyTestId);

    }

    private function checkScenario1Success($name, $testId)
    {
        $tests = Test::where('name', $name)->get();
        $this->assertTrue(count($tests) == 1);
        $questions = Test::find($testId)->testQuestions;
        $this->assertCount(1, $questions);
        $this->assertEquals('GroupQuestion', $questions->first()->question->type);
        $groupQuestion = $questions->first()->question;
        $subQuestions = $groupQuestion->groupQuestionQuestions;
        $this->assertCount(1, $subQuestions);
        $this->assertEquals('MultipleChoiceQuestion', $subQuestions->first()->question->type);
    }

    private function getAttributesForEditQuestion4($testId)
    {
        return array_merge($this->getAttributesForDrawingQuestion($testId), ['add_to_database' => 0]);
    }

    private function getAttributesForEditQuestion1($testId)
    {
        return array_merge($this->getAttributesForMultipleChoiceQuestion($testId), ['add_to_database' => 0]);
    }

    private function getAttributesForEditQuestion2($testId)
    {
        return array_merge($this->getAttributesForGroupQuestion($testId), ['add_to_database' => 0]);
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

// Scenario 4
// inloggen d1@test-correct.nl
// Itembank->Schoollocatie->Toets construeren
// Toets: 	Titel: Toets van GM4
// 		Afkorting: TGM4
// 		Introductie tekst: intro
// 		Rest: default
// 		Teken vraag aanmaken en opslaan: openbaar maken blijft aangevinkt
// 		Toets dupliceren
// 		Teken vraag (kopie) aanpassen: openbaar maken uitvinken
// 		GM: op mijn pc veranderd de uniek ID van de kopie na opslaan

// Scenario 5
// inloggen d1@test-correct.nl
// Itembank->Schoollocatie->Toets construeren
// Toets: 	Titel: Toets van GM5
// 		Afkorting: TGM5
// 		Introductie tekst: intro
// 		Rest: default
// 		Teken vraag aanmaken en opslaan: openbaar maken blijft aangevinkt
// 		Toets dupliceren
// 		Teken vraag (kopie) aanpassen: openbaar maken uitvinken
// 		Teken vraag (origineel) openen en openbaar maken uitvinken en opslaan
// 		GM: op mijn pc veranderd de uniek ID van het origineel na opslaan

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



