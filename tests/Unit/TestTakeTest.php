<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use tcCore\TestTake;
use tcCore\Text2speech;
use tcCore\Text2speechLog;
use tcCore\User;
use Tests\TestCase;

class TestTakeTest extends TestCase
{

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function get_test_take_planned_for_d1()
    {
        $filters = [
            "test_take_status_id" => "1",
            "invigilator_id"      => "1486",
        ];
        $sorting = [
            "time_start" => "asc",
        ];
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(0, $qb->get());
        $this->assertEquals(
            [1486, 1486, 1486, '1', '1486'],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `test_take_status_id` = ? and `test_takes`.`id` in (select `test_take_id` from `invigilators` where `deleted_at` is null and `user_id` = ?) and `test_takes`.`deleted_at` is null order by `time_start` asc",
            $qb->toSql()
        );
    }

    /** @test */
    public function get_test_take_not_taken_for_d1()
    {
        $filters = [
            "test_take_status_id" => "2",
            "invigilator_id"      => "1486",
        ];
        $sorting = [
            "time_start" => "asc",
        ];
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(0, $qb->get());
        $this->assertEquals(
            [1486, 1486, 1486, '2', '1486'],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `test_take_status_id` = ? and `test_takes`.`id` in (select `test_take_id` from `invigilators` where `deleted_at` is null and `user_id` = ?) and `test_takes`.`deleted_at` is null order by `time_start` asc",
            $qb->toSql()
        );
    }

    /** @test */
    public function get_test_take_taken_or_dicussing_for_d1()
    {
        $filters = [
            "test_take_status_id" => [6, 7],
            "invigilator_id"      => "1486",
        ];
        $sorting = [
            "time_start" => "asc",
        ];
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(1, $qb->get());
        $this->assertEquals(
            [1486, 1486, 1486, 6, 7, '1486'],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `test_take_status_id` in (?, ?) and `test_takes`.`id` in (select `test_take_id` from `invigilators` where `deleted_at` is null and `user_id` = ?) and `test_takes`.`deleted_at` is null order by `time_start` asc",
            $qb->toSql()
        );
    }

    /** @test */
    public function get_test_take_discussed_for_d1()
    {
        $filters = [
            "test_take_status_id" => "8",
            "invigilator_id"      => "1486",
        ];
        $sorting = [
            "time_start" => "asc",
        ];
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(0, $qb->get());
        $this->assertEquals(
            [1486, 1486, 1486, '8', '1486'],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `test_take_status_id` = ? and `test_takes`.`id` in (select `test_take_id` from `invigilators` where `deleted_at` is null and `user_id` = ?) and `test_takes`.`deleted_at` is null order by `time_start` asc",
            $qb->toSql()
        );
    }

    /** @test */
    public function get_test_take_rated_for_d1()
    {
        $filters = [
            "test_take_status_id" => "9",
            "invigilator_id"      => "1486",
        ];
        $sorting = [
            "time_start" => "asc",
        ];
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(2, $qb->get());
        $this->assertEquals(
            [1486, 1486, 1486, '9', '1486'],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `test_take_status_id` = ? and `test_takes`.`id` in (select `test_take_id` from `invigilators` where `deleted_at` is null and `user_id` = ?) and `test_takes`.`deleted_at` is null order by `time_start` asc",
            $qb->toSql()
        );
    }

}