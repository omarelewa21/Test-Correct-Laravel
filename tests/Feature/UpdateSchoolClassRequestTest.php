<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\EducationLevel;
use tcCore\School;
use tcCore\SchoolClass;
use Tests\TestCase;

class UpdateSchoolClassRequestTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function cannot_class_to_duplicate_with_ampersand()
    {
        $response = $this->post(
            route('school_class.store'),
            static::getSchoolBeheerderAuthRequestData([
                "name"                 => "Biologie2&",
                "school_location_id"   => "3",
                "is_main_school_class" => "0",
                "education_level_id"   => EducationLevel::find(1)->uuid,
                "education_level_year" => "1",
                "school_year_id"       => "3",
            ])
        )->assertSuccessful();

        $response = $this->post(
            route('school_class.store'),
            static::getSchoolBeheerderAuthRequestData([
                "name"                 => "Biologie2",
                "school_location_id"   => "3",
                "is_main_school_class" => "0",
                "education_level_id"   => EducationLevel::find(1)->uuid,
                "education_level_year" => "1",
                "school_year_id"       => "3",
            ])
        )->assertSuccessful();

        $response = $this->put(
            route('school_class.update', SchoolClass::where('name', 'Biologie2')->first()->uuid),
            static::getSchoolBeheerderAuthRequestData([
                "name"                 => "Biologie2&",
                "school_location_id"   => "3",
                "is_main_school_class" => "0",
                "education_level_id"   => EducationLevel::find(1)->uuid,
                "education_level_year" => "1",
                "school_year_id"       => "3",
            ])
        )->assertStatus(422);

        $tempResponse = $response->decodeResponseJson();
        $this->assertEquals($tempResponse['errors']['name'], ['Deze klasnaam bestaat al in dit schooljaar']);
    }
}