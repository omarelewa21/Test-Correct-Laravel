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

    private $schoolLocationActive;
    private $schoolLocationInActive;
    private Carbon $createdAt7MonthsAgo;
    private Carbon $createdAt13MonthsAgo;
    private Carbon $createdAt23MonthsAgo;
    private Carbon $createdAt25MonthsAgo;


    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->createdAt7MonthsAgo = \Carbon\Carbon::now()->subMonths(7);
        $this->createdAt13MonthsAgo = \Carbon\Carbon::now()->subMonths(13);
        $this->createdAt23MonthsAgo = \Carbon\Carbon::now()->subMonths(23);
        $this->createdAt25MonthsAgo = \Carbon\Carbon::now()->subMonths(25);

        $this->schoolLocationActive = $this->make_school_location(true);

        $this->schoolLocationInActive = $this->make_school_location(false);
    }

    // link to tutorial for this feature https://www.csrhymes.com/2021/01/31/testing-a-laravel-console-command.html

    public function test_teachers_created_seven_months_ago_login_last_seven_months_ago_active_school_false()
    {

        $this->createTeacherFromUser($user, $schoolClass=null);

        $this->artisan('users_one_year_inactive:scheduled_mail');

    }

    public function test_teachers_created_seven_months_ago_login_last_seven_months_ago_active_school_true()
    {

    }

    public function make_school_location(bool $active = true)
    {

        $schoolLocation = SchoolLocation::create([
            "name" => "test schoollocatie",
            "customer_code" => "OV",
            "user_id" => 520,
            "school_id" => School::first()->getKey(),
            "grading_scale_id" => "1",
            "activated" => $active,
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

        return $schoolLocation;
    }

    public function make_user_custom_created_date_school_location_username($createdAt, $schoolLocation, $userName = 'teacher')
    {
        $password = 'password';

        $user = User::create([
            'school_location_id' => $schoolLocation,
            'username'           => sprintf('%s-%s@example.com', \Hash::make($this->schoolLocation->name), $userName),
            'password'           => \Hash::make($password),
            'name_first'         => $this->schoolLocation->name,
            'name'               => sprintf('teacher'),
            'api_key'            => sha1(time()),
            'send_welcome_email' => 1,
            'created_at'       => $createdAt,
        ]);

        return $user;
    }
}

