<?php


namespace Tests\Unit\Http\Helpers;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\Http\Helpers\MigrateUserService;
use tcCore\SamlMessage;
use tcCore\SchoolLocation;
use tcCore\User;
use Tests\TestCase;

class MigrateUserServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function it_should_error_when_both_ids_are_identical()
    {
        $this->assertEquals(
            'An error occured: oldId can not be Id (1, 1)',
            (new MigrateUserService(1, 1))->handle()
        );
    }

    /**
     * @test
     */
    public function it_should_error_when_oldId_is_bigger_then_id()
    {
        $this->assertEquals(
            'An error occured: oldId should be smaller then the new Id (2, 1)',
            (new MigrateUserService(2, 1))->handle()
        );
    }

    /**
     * @test
     */
    public function it_should_error_when_oldId_is_not_a_propper_user_id()
    {
        $this->assertEquals(
            'An error occured: oldId 10 is not a propper user_id',
            (new MigrateUserService(10, 11))->handle()
        );
    }

    /** @test */
    public function it_should_error_when_id_is_not_a_propper_user_id()
    {
        $this->assertEquals(
            'An error occured: id 11111 is not a propper user_id',
            (new MigrateUserService(1483, 11111))->handle()
        );
    }
}
