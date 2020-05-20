<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
use tcCore\User;
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

        $response->assertStatus(201);
        $this->assertCount(
            1,
            User::where('username', 'jan.janssen@sobit.nl')->get()
        );

        $this->assertEquals(($startCountTeachers + 1), \tcCore\Teacher::count());
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
                "notes"        => "OK",
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
        "notes": "OK",
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
        "notes": "OK",
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
        "notes": "OK",
        "class_list": "A1",
        "subject_list": "Nederlands"
    }]');

    }
}
