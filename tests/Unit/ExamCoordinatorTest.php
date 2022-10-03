<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use tcCore\Http\Livewire\Teacher\TestsOverview;
use tcCore\School;
use tcCore\User;
use Tests\TestCase;

class ExamCoordinatorTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_not_assign_other_than_enum_values_to_exam_coordinator_for_column()
    {
        $d1 = self::getTeacherOne();

        $d1->setAttribute('is_examcoordinator_for', 'something');

        $d1->save();
        $d1->refresh();

        $this->assertEmpty($d1->getAttribute('is_examcoordinator_for'));
    }

    /** @test */
    public function can_assign_enum_values_to_exam_coordinator_for_column()
    {
        $d1 = self::getTeacherOne();
        $enums = collect($d1->getPossibleEnumValues('is_examcoordinator_for'));

        $d1->setAttribute('is_examcoordinator_for', $enums->first());

        $d1->save();
        $d1->refresh();

        $this->assertEquals($enums->first(),$d1->getAttribute('is_examcoordinator_for'));
    }

    /** @test */
    public function has()
    {
        $d1 = User::find(1589);

        $this->assertCount(1, $d1->allowedSchoolLocations);

        School::find(1)->schoolLocations->each(function ($location) use ($d1) {
            $d1->addSchoolLocation($location);
        });

        $this->assertCount(5, $d1->allowedSchoolLocations);
    }
}