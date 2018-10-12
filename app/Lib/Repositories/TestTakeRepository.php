<?php namespace tcCore\Lib\Repositories;


use tcCore\SchoolClass;
use tcCore\TestParticipant;
use tcCore\TestTake;

class TestTakeRepository {
    public static function getTestTakesOfSchoolClass(SchoolClass $schoolClass) {
        // Get test takes
        $testTakes = TestTake::whereIn('id', function($query) use ($schoolClass) {
            $testParticipant = new TestParticipant();
            $query->select('test_take_id')->from($testParticipant->getTable())->where('school_class_id', $schoolClass->getKey());
        })->get();

        $schoolClass->setRelation('testTakes', $testTakes);

        return $schoolClass;
    }
}