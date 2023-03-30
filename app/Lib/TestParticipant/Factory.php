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
        $schoolClassIds = $data['school_class_ids'] ?? null;
        $testParticipantIds = $data['test_participant_ids'] ?? null;

        $userIds = $data['user_id'] ?? [];
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }
        unset($data['school_class_ids'], $data['test_participant_ids'], $data['user_id']);

        $schoolClassUserIds = $schoolClassIds ? $this->getUserIdsFromSchoolClass($schoolClassIds) : [];
        $testParticipantUserIds = $testParticipantIds ? $this->getUserIdsFromTestParticipantIds($testParticipantIds) : [];
        logger([
           'schoolClassUserIds' => $schoolClassUserIds,
           'testParticipantIds' => $testParticipantIds,
           'testParticipantUserIds' => $testParticipantUserIds,
        ]);
        $UserIdSchoolClass = [];
        $allUsers = $schoolClassUserIds + $testParticipantUserIds;
        logger([
            'allUsers' => $allUsers
        ]);
        foreach($allUsers as $schoolClassId => $studentUsers) {
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
            $testParticipant->skipBootCreatedMethod = true;
            $testParticipant->skipBootSavedMethod = true;
            if (array_key_exists($userId, $UserIdSchoolClass)) {
                $testParticipant->setAttribute('school_class_id', $UserIdSchoolClass[$userId]);
            }
            $testParticipants[] = $testParticipant;
        }

        return $testParticipants;
    }

    // Get user IDs from the specified school classes.
    private function getUserIdsFromSchoolClass($schoolClassIds)
    {

        $schoolClasses = SchoolClass::with('studentUsers')->find($schoolClassIds);
        $schoolClassUserIds = [];

        foreach($schoolClasses as $schoolClass) {
            foreach($schoolClass->studentUsers as $studentUser) {
                $schoolClassUserIds[$schoolClass->getKey()][] = $studentUser->getKey();
            }
        }

        return $schoolClassUserIds;
    }

    // Get user IDs from the specified test participants.
    private function getUserIdsFromTestParticipantIds($testParticipantIds)
    {
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