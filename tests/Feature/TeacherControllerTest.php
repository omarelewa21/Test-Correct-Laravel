<?php

/*
 * Business rules
 * TeacherController@import
 * Zelfde e-mails maar met ander stamnummer in deze schoollocatie --> fout geven
 * Zelfde stamnummers maar met ander e-mail in deze schoollocatie --> fout geven
 * Als 2 * e-mail met andere stamnummers in het bestand --> fout
 * Als 2 * stamnummers met andere e-mail in het bestand --> fout
 * Controle of klas bestaat --> zo niet --> fout
 * Controle of vak bestaat --> zo niet, foutmelding.
 * Als je een bestaande docent met hetzelfde stamnummer en e-mail met ander vak en/of klas upload, dan moet dat werken. Er moet dan een tweede (model) Teacher komen
 * Geen update van andere gegevens doen (b.v. voornaam/achternaam/tussenvoegsels/afkorting/notities)
 *
 */



use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
use tcCore\User;
use tcCore\Period;
use tcCore\Teacher;
use tcCore\Subject;
use tcCore\SchoolClass;
use tcCore\SchoolYear;
use Tests\TestCase;

class TeacherControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_teacher_can_be_imported()
    {
        $this->assertCount(
            0,
            User::where('username', 'jan.janssen@sobit.nl')->get()
        );

        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequest(route('teacher.import')),
            ['data' => $this->getData()]
        );
        $response->assertStatus(200);
        $this->assertCount(
            1,
            User::where('username', 'jan.janssen@sobit.nl')->get()
        );

        $this->assertEquals(($startCountTeachers + 2), \tcCore\Teacher::count());
    }

    /** @test */
    public function it_should_contain_a_valid_class_name()
    {
        $response = $this->postJson(
            static::AuthBeheerderGetRequest(route('teacher.import')),
            ['data' => $this->getData([
                'school_class' => 'some_bogus_name',
            ])]
        );
        $response->assertStatus(422);
        $json = $response->decodeResponseJson();
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('data.0.school_class', $json['errors']);
        $this->assertEquals(
            'de opgegeven klas dient in de database aanwezig te zijn voor deze schoollocatie',
            $json['errors']['data.0.school_class'][0]
        );
    }

    /** @test */
    public function it_should_contain_a_valid_subject()
    {
        $response = $this->postJson(
            static::AuthBeheerderGetRequest(route('teacher.import')),
            ['data' => $this->getData([
                'subject' => 'some_bogus_subject',
            ])]
        );
        $response->assertStatus(422);
        $json = $response->decodeResponseJson();
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('data.0.subject', $json['errors']);
        $this->assertEquals(
            'het opgegeven vak dient in de database aanwezig te zijn voor deze schoollocatie',
            $json['errors']['data.0.subject'][0]
        );
    }

    /** @test */
    public function school_class_is_validated_by_period(){
        $schoolbeheerder = $this->getSchoolBeheerder();
        $schoolClass = SchoolClass::where('school_location_id',$schoolbeheerder->school_location_id)->first();
        $teacher = Teacher::where('class_id',$schoolClass->id)->first();
        $subject = Subject::where('id',$teacher->subject_id)->first();

        $period = Period::where('school_year_id',$schoolClass->school_year_id);
        $period->update([   'start_date'         => \Carbon\Carbon::now()->subMonths(6),
                            'end_date'           => \Carbon\Carbon::now()->addMonths(6),
                        ]);
        $schoolYear = SchoolYear::find($schoolClass->school_year_id);
        $schoolYear->year = \Carbon\Carbon::now()->subYear(1)->format('Y');
        $schoolYear->save();
        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation3(route('teacher.import')),
            ['data' => $this->getData([
                "school_class" => $schoolClass->name,
                "subject"      => "Vak biologie"
            ])]
        );
        //dump($response->decodeResponseJson());
        $response->assertStatus(200);
    }

    /** @test */
    public function school_class_validation_fails_by_incorrect_period(){
        $schoolbeheerder = $this->getSchoolBeheerder();
        $schoolClass = SchoolClass::where('school_location_id',$schoolbeheerder->school_location_id)->first();
        $teacher = Teacher::where('class_id',$schoolClass->id)->first();
        $subject = Subject::where('id',$teacher->subject_id)->first();

        $period = Period::where('school_year_id',$schoolClass->school_year_id);
        $period->update([   'start_date'         => \Carbon\Carbon::now()->subMonths(6),
                            'end_date'           => \Carbon\Carbon::now()->subMonths(3),
                        ]);
        $schoolYear = SchoolYear::find($schoolClass->school_year_id);
        $schoolYear->year = \Carbon\Carbon::now()->format('Y');
        $schoolYear->save();
        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation3(route('teacher.import')),
            ['data' => $this->getData([
                "school_class" => $schoolClass->name,
                "subject"      => "Vak biologie"
            ])]
        );
        $response->assertStatus(422);
    }

    /** @test */
    public function teacher_import_in_two_schoollocations_gives_two_teachers()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer()]
        );

        $response->assertStatus(200);
        $this->assertCount(
            1,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        //$user = User::where('username', 'bobdebouwer@test-correct.nl')->first();
        //$this->assertEquals(($startCountTeachers + 1), \tcCore\Teacher::count());
        $this->assertGreaterThan($startCountTeachers,\tcCore\Teacher::count());
        $secondStartCountTeachers = \tcCore\Teacher::count();
        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation3(route('teacher.import')),
            ['data' => $this->getDataLocation3BobDeBouwer()]
        );
        $response->assertStatus(200);
        $this->assertEquals(($secondStartCountTeachers + 1), \tcCore\Teacher::count());

    }

    /** @test */
    public function teacher_import_in_one_schoollocations_two_classes_subjects_gives_two_teachers()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer()]
        );
        $response->assertStatus(200);
        $this->assertCount(
            1,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $this->assertGreaterThan($startCountTeachers,\tcCore\Teacher::count());
        $secondStartCountTeachers = \tcCore\Teacher::count();
        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer2()]
        );
        //dump($response->decodeResponseJson());
        $response->assertStatus(200);
        $this->assertEquals(($secondStartCountTeachers + 1), \tcCore\Teacher::count());
    }

    /** @test */
    public function teacher_import_same_username_different_external_id_same_schoollocation_must_fail()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1SameUsernameDifferentExternalId()]
        );
        //dump($response->getContent());
        $response->assertStatus(422);


    }

    /** @test */
    public function teacher_import_same_external_id_different_username_same_schoollocation_must_fail()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1SameExternalIdDifferentUsername()]
        );
        //dump($response->getContent());
        $response->assertStatus(422);
    }

    /** @test */
    public function teacher_import_same_username_same_external_id_same_schoollocation_same_schoolclass_same_subject_must_fail()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1SameUsernameSameExternalIdSameSchoolclassSameSubject()]
        );
        $response->assertStatus(422);
        //dump($response->decodeResponseJson());

    }

    /** @test */
    public function teacher_import_same_username_different_external_id_same_schoollocation_two_imports_must_fail()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer()]
        );
        $response->assertStatus(200);
        $this->assertCount(
            1,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $this->assertGreaterThan($startCountTeachers,\tcCore\Teacher::count());
        $secondStartCountTeachers = \tcCore\Teacher::count();
        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer3()]
        );
        $response->assertStatus(422);
        //dump($response->decodeResponseJson());
    }

    /** @test */
    public function teacher_import_same_external_id_different_username_same_schoollocation_two_imports_must_fail()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer()]
        );
        $response->assertStatus(200);
        $this->assertCount(
            1,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $this->assertGreaterThan($startCountTeachers,\tcCore\Teacher::count());
        $secondStartCountTeachers = \tcCore\Teacher::count();
        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer4()]
        );
        $response->assertStatus(422);
        //dump($response->decodeResponseJson());
    }

    /** @test */
    public function teacher_import_subject_does_not_exist_must_fail()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1NonexistingSubject()]
        );
        $response->assertStatus(422);
        //dump($response->decodeResponseJson());

    }

    /** @test */
    public function teacher_import_schoolclass_does_not_exist_must_fail()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1NonexistingSchoolclass()]
        );
        $response->assertStatus(422);
        //dump($response->decodeResponseJson());

    }

    /** @test */
    public function second_teacher_import_in_one_schoollocations_does_not_update_user_data()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer()]
        );
        $response->assertStatus(200);
        $this->assertCount(
            1,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $this->assertGreaterThan($startCountTeachers,\tcCore\Teacher::count());
        $secondStartCountTeachers = \tcCore\Teacher::count();
        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer5()]
        );
        //dump($response->decodeResponseJson());
        $response->assertStatus(200);
        $this->assertEquals(($secondStartCountTeachers + 1), \tcCore\Teacher::count());
        $bob = User::where('username', 'bobdebouwer@test-correct.nl')->first();
        $this->assertEquals('Bob',$bob->name_first );
        $this->assertEquals('de',$bob->name_suffix );
        $this->assertEquals('Bouwer',$bob->name );
        $this->assertEquals('bdb',$bob->abbreviation );
        $this->assertEquals('testnotities',$bob->note );
    }

    /** @test */
    public function teacher_import_same_username_same_external_id_same_schoollocation_same_subject_same_schoolclass_two_imports_must_fail()
    {
        $this->assertCount(
            0,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $startCountTeachers = \tcCore\Teacher::count();

        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer()]
        );
        $response->assertStatus(200);
        $this->assertCount(
            1,
            User::where('username', 'bobdebouwer@test-correct.nl')->get()
        );
        $this->assertGreaterThan($startCountTeachers,\tcCore\Teacher::count());
        $secondStartCountTeachers = \tcCore\Teacher::count();
        $response = $this->postJson(
            static::AuthBeheerderGetRequestLocation1(route('teacher.import')),
            ['data' => $this->getDataLocation1BobDeBouwer()]
        );
        $response->assertStatus(422);
        //dump($response->decodeResponseJson());
    }



    private function getData($overrides = [])
    {
        return [
            (object)array_merge([
                "6"            => "",
                "name_first"   => "Jan",
                "name_suffix"  => "van",
                "name"         => "Janssen",
                "abbrviation"  => "JJ",
                "username"     => "jan.janssen@sobit.nl",
                "note"        => "OK",
                "school_class" => "Klas1",
                "subject"      => "Nederlands"
            ],
                $overrides)
        ];


    }


    private function getDataThreeItems()
    {
        return json_decode('[{
        "6": "",
        "name_first": "Jan",
        "name_suffix": "van",
        "name": "Janssen",
        "abbrviation": "JJ",
        "username": "jan.janssen@sobit.nl",
        "password": "password",
        "note": "OK",
        "class_list": "A1",
        "subject_list": "Nederlands"
    },{
        "6": "",
        "name_first": "Martin",
        "name_suffix": "",
        "name": "Folkerts",
        "abbrviation": "MF",
        "username": "",
        "password": "password",
        "note": "OK",
        "class_list": "A1",
        "subject_list": "Duits"
    },{
        "6": "",
        "name_first": "Erik",
        "name_suffix": "",
        "name": "",
        "abbrviation": "ED",
        "username": "erik@sobit.nl",
        "password": "password",
        "note": "OK",
        "class_list": "A1",
        "subject_list": "Nederlands"
    }]');
    }

    private function getDataLocation1BobDeBouwer()
    {
        return json_decode( '[
            {
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Klas1",
                "subject": "Nederlands",
                "class_id": 1,
                "subject_id": 1
            }
        ]');
    }

    private function getDataLocation1BobDeBouwer2()
    {
        return json_decode( '[
            {
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Klas 19",
                "subject": "Biologie",
                "class_id": 19,
                "subject_id": 4
            }
        ]');
    }

    private function getDataLocation1BobDeBouwer3()
    {
        return json_decode( '[
            {
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "124356789",
                "note": "testnotities",
                "school_class": "Klas 19",
                "subject": "Biologie",
                "class_id": 19,
                "subject_id": 4
            }
        ]');
    }

    private function getDataLocation1BobDeBouwer4()
    {
        return json_decode( '[
            {
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer2@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Klas 19",
                "subject": "Biologie",
                "class_id": 19,
                "subject_id": 4
            }
        ]');
    }

    private function getDataLocation1BobDeBouwer5()
    {
        return json_decode( '[
            {
                "name_first": "Jantje",
                "name_suffix": "van",
                "name": "Leiden",
                "abbreviation": "jvl",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities2",
                "school_class": "Klas 19",
                "subject": "Biologie",
                "class_id": 19,
                "subject_id": 4
            }
        ]');
    }

    private function getDataLocation3BobDeBouwer()
    {
        return json_decode( '[
            {
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Biologie",
                "subject": "Vak Biologie",
                "class_id": 3,
                "subject_id": 4
            }
        ]');
    }

    private function getDataLocation1SameUsernameDifferentExternalId()
    {
        return json_decode('[{
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Klas1",
                "subject": "Nederlands",
                "class_id": 1,
                "subject_id": 1
            },{
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "124356789",
                "note": "testnotities",
                "school_class": "Klas 19",
                "subject": "Biologie",
                "class_id": 19,
                "subject_id": 4
            }]');
    }

    private function getDataLocation1SameExternalIdDifferentUsername()
    {
        return json_decode('[{
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer1@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Klas1",
                "subject": "Nederlands",
                "class_id": 1,
                "subject_id": 1
            },{
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer2@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Klas 19",
                "subject": "Biologie",
                "class_id": 19,
                "subject_id": 4
            }]');
    }

    private function getDataLocation1SameUsernameSameExternalIdSameSchoolclassSameSubject()
    {
        return json_decode('[{
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Klas1",
                "subject": "Nederlands",
                "class_id": 1,
                "subject_id": 1
            },{
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Klas1",
                "subject": "Nederlands",
                "class_id": 1,
                "subject_id": 1
            }]');
    }

    private function getDataLocation1NonexistingSubject()
    {
        return json_decode( '[
            {
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Klas1",
                "subject": "joepie de poepie",
                "class_id": 1,
                "subject_id": 1
            }
        ]');
    }

    private function getDataLocation1NonexistingSchoolclass()
    {
        return json_decode( '[
            {
                "name_first": "Bob",
                "name_suffix": "de",
                "name": "Bouwer",
                "abbreviation": "bdb",
                "username": "bobdebouwer@test-correct.nl",
                "external_id": "12435678",
                "note": "testnotities",
                "school_class": "Joepie de poepie",
                "subject": "Nederlands",
                "class_id": 1,
                "subject_id": 1
            }
        ]');
    }
}
