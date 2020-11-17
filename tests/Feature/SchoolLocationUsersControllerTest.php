<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
use tcCore\User;
use Tests\TestCase;

class SchoolLocationUsersControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_user_can_be_added_to_an_extra_school_location()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function a_user_can_be_removed_from_an_extra_school_location()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function a_user_can_switch_his_active_school_location()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function a_user_cannot_switch_to_a_school_location_where_he_is_not_registered()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function a_user_can_request_a_list_of_school_locations()
    {
        $this->get()

    }


}
