<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\FactoryScenarios\FactoryScenarioClassImportCake;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Student;
use tcCore\User;
use Tests\TestCase;

class SchoolClassStudentImportControllerTest extends TestCase
{
    use DatabaseTransactions;


    /** @test */
    public function it_can_import_a_user_rejects_international_characters_response()
    {

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "carloschéoep+K999jjanssen@hotmail.com",
            ]],
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('data.0.username');

        $decodedResponse = $response->decodeResponseJson();
        $this->assertStringContainsString('The email address contains invalid or international characters', $decodedResponse['errors']['data.0.username'][1]);
    }

    /** @test */
    public function it_can_import_a_user_rejects_international_characters_various()
    {

        $this->assertCount(0, User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->get());

        $countStudentsBefore = \tcCore\Student::count();

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "123451",
                'name_first'  => "Jan1",
                'name_suffix' => "",
                'name'        => "Janssen1",
                'username'    => "140965ä@test-correct.nl"
            ]]
        ]));

        $this->assertEquals(422, $response->getStatusCode());

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "123452",
                'name_first'  => "Jan2",
                'name_suffix' => "",
                'name'        => "Janssen2",
                'username'    => "140907ö@test-correct.nl"
            ]]]));

        $this->assertEquals(422, $response->getStatusCode());

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "123453",
                'name_first'  => "Jan3",
                'name_suffix' => "",
                'name'        => "Janssen3",
                'username'    => "140990û@test-correct.nl"
            ]]]));

        $this->assertEquals(422, $response->getStatusCode());

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "123454",
                'name_first'  => "Jan4",
                'name_suffix' => "",
                'name'        => "Janssen4",
                'username'    => "140922é@test-correct.nl",
            ]]]));

        $this->assertEquals(422, $response->getStatusCode());

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "123455",
                'name_first'  => "Jan5",
                'name_suffix' => "",
                'name'        => "Janssen5",
                'username'    => "140991è@test-correct.nl",
            ]]]));

        $this->assertEquals(422, $response->getStatusCode());

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "123456",
                'name_first'  => "Jan6",
                'name_suffix' => "",
                'name'        => "Janssen6",
                'username'    => "140878á@test-correct.nl",
            ]]]));

        $this->assertEquals(422, $response->getStatusCode());

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "123457",
                'name_first'  => "Jan7",
                'name_suffix' => "",
                'name'        => "Janssen7",
                'username'    => "141507ë@test-correct.nl",
            ]]]));

        $this->assertEquals(422, $response->getStatusCode());

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "123459",
                'name_first'  => "Jan9",
                'name_suffix' => "",
                'name'        => "Janssen9",
                'username'    => "1405&#x26;52@test-correct.nl",
            ]]]));

        $this->assertEquals(422, $response->getStatusCode());

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id'   => "123458",
                'name_first'    => "Jan8",
                'name_suffix'   => "",
                'name'          => "Janssen8",
                'usern&amp;ame' => "140841@test-correct.nl",
            ]]]));

        $this->assertEquals(422, $response->getStatusCode());
    }


    /** @test */
    public function it_can_import_a_user()
    {
        $this->assertCount(0, User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->get());

        $countStudentsBefore = \tcCore\Student::count();

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "carloschoep+K999jjanssen@hotmail.com",
            ]],
        ]))->assertSuccessful();
        $this->assertEquals('1 studenten zijn toegevoegd', $response->getContent());

        $this->assertCount(1, User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->get());
        $this->assertCount(++$countStudentsBefore, Student::get());
        $this->assertEquals(
            3,
            User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->first()->students()->first()->class_id
        );
    }

    /** @test */
    public function it_can_import_a_user_with_class()
    {
        $this->assertCount(0, User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->get());

        $countStudentsBefore = \tcCore\Student::count();

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(8)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id'       => "12345",
                'name_first'        => "Jan",
                'name_suffix'       => "",
                'name'              => "Janssen",
                'username'          => "carloschoep+K999jjanssen@hotmail.com",
                'school_class_name' => "Biologie"
            ]],
        ]))->assertSuccessful();
        $this->assertEquals('1 studenten zijn toegevoegd', $response->getContent());

        $this->assertCount(1, User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->get());
        $this->assertCount(++$countStudentsBefore, Student::get());
        $this->assertEquals(
            3,
            User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->first()->students()->first()->class_id
        );
    }

    /** @test */
    public function it_cannot_import_users_with_multiple_classes_unkown_class()
    {
        $usernames = ["jansen@hotmail.com", "marien@hotmail.com", "pietersen@hotmail.com", "scholten@hotmail.com", "klaassen@hotmail.com"];
        foreach ($usernames as $username) {
            $this->assertCount(0, User::whereUsername($username)->get());
        }

        $countStudentsBefore = \tcCore\Student::count();
        $schoolLocation = SchoolLocation::find(3);
        $schoolClass = $schoolLocation->schoolClasses()->first();
        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => $schoolLocation->uuid,
                'schoolClass'    => $schoolClass->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [
                [
                    "external_id"       => "12345",
                    "name_first"        => "Jan",
                    "name_suffix"       => "",
                    "name"              => "Jansen",
                    "username"          => "jansen@hotmail.com",
                    "school_class_name" => "Biologie"
                ],
                [
                    "external_id"       => "234567",
                    "name_first"        => "Marie",
                    "name_suffix"       => "",
                    "name"              => "Marien",
                    "username"          => "marien@hotmail.com",
                    "school_class_name" => "Biologie"
                ],
                [
                    "external_id"       => "32134",
                    "name_first"        => "Piet",
                    "name_suffix"       => "",
                    "name"              => "Pietersen",
                    "username"          => "pietersen@hotmail.com",
                    "school_class_name" => "Klas1"
                ],
                [
                    "external_id"       => "23432",
                    "name_first"        => "Karin",
                    "name_suffix"       => "",
                    "name"              => "Scholten",
                    "username"          => "scholten@hotmail.com",
                    "school_class_name" => "Klas1"
                ],
                [
                    "external_id"       => "5432",
                    "name_first"        => "Klaas",
                    "name_suffix"       => "",
                    "name"              => "Klaassen",
                    "username"          => "klaassen@hotmail.com",
                    "school_class_name" => "Klas1"
                ]
            ],
        ]));
        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_import_users_with_multiple_classes()
    {
        $usernames = ["jansen@hotmail.com", "marien@hotmail.com", "pietersen@hotmail.com", "scholten@hotmail.com", "klaassen@hotmail.com"];
        foreach ($usernames as $username) {
            $this->assertCount(0, User::whereUsername($username)->get());
        }

        $countStudentsBefore = \tcCore\Student::count();
        $schoolLocation = SchoolLocation::find(3);
        $schoolClass = $schoolLocation->schoolClasses()->first();
        $klas1 = new SchoolClass();
        $klas1->education_level_id = 1;
        $klas1->school_location_id = $schoolLocation->id;
        $klas1->school_year_id = 3;
        $klas1->name = 'Klas1';
        $klas1->education_level_year = 1;
        $klas1->is_main_school_class = 0;
        $klas1->do_not_overwrite_from_interface = 0;
        $klas1->demo = 0;

        $klas1->save();
        $biologie = $schoolLocation->schoolClasses()->where('name', 'Biologie')->first();
        $this->assertNotNull($biologie);

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => $schoolLocation->uuid,
                'schoolClass'    => $schoolClass->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [
                [
                    "external_id"       => "12345",
                    "name_first"        => "Jan",
                    "name_suffix"       => "",
                    "name"              => "Jansen",
                    "username"          => "jansen@hotmail.com",
                    "school_class_name" => "Biologie"
                ],
                [
                    "external_id"       => "234567",
                    "name_first"        => "Marie",
                    "name_suffix"       => "",
                    "name"              => "Marien",
                    "username"          => "marien@hotmail.com",
                    "school_class_name" => "Biologie"
                ],
                [
                    "external_id"       => "32134",
                    "name_first"        => "Piet",
                    "name_suffix"       => "",
                    "name"              => "Pietersen",
                    "username"          => "pietersen@hotmail.com",
                    "school_class_name" => "Klas1"
                ],
                [
                    "external_id"       => "23432",
                    "name_first"        => "Karin",
                    "name_suffix"       => "",
                    "name"              => "Scholten",
                    "username"          => "scholten@hotmail.com",
                    "school_class_name" => "Klas1"
                ],
                [
                    "external_id"       => "5432",
                    "name_first"        => "Klaas",
                    "name_suffix"       => "",
                    "name"              => "Klaassen",
                    "username"          => "klaassen@hotmail.com",
                    "school_class_name" => "Klas1"
                ]
            ],
        ]))->assertSuccessful();
        $this->assertEquals('5 studenten zijn toegevoegd', $response->getContent());

        foreach ($usernames as $username) {
            $this->assertCount(1, User::whereUsername($username)->get());
        }
        $this->assertCount(($countStudentsBefore + 5), Student::get());
        $this->assertEquals(
            $biologie->id,
            User::whereUsername("jansen@hotmail.com")->first()->students()->first()->class_id
        );
        $this->assertEquals(
            $biologie->id,
            User::whereUsername("marien@hotmail.com")->first()->students()->first()->class_id
        );
        $this->assertEquals(
            $klas1->id,
            User::whereUsername("pietersen@hotmail.com")->first()->students()->first()->class_id
        );
        $this->assertEquals(
            $klas1->id,
            User::whereUsername("scholten@hotmail.com")->first()->students()->first()->class_id
        );
        $this->assertEquals(
            $klas1->id,
            User::whereUsername("klaassen@hotmail.com")->first()->students()->first()->class_id
        );
    }

    /** @test */
    public function it_cannot_import_users_with_multiple_classes_twice()
    {
        $usernames = ["jansen@hotmail.com", "marien@hotmail.com", "pietersen@hotmail.com", "scholten@hotmail.com", "klaassen@hotmail.com"];
        foreach ($usernames as $username) {
            $this->assertCount(0, User::whereUsername($username)->get());
        }

        $countStudentsBefore = \tcCore\Student::count();
        $schoolLocation = SchoolLocation::find(3);
        $schoolClass = $schoolLocation->schoolClasses()->first();
        $klas1 = new SchoolClass();
        $klas1->education_level_id = 1;
        $klas1->school_location_id = $schoolLocation->id;
        $klas1->school_year_id = 3;
        $klas1->name = 'Klas1';
        $klas1->education_level_year = 1;
        $klas1->is_main_school_class = 0;
        $klas1->do_not_overwrite_from_interface = 0;
        $klas1->demo = 0;

        $klas1->save();
        $biologie = $schoolLocation->schoolClasses()->where('name', 'Biologie')->first();
        $this->assertNotNull($biologie);

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => $schoolLocation->uuid,
                'schoolClass'    => $schoolClass->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData($this->getMultipleClassData()))->assertSuccessful();
        $this->assertEquals('5 studenten zijn toegevoegd', $response->getContent());
        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => $schoolLocation->uuid,
                'schoolClass'    => $schoolClass->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData($this->getMultipleClassData()));
        $response->assertStatus(422);

        $this->assertCount(5, $response->decodeResponseJson()['errors']);

    }

    /** @test */
    public function it_can_import_multiple_users()
    {
        $this->assertCount(0, User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->get());

        $countStudentsBefore = \tcCore\Student::count();

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "carloschoep+K999jjanssen@hotmail.com",
            ], [
                'external_id' => "12346",
                'name_first'  => "Janus",
                'name_suffix' => "",
                'name'        => "Janssens",
                'username'    => "carloschoep+K1000jjanssen@hotmail.com",
            ]],
        ]))->assertSuccessful();
        $this->assertEquals('2 studenten zijn toegevoegd', $response->getContent());

        $this->assertCount(1, User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->get());
        $this->assertCount(1, User::whereUsername("carloschoep+K1000jjanssen@hotmail.com")->get());
        $this->assertCount(2 + $countStudentsBefore, Student::get());
        $this->assertEquals(
            3,
            User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->first()->students()->first()->class_id
        );
        $this->assertEquals(
            3,
            User::whereUsername("carloschoep+K1000jjanssen@hotmail.com")->first()->students()->first()->class_id
        );
    }

    /** @test */
    public function the_email_address_should_be_unique_for_every_user_in_the_request()
    {
        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "carloschoep+K999jjanssen@hotmail.com",
            ], [
                'external_id' => "12346",
                'name_first'  => "Janus",
                'name_suffix' => "",
                'name'        => "Janssens",
                'username'    => "carloschoep+K999jjanssen@hotmail.com",
            ]],
        ]))->assertStatus(422);

        $decodedResponse = $response->decodeResponseJson();
        $requiredErrors = [
            'data.0.username' => 'Deze import bevat dubbele emailadressen voor dezelfde klas.',
            'data.1.username' => 'Deze import bevat dubbele emailadressen voor dezelfde klas.',
        ];

        foreach ($requiredErrors as $errorField => $errorMessage) {
            $this->assertArrayHasKey($errorField, $decodedResponse['errors']);
            $this->assertEquals($errorMessage, $decodedResponse['errors'][$errorField][0]);
        }
    }

    /** @test */
    public function the_email_address_should_be_unique_and_cannot_already_be_in_the_database()
    {
        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "carloschoep+K999jjanssen@hotmail.com",
            ],],
        ]))->assertSuccessful();

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12346",
                'name_first'  => "Janus",
                'name_suffix' => "",
                'name'        => "Janssens",
                'username'    => "carloschoep+K999jjanssen@hotmail.com",
            ]],
        ]))->assertStatus(422);

        $decodedResponse = $response->decodeResponseJson();
        $requiredErrors = [
            'data.0.username' => 'The data.0.username has already been taken.',
        ];

        foreach ($requiredErrors as $errorField => $errorMessage) {
            $this->assertArrayHasKey($errorField, $decodedResponse['errors']);
            $this->assertEquals($errorMessage, $decodedResponse['errors'][$errorField][0]);
        }
    }

    /** @test */
    public function the_external_id_should_be_unique_in_the_request_between_users()
    {
        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "thisOne@hotmail.com",
            ], [
                'external_id' => "12345",
                'name_first'  => "Janus",
                'name_suffix' => "",
                'name'        => "oepsi",
                'username'    => "other@hotmail.com",
            ]],
        ]))->assertStatus(422);

        $decodedResponse = $response->decodeResponseJson();
        $requiredErrors = [
            'data.0.external_id' => 'Deze import bevat dubbele studentennummers voor dezelfde klas.',
            'data.1.external_id' => 'Deze import bevat dubbele studentennummers voor dezelfde klas.',
        ];

        foreach ($requiredErrors as $errorField => $errorMessage) {
            $this->assertArrayHasKey($errorField, $decodedResponse['errors']);
            $this->assertEquals($errorMessage, $decodedResponse['errors'][$errorField][0]);
        }
    }


    /** @test */
    public function the_external_id_can_be_used_for_users_in_diffent_school_locations()
    {
        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "thisOne@hotmail.com",
            ]],
        ]))->assertSuccessful();
        // put schoolbeheerder in school_location one;
        $user = User::where('username', '=', static::USER_SCHOOLBEHEERDER)->get()->first();
        $user->school_location_id = 1;
        $user->save();


        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(1)->uuid,
                'schoolClass'    => SchoolClass::find(4)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Janus",
                'name_suffix' => "",
                'name'        => "oepsi",
                'username'    => "other@hotmail.com",
            ]],
        ]))->assertSuccessful();
    }


    /** @test */
    public function the_username_cannot_be_used_for_users_in_diffent_school_locations()
    {
        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12346",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "thisOne@hotmail.com",
            ]],
        ]))->assertSuccessful();
        // put schoolbeheerder in school_location one;
        $user = User::where('username', '=', static::USER_SCHOOLBEHEERDER)->get()->first();
        $user->school_location_id = 1;
        $user->save();


        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(1)->uuid,
                'schoolClass'    => SchoolClass::find(4)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Janus",
                'name_suffix' => "",
                'name'        => "oepsi",
                'username'    => "thisOne@hotmail.com",
            ]],
        ]))->assertStatus(422);
        $this->assertArrayHasKey('data.0.username', $response->decodeResponseJson()['errors']);
        $this->assertEquals('The data.0.username has already been taken.', $response->decodeResponseJson()['errors']['data.0.username'][0]);
    }


    /** @test */
    public function when_importing_the_same_users_in_the_same_class_twice_it_fails()
    {
//  TODO      dit moet leiden tot een foutmelding

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "thisOne@hotmail.com",
            ],],
        ]))->assertSuccessful();


        $beforeCountStudents = Student::count();
        $beforeCountUsers = User::count();

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "thisOne@hotmail.com",
            ],],
        ]))->assertStatus(422);

        $this->assertEquals($beforeCountStudents, Student::count());
        $this->assertEquals($beforeCountUsers, User::count());
    }

    /** @test */
    public function when_importing_the_same_users_in_a_different_class_it_does_not_fail_and_enter_a_extra_student_but_no_user_record()
    {
        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "thisOne@hotmail.com",
            ],],
        ]))->assertSuccessful();

        $beforeCountStudents = Student::count();
        $beforeCountUsers = User::count();

        // move school_class with id=2 to school_location 3;
        $schoolClassTwo = SchoolClass::find(2);
        $schoolClassTwo->school_location_id = 3;
        $schoolClassTwo->save();

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(2)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "thisOne@hotmail.com",
            ],],
        ]))->assertSuccessful();

        $this->assertEquals(++$beforeCountStudents, Student::count());
        $this->assertEquals($beforeCountUsers, User::count());
    }


    /** @test */
    public function the_external_id_should_be_unique_for_this_email_address_in_this_school_location()
    {
        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "thisOne@hotmail.com",
            ],],
        ]))->assertSuccessful();

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                'external_id' => "12345",
                'name_first'  => "Janus",
                'name_suffix' => "",
                'name'        => "oepsi",
                'username'    => "other@hotmail.com",
            ],],
        ]))->assertStatus(422)
            ->assertJsonValidationErrorFor('data.0.external_id');

        $this->assertStringContainsString(
            __('validation.unique', ['attribute' => 'data.0.external id']),
            $response->getContent()
        );
    }

    /** @test */
    public function it_should_return_required_column_validation_errors_for_required_fields()
    {
        $fields = ['data.0.username', 'data.0.name_first', 'data.0.name'];
        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid,
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [[
                "12345",
                "Jan",
                "",
                "Janssen",
                "carloschoep+K999jjanssen@hotmail.com",
            ]],
        ]))->assertStatus(422)
            ->assertJsonValidationErrors($fields);

        collect($fields)->each(function ($field) use ($response) {
            $this->assertStringContainsString(
                __('validation.required', ['attribute' => $field]),
                $response->getContent()
            );
        });
    }

    private function getMultipleClassData()
    {
        return [
            'data' => [
                [
                    "external_id"       => "12345",
                    "name_first"        => "Jan",
                    "name_suffix"       => "",
                    "name"              => "Jansen",
                    "username"          => "jansen@hotmail.com",
                    "school_class_name" => "Biologie"
                ],
                [
                    "external_id"       => "234567",
                    "name_first"        => "Marie",
                    "name_suffix"       => "",
                    "name"              => "Marien",
                    "username"          => "marien@hotmail.com",
                    "school_class_name" => "Biologie"
                ],
                [
                    "external_id"       => "32134",
                    "name_first"        => "Piet",
                    "name_suffix"       => "",
                    "name"              => "Pietersen",
                    "username"          => "pietersen@hotmail.com",
                    "school_class_name" => "Klas1"
                ],
                [
                    "external_id"       => "23432",
                    "name_first"        => "Karin",
                    "name_suffix"       => "",
                    "name"              => "Scholten",
                    "username"          => "scholten@hotmail.com",
                    "school_class_name" => "Klas1"
                ],
                [
                    "external_id"       => "5432",
                    "name_first"        => "Klaas",
                    "name_suffix"       => "",
                    "name"              => "Klaassen",
                    "username"          => "klaassen@hotmail.com",
                    "school_class_name" => "Klas1"
                ]
            ],
        ];
    }


    private function classImportScenarioDataProvider(): array
    {
        return [
            1  => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S-1-20221109E2',
                        'name_first'        => 'S-1-20221109E2',
                        'name_suffix'       => 'TV-1-20221109E2',
                        'name'              => 'AchterN-1-20221109E2',
                        'username'          => 'carloschoep+S-1-20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ],
                    [
                        'external_id'       => 'STUDENTNR S-2-20221109E2',
                        'name_first'        => 'S-2-20221109E2',
                        'name_suffix'       => 'TV-2-20221109E2',
                        'name'              => 'AchterN-2-20221109E2',
                        'username'          => 'carloschoep+S-2-20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ]
                ],
                'statusCode' => 200,
            ],
            2  => [
                'data'       => [
                    [
                        'external_id'       => 'ANDERSTUDENTNR S-1-20221109E2',
                        'name_first'        => 'S-1-20221109E2',
                        'name_suffix'       => 'TV-1-20221109E2',
                        'name'              => 'AchterN-1-20221109E2',
                        'username'          => 'carloschoep+S-1-20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ],
                    [
                        'external_id'       => 'ANDERSTUDENTNR S-2-20221109E2',
                        'name_first'        => 'S-2-20221109E2',
                        'name_suffix'       => 'TV-2-20221109E2',
                        'name'              => 'AchterN-2-20221109E2',
                        'username'          => 'carloschoep+S-2-20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ]
                ],
                'statusCode' => 422,
            ],
            3  => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S-1-20221109E2',
                        'name_first'        => 'S-1-20221109E2',
                        'name_suffix'       => 'TV-1-20221109E2',
                        'name'              => 'AchterN-1-20221109E2',
                        'username'          => 'carloschoep+ANDERS-1-20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ],
                    [
                        'external_id'       => 'STUDENTNR S-2-20221109E2',
                        'name_first'        => 'S-2-20221109E2',
                        'name_suffix'       => 'TV-2-20221109E2',
                        'name'              => 'AchterN-2-20221109E2',
                        'username'          => 'carloschoep+ANDERS-2-20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ]
                ],
                'statusCode' => 422,
            ],
            5  => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S-1-20221109E2',
                        'name_first'        => 'S-1-20221109E2',
                        'name_suffix'       => 'TV-1-20221109E2',
                        'name'              => 'AchterN-1-20221109E2',
                        'username'          => 'carloschoep+S-1-20221109E2@hotmail.com',
                        'school_class_name' => 'ANDEREKLASIMPORTER',
                    ],
                    [
                        'external_id'       => 'STUDENTNR S-2-20221109E2',
                        'name_first'        => 'S-2-20221109E2',
                        'name_suffix'       => 'TV-2-20221109E2',
                        'name'              => 'AchterN-2-20221109E2',
                        'username'          => 'carloschoep+S-2-20221109E2@hotmail.com',
                        'school_class_name' => 'ANDEREKLASIMPORTER',
                    ]
                ],
                'statusCode' => 200,
            ],
            6  => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S--20221109E2',
                        'name_first'        => 'S--20221109E2',
                        'name_suffix'       => 'TV--20221109E2',
                        'name'              => 'AchterN--20221109E2',
                        'username'          => 'carloschoep+S--20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ],
                    [
                        'external_id'       => 'STUDENTNR S--20221109E2',
                        'name_first'        => 'S--20221109E2',
                        'name_suffix'       => 'TV--20221109E2',
                        'name'              => 'AchterN--20221109E2',
                        'username'          => 'carloschoep+S--20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ]
                ],
                'statusCode' => 422,
            ],
            7  => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S--20221109E2',
                        'name_first'        => 'S--20221109E2',
                        'name_suffix'       => 'TV--20221109E2',
                        'name'              => 'AchterN--20221109E2',
                        'username'          => 'carloschoep+S--20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ],
                    [
                        'external_id'       => 'ANDERSTUDENTNR S--20221109E2',
                        'name_first'        => 'S--20221109E2',
                        'name_suffix'       => 'TV--20221109E2',
                        'name'              => 'AchterN--20221109E2',
                        'username'          => 'carloschoep+S--20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ]
                ],
                'statusCode' => 422,
            ],
            8  => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S--20221109E2',
                        'name_first'        => 'S--20221109E2',
                        'name_suffix'       => 'TV--20221109E2',
                        'name'              => 'AchterN--20221109E2',
                        'username'          => 'carloschoep+S--20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ],
                    [
                        'external_id'       => 'STUDENTNR S--20221109E2',
                        'name_first'        => 'S--20221109E2',
                        'name_suffix'       => 'TV--20221109E2',
                        'name'              => 'AchterN--20221109E2',
                        'username'          => 'carloschoep+ANDERS--20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ]
                ],
                'statusCode' => 422,
            ],
            11 => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S-1-20221109E2',
                        'name_first'        => 'S-1-20221109E2',
                        'name_suffix'       => 'TV-1-20221109E2',
                        'name'              => 'AchterN-1-20221109E2',
                        'username'          => 'carloschoep+S-1-20221109E2@hotmail.com',
                        'school_class_name' => 'ANDEREKLAS2IMPORTER',
                    ],
                    [
                        'external_id'       => 'STUDENTNR S-1-20221109E2',
                        'name_first'        => 'S-1-20221109E2',
                        'name_suffix'       => 'TV-1-20221109E2',
                        'name'              => 'AchterN-1-20221109E2',
                        'username'          => 'carloschoep+S-1-20221109E2@hotmail.com',
                        'school_class_name' => 'ANDEREKLAS3IMPORTER',
                    ]
                ],
                'statusCode' => 200,
            ],
            12 => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S-1-20221109E2',
                        'name_first'        => 'S-1-20221109E2',
                        'name_suffix'       => 'TV-1-20221109E2',
                        'name'              => 'AchterN-1-20221109E2',
                        'username'          => 'carloschoep+S-1-20221109E2@hotmail.com',
                        'school_class_name' => 'ANDEREKLAS4IMPORTER',
                    ]
                ],
                'statusCode' => 200,
            ],
            14 => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S-1-20221109E2',
                        'name_first'        => 'S-1-20221109E2',
                        'name_suffix'       => 'TV-1-20221109E2',
                        'name'              => 'AchterN-1-20221109E2',
                        'username'          => 'carloschoep+S-1-20221109E2@hotmail.com',
                        'school_class_name' => 'NIETBESTAANDEKLAS',
                    ]
                ],
                'statusCode' => 422,
            ],
            15 => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S-1-20221109E2',
                        'name_first'        => 'S-1-20221109E2',
                        'name_suffix'       => 'ANDERTV-1-20221109E2',
                        'name'              => 'ANDERAchterN-1-20221109E2',
                        'username'          => 'carloschoep+S-1-20221109E2@hotmail.com',
                        'school_class_name' => 'ANDEREKLAS5IMPORTER',
                    ]
                ],
                'statusCode' => 200,
            ],
            16 => [
                'data'       => [
                    [

                        'name_first'        => 'S-3-20221109E2',
                        'name_suffix'       => 'TV-3-20221109E2',
                        'name'              => 'AchterN-3-20221109E2',
                        'username'          => 'carloschoep+S-3-20221109E2@hotmail.com',
                        'school_class_name' => 'KLASIMPORTER',
                    ]
                ],
                'statusCode' => 200,
            ],
            17 => [
                'data'       => [
                    [
                        'external_id'       => 'STUDENTNR S-3-20221109E2',
                        'name_first'        => 'S-3-20221109E2',
                        'name_suffix'       => 'TV-3-20221109E2',
                        'name'              => 'AchterN-3-20221109E2',
                        'username'          => 'carloschoep+S-3-20221109E2@hotmail.com',
                        'school_class_name' => 'ANDEREKLASIMPORTER',
                    ]
                ],
                'statusCode' => 200,
            ],
        ];
    }

    /**
     * @test
     */
    public function can_setup_test_scenario_in_test_db()
    {
        $classCount = SchoolClass::count();
        $classes = FactoryScenarioClassImportCake::create(
            SchoolLocation::find(1)
        )->schoolClasses;

        $this->assertDatabaseCount('school_classes', $classCount + 11);
        $classes->each(function ($class) {
            $this->assertInstanceOf(SchoolClass::class, $class);
        });
    }

    /** @test */
    public function can_get_correct_statuses_per_scenario()
    {
        FactoryScenarioClassImportCake::create(SchoolLocation::find(3));
        $scenarios = $this->classImportScenarioDataProvider();

        collect($scenarios)->each(function ($scenario) use ($scenarios) {
            dump(array_search($scenario, $scenarios));
            $this->post(
                route('school_classes.import_with_classes', ['schoolLocation' => SchoolLocation::find(3)->uuid]),
                static::getSchoolBeheerderAuthRequestData(['data' => $scenario['data']])
            )->assertStatus($scenario['statusCode']);
        });
    }
}
