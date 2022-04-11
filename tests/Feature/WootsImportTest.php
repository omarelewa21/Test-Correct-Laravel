<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use tcCore\DemoTeacherRegistration;
use tcCore\EducationLevel;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\Mail\TeacherRegistered;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\OpenQuestionTrait;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Http\Helpers\SchoolHelper;

class WootsImportTest extends TestCase
{
//    use DatabaseTransactions;
//
    /** @test */
    public function a_manager_can_upload_a_zip_file()
    {
        $accountManager = User::whereUsername('accountmanager@test-correct.nl')->first();
        $filename = 'NBO voorronde 20_21-qti.zip';
        $stub = base_path('tests/_fixtures_woots_qti/'.$filename);
        $path = sys_get_temp_dir().'/'.$filename;

        copy($stub, $path);

        $response = $this->post(
            route(
                'qtiimportcito_import'
            ),
            static::getUserAuthRequestData($accountManager, [
                "school_location_id"   => SchoolLocation::find(1)->uuid,
                "author_id"            => User::find(1496)->uuid,
                "subject_id"           => Subject::find(6)->uuid,
                "education_level_id"   => EducationLevel::find(9)->uuid,
                "education_level_year" => 1,
                "test_kind_id"         => "2",
                "abbr"                 => "ABC",
                "period_id"            => 1,
                'zip_file'             => new \Illuminate\Http\UploadedFile(
                    $path,
                    $filename,
                    'zip',
                    null,
                    true
                ),
            ])
        );

        $response->decodeResponseJson();

        $this->assertStringContainsString(
            'De import is succesvol verlopen!',
            $response->decodeResponseJson()['data']
        );

        $lastTest = Test::orderBy('id', 'desc')->first();
        $this->assertEquals('NBO voorronde 20_21-qti',$lastTest->name);
        $this->assertEquals('ABC',$lastTest->abbreviation);
        $this->assertEmpty($lastTest->scope);
    }

    /** @test */
    public function a_manager_can_upload_a_cito_zip_file()
    {
        $accountManager = User::whereUsername('accountmanager@test-correct.nl')->first();
        $filename = 'economie-VMBO_pakket_test-correct_20200827-212636.zip';
        $stub = base_path('tests/_fixtures_qti/economie-wiskundeA-niet-definitief/Economie-VMBO/'.$filename);
        $path = sys_get_temp_dir().'/'.$filename;

        copy($stub, $path);

        $response = $this->post(
            route(
                'qtiimportcito_import'
            ),
            static::getUserAuthRequestData($accountManager, [
                "school_location_id"   => SchoolLocation::find(1)->uuid,
                "author_id"            => User::find(1496)->uuid,
                "subject_id"           => Subject::find(6)->uuid,
                "education_level_id"   => EducationLevel::find(9)->uuid,
                "education_level_year" => 1,
                "test_kind_id"         => "2",
                "abbr"                 => "",
                "period_id"            => 1,
                'zip_file'             => new \Illuminate\Http\UploadedFile(
                    $path,
                    $filename,
                    'zip',
                    null,
                    true
                ),
            ])
        );

        $this->assertStringContainsString(
            'De import is succesvol verlopen!',
            $response->decodeResponseJson()['data']
        );
    }
}
