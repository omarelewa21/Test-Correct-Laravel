<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use tcCore\Test;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class CopyTestTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
    	//$this->clearDB();
    }

    public function tearDown(): void
    {
    	//$this->clearDB();
    	
    	parent::tearDown();
    }

    /** @test */
    public function it_should_copy_questionsForRubriceer()
    {
  
    	$test = Test::where('name','TToets van GM4')->first();
    	$this->assertNull($test);
        $this->setupScenario4();
        $tests = Test::where('name','TToets van GM4')->get();
        $this->$this->assertEquals(1,count($tests));
    }

    private function setupScenario4(){
    	$response = $this->post(
            '/test',
            static::getTeacherOneAuthRequestData(
                $this->getAttributesForTest4()
            )
        );
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

