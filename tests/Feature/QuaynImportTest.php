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
use tcCore\TestQuestion;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\OpenQuestionTrait;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Http\Helpers\SchoolHelper;

class QuaynImportTest extends TestCase
{
//    use DatabaseTransactions;
//
    /** @test */
    public function a_manager_can_upload_a_zip_file()
    {
        $accountManager = User::whereUsername('accountmanager@test-correct.nl')->first();
        $filename = 'Nova_1-2kgt_h2_et-b_quayn.zip';
        $stub = base_path('tests/_fixtures_quayn_qti/'.$filename);
        $path = sys_get_temp_dir().'/'.$filename;

        copy($stub, $path);

        $response = $this->post(
            route(
                'qtiimport_import'
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
