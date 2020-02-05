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

class TestTakesTest extends TestCase
{

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function load_planned_teacher_for_d1()
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
    public function surveillance_for_d1()
    {
        $filters = [
            "test_take_status_id" => "3",
            "invigilator_id"      => "1486",
            "mode"                => "list",
        ];
        $sorting = [
            "time_start" => "asc",
        ];
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(0, $qb->get());
        $this->assertEquals(
            [1486, 1486, 1486, '3', '1486'],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `test_take_status_id` = ? and `test_takes`.`id` in (select `test_take_id` from `invigilators` where `deleted_at` is null and `user_id` = ?) and `test_takes`.`deleted_at` is null order by `time_start` asc",
            $qb->toSql()
        );
    }

    /** @test */
    public function load_taken_teacher_for_d1()
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
    public function load_to_rate_for_d1()
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
    public function load_rated_for_d1()
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

        $this->assertCount(1, $qb->get());
        $this->assertEquals(
            [1486, 1486, 1486, '9', '1486'],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `test_take_status_id` = ? and `test_takes`.`id` in (select `test_take_id` from `invigilators` where `deleted_at` is null and `user_id` = ?) and `test_takes`.`deleted_at` is null order by `time_start` asc",
            $qb->toSql()
        );
    }

    /** @test */
    public function start_multiple_for_d1()
    {
        $sorting = ['time_start' => 'asc'];

        $today = date('Y-m-d 00:00:00');
        $tomorrow = date('Y-m-d 00:00:00', strtotime('+1 day'));

        $filters = [
            'time_start_from'     => $today,
            'time_start_to'       => $tomorrow,
            'test_take_status_id' => 1,
        ];
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(0, $qb->get());
        $this->assertEquals(
            [1486, 1486, 1486, $today, $tomorrow, 1],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `time_start` >= ? and `time_start` <= ? and `test_take_status_id` = ? and `test_takes`.`deleted_at` is null order by `time_start` asc",
            $qb->toSql()
        );
    }

    /** @test */
    public function load_discussed_glance_for_s1()
    {
        $today = date('Y-m-d H:i:00');
        $sorting = [
            'time_start' => 'desc',
        ];

        $filters = [
            'test_take_status_id' => [8, 9],
            'show_results_from'   => $today,
        ];

        $this->actingAs(User::whereUsername('s1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

//        dd($qb->getBindings());

        $this->assertCount(0, $qb->get());
        $this->assertEquals(
            [1483, 8, 9, $today],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where `test_takes`.`id` in (select `test_take_id` from `test_participants` where `user_id` = ? and `deleted_at` is null) and `tests`.`deleted_at` is null and `test_take_status_id` in (?, ?) and `show_results` >= ? and `test_takes`.`deleted_at` is null order by `time_start` desc",
            $qb->toSql()
        );
    }

    /** @test */
    public function widget_planned_for_s1()
    {
        $sorting = ['time_start' => 'asc'];

        $filters = [
            'test_take_status_id' => ["1", "3"],
        ];

        $this->actingAs(User::whereUsername('s1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(0, $qb->get());

        $this->assertEquals(
            [1483, "1", "3"],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where `test_takes`.`id` in (select `test_take_id` from `test_participants` where `user_id` = ? and `deleted_at` is null) and `tests`.`deleted_at` is null and `test_take_status_id` in (?, ?) and `test_takes`.`deleted_at` is null order by `time_start` asc",
            $qb->toSql()
        );
    }

    /** @test */
    public function widget_rated_for_s1()
    {
        $sorting = [
            'id' => 'desc',
        ];

        $filters = [
            'test_take_status_id' => 9
        ];

        $this->actingAs(User::whereUsername('s1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(1, $qb->get());

        $this->assertEquals(
            [1483, 9],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where `test_takes`.`id` in (select `test_take_id` from `test_participants` where `user_id` = ? and `deleted_at` is null) and `tests`.`deleted_at` is null and `test_take_status_id` = ? and `test_takes`.`deleted_at` is null order by `id` desc",
            $qb->toSql()
        );
    }

    /** @test */
    public function load_planned_student_for_s1()
    {
        $sorting = [
            'time_start' => 'asc',
        ];

        $filters = [
            'test_take_status_id' => [1, 3],
        ];

        $this->actingAs(User::whereUsername('s1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(0, $qb->get());
        $this->assertEquals(
            [1483, 1, 3],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where `test_takes`.`id` in (select `test_take_id` from `test_participants` where `user_id` = ? and `deleted_at` is null) and `tests`.`deleted_at` is null and `test_take_status_id` in (?, ?) and `test_takes`.`deleted_at` is null order by `time_start` asc",
            $qb->toSql()
        );
    }


    /** @test */
    public function load_taken_student_for_s1()
    {
        $sorting = [
            'time_start' => 'desc',
        ];

        $filters = [
            'test_take_status_id' => [6, 7],
        ];

        $this->actingAs(User::whereUsername('s1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(1, $qb->get());
        $this->assertEquals(
            [1483, 6, 7],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where `test_takes`.`id` in (select `test_take_id` from `test_participants` where `user_id` = ? and `deleted_at` is null) and `tests`.`deleted_at` is null and `test_take_status_id` in (?, ?) and `test_takes`.`deleted_at` is null order by `time_start` desc",
            $qb->toSql()
        );
    }

    /** @test */
    public function load_rated_student_for_s1()
    {
        $sorting = [
            'time_start' => 'desc',
        ];

        $filters = [
            'test_take_status_id' => 9,
        ];

        $this->actingAs(User::whereUsername('s1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);

        $this->assertCount(1, $qb->get());
        $this->assertEquals(
            [1483, 9],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where `test_takes`.`id` in (select `test_take_id` from `test_participants` where `user_id` = ? and `deleted_at` is null) and `tests`.`deleted_at` is null and `test_take_status_id` = ? and `test_takes`.`deleted_at` is null order by `time_start` desc",
            $qb->toSql()
        );
    }

    /** @test */
    public function surveillance_data_for_d1()
    {
        $sorting = [
            'time_start' => 'desc',
        ];

        $filters = [
            'test_take_status_id' => 3,
            'invigilator_id'      => 1486,
        ];

        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);
        $this->assertEquals(
            [1486, 1486, 1486, 3, 1486,],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `test_take_status_id` = ? and `test_takes`.`id` in (select `test_take_id` from `invigilators` where `deleted_at` is null and `user_id` = ?) and `test_takes`.`deleted_at` is null order by `time_start` desc",
            $qb->toSql()
        );
    }


    /** @test */
    public function select_test_take_list_for_d1()
    {
        $sorting = [];

        $filters = [];

        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);
        $this->assertEquals(
            [1486, 1486, 1486,],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `test_takes`.`deleted_at` is null",
            $qb->toSql()
        );
    }

    /** @test */
    public function analysis_controller_school_class_for_d1()
    {
        $sorting = ['id' => 'desc'];

        $filters = [
            'school_class_id'     => 1,
            'test_take_status_id' => [6, 7, 8, 9],
        ];

        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        $qb = TestTake::filtered($filters, $sorting);
        $this->assertEquals(
            [1486, 1486, 1486, 2, 3, 6, 7, 8, 9,],
            $qb->getBindings()
        );
        $this->assertEquals(
            "select `test_takes`.* from `test_takes` inner join `tests` on `tests`.`id` = `test_takes`.`test_id` where (`test_id` in (select `id` from `tests` where `user_id` = ? and `deleted_at` is null) or `user_id` = ? or `test_takes`.`id` in (select `test_take_id` from `invigilators` where `user_id` = ? and `deleted_at` is null)) and `tests`.`deleted_at` is null and `test_takes`.`id` in (?, ?, ?, ?, ?) and `test_take_status_id` in (?, ?, ?, ?) and `test_takes`.`deleted_at` is null order by `id` desc",
            $qb->toSql()
        );
    }




}