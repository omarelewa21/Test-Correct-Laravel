<?php

namespace tcCore\Factories;

use iio\libmergepdf\Exception;
use Illuminate\Support\Arr;
use tcCore\Factories\Traits\DieAndDumpAble;
use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;
use tcCore\Lib\TestParticipant\Factory;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\Student;
use tcCore\TestParticipant;
use tcCore\TestTake;


class FactoryTestParticipant
{

    use PropertyGetableByName;
    use DoWhileLoggedInTrait;
    use DieAndDumpAble;

    /**
     * @var array{
     *          school_class_id: int,
     *          test_status_id: int,
     *      } $participantsData
     */
    public array $participantsData;
    public int $testTakeId;

    /**
     * @param int|array $classId
     * @return FactoryTestParticipant
     */
    public static function makeForAllUsersInClass($classId): FactoryTestParticipant
    {
        $list = collect(Arr::wrap($classId))->map(fn($item) => $item instanceof SchoolClass ? $item->getKey() : $item);

        if ($list->reject(fn($item) =>  is_int($item) )->count() > 0) {
            throw new Exception('$classId should contain ids of SchoolClass or Instances of SchoolClass');
        }

        $factory = new static;
        $factory->participantsData = [
            "school_class_ids" => $list->toArray(),
        ];

//        $examplepostdata = [
//            "school_class_ids"    => [
//                "1",
//            ],
//            "test_take_status_id" => "1",
//        ];

        return $factory;
    }

    public static function makeWithUserAndClass(array $studentIds, ?int $classId): FactoryTestParticipant
    {
        if (!$classId) {
            $classId = Student::where('user_id', $studentIds[0])->latest()->class_id;
        }

        $factory = new static;

        $factory->participantsData = [
            "user_id"         => $studentIds,
            "school_class_id" => $classId,
        ];

//        $example_data = [
//            "user_id"             => [
//                1621,
//                1623,
//            ],
//            "school_class_id"     => 33,
//            "test_take_status_id" => 1,
//        ];

        return $factory;
    }

    public static function createValidClassWithTestTake(TestTake $testTake, bool $first = false)
    {
        $factory = new static;

        $schoolLocationId = $testTake->test->owner_id;

        $validSchoolClasses = SchoolClass::where('school_location_id', $schoolLocationId)->where('demo', '0')
            ->has('students')
            ->get();
//            ->filter(function ($class, $key) use ($testTake) {
//                return ($class->teacher->where('subject_id', $testTake->test->subject_id)->where('user_id', $testTake->user_id)->isNotEmpty());
//            });


        if ($first) {
            $validSchoolClass = $validSchoolClasses->first()->id;
        } else {
            $validSchoolClass = $validSchoolClasses->random()->id;
        }

        // find school classes with the same school location
        // school classes with students in them

        // get first/random from result

        $factory->participantsData = [
            "school_class_ids"    => [
                $validSchoolClass,
            ],
            "test_take_status_id" => $testTake->test_take_status_id,
        ];
//
//        $factory->storeParticipants($testTake);

        return $factory;
    }

    public function storeParticipants(TestTake $testTake)
    {
        $this->participantsData['test_take_status_id'] = $testTake->getAttribute('test_take_status_id');
        $testTakeParticipantFactory = new Factory(new TestParticipant());
        return $testTakeParticipantFactory->generateMany($testTake, $this->participantsData);
    }
}
