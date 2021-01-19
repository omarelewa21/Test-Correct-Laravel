<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
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
        
        $this->assertEquals(422, $response->getStatusCode());
         
        $decodedResponse = $response->decodeResponseJson();
         
        $this->assertArrayHasKey('data.0.username', $decodedResponse['errors']);
        $this->assertEquals('The data.0.username must be a valid email address.', $decodedResponse['errors']['data.0.username'][0]);
        $this->assertEquals('The email address contains international characters.', $decodedResponse['errors']['data.0.username'][1]);

    }
    
        /** @test */
    public function it_can_import_a_user_rejects_international_characters_various() {
        
        $this->assertCount(0, User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->get());

        $countStudentsBefore = \tcCore\Student::count();

        $response = $this->post(
                route(
                        'school_classes.import', [
            'schoolLocation' => SchoolLocation::find(3)->uuid,
            'schoolClass' => SchoolClass::find(3)->uuid
                ])
                , static::getSchoolBeheerderAuthRequestData([
                    'data' => [[
                    'external_id' => "123451",
                    'name_first' => "Jan1",
                    'name_suffix' => "",
                    'name' => "Janssen1",
                    'username' => "140965ä@test-correct.nl"
                        ]]
        ]));
        
        $this->assertEquals(422, $response->getStatusCode());

        $response = $this->post(
                route(
                        'school_classes.import', [
            'schoolLocation' => SchoolLocation::find(3)->uuid,
            'schoolClass' => SchoolClass::find(3)->uuid
                ])
                , static::getSchoolBeheerderAuthRequestData([
                    'data' => [[
                    'external_id' => "123452",
                    'name_first' => "Jan2",
                    'name_suffix' => "",
                    'name' => "Janssen2",
                    'username' => "140907ö@test-correct.nl"
        ]]]));
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $response = $this->post(
                route(
                        'school_classes.import', [
            'schoolLocation' => SchoolLocation::find(3)->uuid,
            'schoolClass' => SchoolClass::find(3)->uuid
                ])
                , static::getSchoolBeheerderAuthRequestData([
                    'data' => [[
                    'external_id' => "123453",
                    'name_first' => "Jan3",
                    'name_suffix' => "",
                    'name' => "Janssen3",
                    'username' => "140990û@test-correct.nl"
        ]]]));
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $response = $this->post(
                route(
                        'school_classes.import', [
            'schoolLocation' => SchoolLocation::find(3)->uuid,
            'schoolClass' => SchoolClass::find(3)->uuid
                ])
                , static::getSchoolBeheerderAuthRequestData([
                    'data' => [[
                    'external_id' => "123454",
                    'name_first' => "Jan4",
                    'name_suffix' => "",
                    'name' => "Janssen4",
                    'username' => "140922é@test-correct.nl",
        ]]]));
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $response = $this->post(
                route(
                        'school_classes.import', [
            'schoolLocation' => SchoolLocation::find(3)->uuid,
            'schoolClass' => SchoolClass::find(3)->uuid
                ])
                , static::getSchoolBeheerderAuthRequestData([
                    'data' => [[
                    'external_id' => "123455",
                    'name_first' => "Jan5",
                    'name_suffix' => "",
                    'name' => "Janssen5",
                    'username' => "140991è@test-correct.nl",
        ]]]));
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $response = $this->post(
                route(
                        'school_classes.import', [
            'schoolLocation' => SchoolLocation::find(3)->uuid,
            'schoolClass' => SchoolClass::find(3)->uuid
                ])
                , static::getSchoolBeheerderAuthRequestData([
                    'data' => [[
                    'external_id' => "123456",
                    'name_first' => "Jan6",
                    'name_suffix' => "",
                    'name' => "Janssen6",
                    'username' => "140878á@test-correct.nl",
        ]]]));
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $response = $this->post(
                route(
                        'school_classes.import', [
            'schoolLocation' => SchoolLocation::find(3)->uuid,
            'schoolClass' => SchoolClass::find(3)->uuid
                ])
                , static::getSchoolBeheerderAuthRequestData([
                    'data' => [[
                    'external_id' => "123457",
                    'name_first' => "Jan7",
                    'name_suffix' => "",
                    'name' => "Janssen7",
                    'username' => "141507ë@test-correct.nl",
        ]]]));
        
        $this->assertEquals(422, $response->getStatusCode());
   
        $response = $this->post(
        route(
        'school_classes.import', [
        'schoolLocation' => SchoolLocation::find(3)->uuid,
        'schoolClass' => SchoolClass::find(3)->uuid
        ])
        , static::getSchoolBeheerderAuthRequestData([
        'data' => [[
        'external_id' => "123459",
        'name_first' => "Jan9",
        'name_suffix' => "",
        'name' => "Janssen9",
        'username' => "1405&#x26;52@test-correct.nl",
        ]]]));

        $this->assertEquals(422, $response->getStatusCode());
        
        $response = $this->post(
                route(
                        'school_classes.import', [
            'schoolLocation' => SchoolLocation::find(3)->uuid,
            'schoolClass' => SchoolClass::find(3)->uuid
                ])
                , static::getSchoolBeheerderAuthRequestData([
                    'data' => [[
                    'external_id' => "123458",
                    'name_first' => "Jan8",
                    'name_suffix' => "",
                    'name' => "Janssen8",
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
        $this->assertEquals('1 studenten zijn toegevoegd', $response->decodeResponseJson());

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
                'external_id' => "12345",
                'name_first'  => "Jan",
                'name_suffix' => "",
                'name'        => "Janssen",
                'username'    => "carloschoep+K999jjanssen@hotmail.com",
                'school_class_name'=>"Biologie"
            ]],
        ]))->assertSuccessful();
        $this->assertEquals('1 studenten zijn toegevoegd', $response->decodeResponseJson());

        $this->assertCount(1, User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->get());
        $this->assertCount(++$countStudentsBefore, Student::get());
        $this->assertEquals(
            8,
            User::whereUsername("carloschoep+K999jjanssen@hotmail.com")->first()->students()->first()->class_id
        );
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
        $this->assertEquals('2 studenten zijn toegevoegd', $response->decodeResponseJson());

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
            'data.0.username'   => 'Deze import bevat dubbele emailadressen',
            'data.1.username'   => 'Deze import bevat dubbele emailadressen',
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
            'data' => [ [
                'external_id' => "12346",
                'name_first'  => "Janus",
                'name_suffix' => "",
                'name'        => "Janssens",
                'username'    => "carloschoep+K999jjanssen@hotmail.com",
            ]],
        ]))->assertStatus(422);

        $decodedResponse = $response->decodeResponseJson();
        $requiredErrors = [
            'data.0.username'   => 'The data.0.username has already been taken.',
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
            'data.0.external_id'   => 'Deze import bevat dubbele studentennummers',
            'data.1.external_id'   => 'Deze import bevat dubbele studentennummers',
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
        $user = User::where('username','=',static::USER_SCHOOLBEHEERDER)->get()->first();
        $user->school_location_id =1;
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
        $user = User::where('username','=',static::USER_SCHOOLBEHEERDER)->get()->first();
        $user->school_location_id =1;
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
            ], ],
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
            ], ],
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
            ], ],
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
            ], ],
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
            ], ],
        ]))->assertSuccessful();

        $response = $this->post(
            route(
                'school_classes.import', [
                'schoolLocation' => SchoolLocation::find(3)->uuid,
                'schoolClass'    => SchoolClass::find(3)->uuid
            ])
            , static::getSchoolBeheerderAuthRequestData([
            'data' => [  [
                'external_id' => "12345",
                'name_first'  => "Janus",
                'name_suffix' => "",
                'name'        => "oepsi",
                'username'    => "other@hotmail.com",
            ], ],
        ]))->assertStatus(422);

        $decodedResponse = $response->decodeResponseJson();
        $requiredErrors = [
            'data.0.external_id'   => 'The data.0.external id has already been taken.',
        ];

        foreach ($requiredErrors as $errorField => $errorMessage) {
            $this->assertArrayHasKey($errorField, $decodedResponse['errors']);
            $this->assertEquals($errorMessage, $decodedResponse['errors'][$errorField][0]);
        }
    }

    /** @test */
    public function it_should_return_required_column_validation_errors_for_required_fields()
    {
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
        ]))->assertStatus(422);
        $decodedResponse = $response->decodeResponseJson();

        $requiredErrors = [
            'data.0.username'   => 'The data.0.username field is required.',
            'data.0.name_first' => 'The data.0.name_first field is required.',
            'data.0.name'       => 'The data.0.name field is required.',
        ];

        foreach ($requiredErrors as $errorField => $errorMessage) {
            $this->assertArrayHasKey($errorField, $decodedResponse['errors']);
            $this->assertEquals($errorMessage, $decodedResponse['errors'][$errorField][0]);
        }
    }
}
