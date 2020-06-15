<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestingControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_should_refresh_the_database_using_the_requested_flag()
    {
        dd($this->post(route('testing.store', static::getTeacherOneAuthRequestData(['flag' => 'create-test-create-questions']))));
    }
}
