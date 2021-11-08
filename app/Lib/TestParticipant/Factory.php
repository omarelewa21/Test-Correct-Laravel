<?php namespace tcCore\Lib\TestParticipant;

use tcCore\SchoolClass;
use tcCore\TestParticipant;

class Factory {

    private $testParticipant;

    public function __construct(TestParticipant $testParticipant)
    {
        $this->testParticipant = $testParticipant;
    }

    public function generateMany($testTakeId, $data)
    {
        $schoolClassIds = null;
        if (array_key_exists('school_class_ids', $data)) {
            $schoolClassIds = $data['school_class_ids'];
            unset($data['school_class_ids']);
        }

        $testParticipantIds = null;
        if (array_key_exists('test_participant_ids', $data)) {
            $testParticipantIds = $data['test_participant_ids'];
            unset($data['test_participant_ids']);
        }

        $userIds = [];

        if (array_key_exists('user_id', $data)) {
            if (is_array($data['user_id'])) {
                $userIds = $data['user_id'];
            } else {
                $userIds = [$data['user_id']];
            }

            unset($data['user_id']);
        }

        $schoolClassUserIds = [];
        if($schoolClassIds) {
            $schoolClassUserIds = $this->getUserIdsFromSchoolClass($schoolClassIds);
        }

        $testParticipantUserIds = [];
        if($testParticipantIds) {
            $testParticipantUserIds = $this->getUserIdsFromTestParticipantIds($testParticipantIds);
        }

        $UserIdSchoolClass = [];
        foreach($schoolClassUserIds as $schoolClassId => $studentUsers) {
            foreach ($studentUsers as $studentUserId) {
                $UserIdSchoolClass[$studentUserId] = $schoolClassId;
                $userIds[] = $studentUserId;
            }
        }

        foreach($testParticipantUserIds as $schoolClassId => $studentUsers) {
            foreach ($studentUsers as $studentUserId) {
                $UserIdSchoolClass[$studentUserId] = $schoolClassId;
                $userIds[] = $studentUserId;
            }
        }

        $userIds = array_unique($userIds);

        $testParticipants = [];

        $existingTestParticipants = TestParticipant::withTrashed()->whereIn('user_id', $userIds)->where('test_take_id', $testTakeId)->get();
        foreach ($existingTestParticipants as $existingTestParticipant) {
            if ($existingTestParticipant->trashed()) {
                $existingTestParticipant->setAttribute(with(new TestParticipant())->getDeletedAtColumn(), null);
            }

            $existingTestParticipant->fill($data);

            if(($key = array_search($existingTestParticipant->getAttribute('user_id'), $userIds)) !== false) {
                unset($userIds[$key]);
            }

            if (array_key_exists($existingTestParticipant->getAttribute('user_id'), $UserIdSchoolClass)) {
                $existingTestParticipant->setAttribute('school_class_id', $UserIdSchoolClass[$existingTestParticipant->getAttribute('user_id')]);
            }
            $testParticipants[] = $existingTestParticipant;
        }

        foreach ($userIds as $userId) {
            $testParticipant = new TestParticipant($data);
            $testParticipant->setAttribute('user_id', $userId);
            if (array_key_exists($userId, $UserIdSchoolClass)) {
                $testParticipant->setAttribute('school_class_id', $UserIdSchoolClass[$userId]);
            }
            $testParticipants[] = $testParticipant;
        }

        return $testParticipants;
    }

    private function getUserIdsFromSchoolClass($schoolClassIds) {
        $schoolClasses = SchoolClass::with('studentUsers')->find($schoolClassIds);
        $schoolClassUserIds = [];

        foreach($schoolClasses as $schoolClass) {
            foreach($schoolClass->studentUsers as $studentUser) {
                $schoolClassUserIds[$schoolClass->getKey()][] = $studentUser->getKey();
            }
        }

        return $schoolClassUserIds;
    }

    private function getUserIdsFromTestParticipantIds($testParticipantIds) {
        $testParticipants = TestParticipant::find($testParticipantIds);
        $testParticipantUserIds = [];

        foreach($testParticipants as $testParticipant) {
            $testParticipantUserIds[$testParticipant->getAttribute('school_class_id')][] = $testParticipant->getAttribute('user_id');
        }

        return $testParticipantUserIds;
    }


    public function generate($data, $withoutSaving = false)
    {
        $this->testParticipant = new TestParticipant();

        $this->testParticipant->fill($data);

        if($withoutSaving === true){
            return $this->testParticipant;
        }

        if($this->testParticipant->save()){
            return $this->testParticipant;
        }

        return false;
    }
}