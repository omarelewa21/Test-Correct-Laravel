<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Lib\User\Factory;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;
use Tests\TestCase;

class ScheduleMailToUsersOneYearInactiveTest extends TestCase
{

    private $schoolLocation;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->schoolLocation = SchoolLocation::create([
            "name" => "test schoollocatie",
            "customer_code" => "OV",
            "user_id" => 520,
            "school_id" => School::first()->getKey(),
            "grading_scale_id" => "1",
            "activated" => "1",
            "number_of_students" => "10",
            "number_of_teachers" => "10",
            "external_main_code" => "06SS",
            "external_sub_code" => "00",
            "is_rtti_school_location" => "0",
            "is_open_source_content_creator" => "0",
            "is_allowed_to_view_open_source_content" => "0",
            "main_address" => "AgrobusinessPark 75",
            "invoice_address" => "AgrobusinessPark",
            "visit_address" => "AgrobusinessPark",
            "main_postal" => "6708PV",
            "invoice_postal" => "6708PV",
            "visit_postal" => "6708PV",
            "main_city" => "Wageningen",
            "invoice_city" => "Wageningen",
            "visit_city" => "Wageningen",
            "main_country" => "Netherlands",
            "invoice_country" => "Netherlands",
            "visit_country" => "Netherlands",
            "lvs_active" => true,
            "lvs_type" => \tcCore\SchoolLocation::LVS_SOMTODAY,
        ]);
    }

    // link to tutorial for this feature https://www.csrhymes.com/2021/01/31/testing-a-laravel-console-command.html

    public function test_teachers_created_seven_months_ago_login_last_seven_months_ago_active_school_false()
    {
        $createdAt= \Carbon\Carbon::now();
        $password = 'password';

        $user = User::create([
            'school_location_id' => $this->schoolLocation->getKey(),
            'username'           => sprintf('%s-teacher@example.com', \Hash::make($this->schoolLocation->name)),
            'password'           => \Hash::make($password),
            'name_first'         => $this->schoolLocation->name,
            'name'               => sprintf('teacher'),
            'api_key'            => sha1(time()),
            'send_welcome_email' => 1,
            'created_at'       => '',
        ]);

        $this->expectException(RuntimeException::class);
        $this->artisan('import:products');

        $this->createTeacherFromUser($user, $schoolClass=null);
    }

    public function test_teachers_created_seven_months_ago_login_last_seven_months_ago_active_school_true()
    {

    }
}
