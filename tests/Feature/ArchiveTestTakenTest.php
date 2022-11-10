<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\ArchivedModel;
use tcCore\TestTake;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveTestTakenTest extends TestCase
{
    use DatabaseTransactions;


    /** @test */
    public function a_teacher_can_see_a_list_of_taken_test_takes()
    {
        $list = $this->getListOfTakenTests();
        $this->assertEquals(1, count($list));
        $this->assertEquals('Toets met geluid', $list[0]['test']['name']);
        $this->assertNotEmpty($list[0]['uuid']);

    }

    /** @test */
    public function a_teacher_can_archive_a_taken_test_take()
    {
        $this->assertCount(0, ArchivedModel::all());
        $list = $this->getListOfTakenTests();

        $uuid = $list[0]['uuid'];

        $response = $this->put(
            route('test_take.archive', $uuid),
            $this->getTeacherOneAuthRequestData()
        )->assertSuccessful();
        $this->assertCount(1, ArchivedModel::all());
    }

    /** @test */
    public function a_teacher_can_unarchive_a_archived_taken_test_take()
    {
        $this->assertCount(0, ArchivedModel::all());
        $list = $this->getListOfTakenTests();

        $uuid = $list[0]['uuid'];

        $response = $this->put(
            route('test_take.archive', $uuid),
            $this->getTeacherOneAuthRequestData()
        );
        $this->assertCount(1, ArchivedModel::all());

        $response = $this->put(
            route('test_take.un_archive', $uuid),
            $this->getTeacherOneAuthRequestData()
        )->assertSuccessful();

        $this->assertCount(0, ArchivedModel::all());
    }

    /** @test */
    public function when_archived_for_a_teacher_the_filter_archive_param_set_zero_will_not_show_the_archived_test_take()
    {
        $list = $this->getListOfTakenTests();
        $uuid = $list[0]['uuid'];
        $response = $this->put(
            route('test_take.archive', $uuid),
            $this->getTeacherOneAuthRequestData()
        )->assertSuccessful();

        $list = $this->getListOfTakenTests();

        $this->assertEquals(0, count($list));


    }

    /** @test */
    public function when_archived_for_a_teacher_the_filter_archive_param_set_one_will_show_the_archived_test_take()
    {
        $list = $this->getListOfTakenTests();
        $uuid = $list[0]['uuid'];
        $response = $this->put(
            route('test_take.archive', $uuid),
            $this->getTeacherOneAuthRequestData()
        )->assertSuccessful();

        $list = $this->getListOfTakenTests('1');

        $this->assertEquals(1, count($list));


    }

    /** @test */
    public function when_a_test_take_is_archived_twice_it_has_only_one_entry()
    {
        $this->assertCount(0, ArchivedModel::all());
        $list = $this->getListOfTakenTests();

        $uuid = $list[0]['uuid'];

        $this->put(
            route('test_take.archive', $uuid),
            $this->getTeacherOneAuthRequestData()
        )->assertSuccessful();
        $this->put(
            route('test_take.archive', $uuid),
            $this->getTeacherOneAuthRequestData()
        )->assertSuccessful();
        $this->assertCount(1, ArchivedModel::all());
    }


    /**
     * @return array
     */
    private function getListOfTakenTests($archived = '0')
    {
        $listData = [
            'sort' => '',
            'results' => 60,
            'page' => 1,
            'filters' => '_method=POST&data%5BTestTake%5D%5Bperiod_id%5D=0&data%5BTestTake%5D%5Bretake%5D=-1&data%5BTestTake%5D%5Btime_start_from%5D=&data%5BTestTake%5D%5Btime_start_to%5D=',
            'order' => ['time_start' => 'asc'],

            'filter' => [
                'test_take_status_id' => ['6', '7'],
                'invigilator_id' => 1486,
                'archived'=> $archived
            ],
        ];

        $response = $this->get(static::authTeacherOneGetRequest('test_take', $listData));

        return $response->decodeResponseJson()['data'];
    }

    protected function withArchived(){

    }
}
