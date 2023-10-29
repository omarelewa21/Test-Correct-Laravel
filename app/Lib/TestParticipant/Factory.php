<?php

namespace tcCore\Lib\TestParticipant;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use tcCore\SchoolClass;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;

class Factory
{

    private $testParticipant;
    private TestTake $testTake;

    public function __construct(TestParticipant $testParticipant)
    {
        $this->testParticipant = $testParticipant;
    }

    public function generateMany($testTake, $data)
    {
        $schoolClassIds = $data['school_class_ids'] ?? null;
        $testParticipantIds = $data['test_participant_ids'] ?? null;

        $userIds = Arr::wrap($data['user_id'] ?? []);

        unset($data['school_class_ids'], $data['test_participant_ids'], $data['user_id']);

        $data['allow_inbrowser_testing'] ??= $testTake->allow_inbrowser_testing;

        $schoolClassUserIds = $schoolClassIds ? $this->getUserIdsFromSchoolClass($schoolClassIds) : [];
        $testParticipantUserIds = $testParticipantIds ? $this->getUserIdsFromTestParticipantIds(
            $testParticipantIds
        ) : [];

        $UserIdSchoolClass = [];
        $allUsers = $schoolClassUserIds + $testParticipantUserIds;

        foreach ($allUsers as $schoolClassId => $studentUsers) {
            foreach ($studentUsers as $studentUserId) {
                $UserIdSchoolClass[$studentUserId] = $schoolClassId;
                $userIds[] = $studentUserId;
            }
        }
        $userIds = array_unique($userIds);

        $testParticipants = [];

        $existingTestParticipants = TestParticipant::withTrashed()->whereIn('user_id', $userIds)->where(
            'test_take_id',
            $testTake->getKey()
        )->get();
        foreach ($existingTestParticipants as $existingTestParticipant) {
            if ($existingTestParticipant->trashed()) {
                $existingTestParticipant->restore();
            }

            $existingTestParticipant->fill($data);

            if (($key = array_search($existingTestParticipant->getAttribute('user_id'), $userIds)) !== false) {
                unset($userIds[$key]);
            }

            if (array_key_exists($existingTestParticipant->getAttribute('user_id'), $UserIdSchoolClass)) {
                $existingTestParticipant->setAttribute(
                    'school_class_id',
                    $UserIdSchoolClass[$existingTestParticipant->getAttribute('user_id')]
                );
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

        foreach ($schoolClasses as $schoolClass) {
            foreach ($schoolClass->studentUsers as $studentUser) {
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

        foreach ($testParticipants as $testParticipant) {
            $testParticipantUserIds[$testParticipant->getAttribute(
                'school_class_id'
            )][] = $testParticipant->getAttribute('user_id');
        }

        return $testParticipantUserIds;
    }

    public function generate($data, $withoutSaving = false)
    {
        $this->testParticipant = new TestParticipant();

        $this->testParticipant->fill($data);

        if ($withoutSaving === true) {
            return $this->testParticipant;
        }

        if ($this->testParticipant->save()) {
            return $this->testParticipant;
        }

        return false;
    }

    public static function generateForUsers(TestTake $testTake, array $classesAndStudents): Collection
    {
        $factory = new self(new TestParticipant());
        $factory->testTake = $testTake;

        $participantProposals = $factory->getParticipantProposals($classesAndStudents);
        $existingParticipants = $factory->testTake->testParticipants->loadMissing('user:id,guest');

        $participantsToCreate = $factory->getParticipantsToCreate($participantProposals, $existingParticipants);
        $participantsToDelete = $factory->getParticipantsToDelete($participantProposals, $existingParticipants);
        $participantsToUpdate = $factory->getParticipantsToUpdate($participantsToDelete, $participantsToCreate);

        $factory->createParticipants($participantsToCreate);
        $factory->deleteParticipants($participantsToDelete);
        $factory->updateParticipants($participantsToUpdate, $participantProposals);

        return $factory->testTake->testParticipants()->get();
    }

    private function getParticipantProposals(array $classesAndStudents)
    {
        $selectedClasses = $this->getSelectedClasses($classesAndStudents);
        return $this->getSelectedUserIds($classesAndStudents)
            ->mapWithKeys(function ($userId, $userUuid) use ($classesAndStudents, $selectedClasses) {
                $child = collect($classesAndStudents['children'])
                    ->first(fn($child) => $child['value'] === $userUuid);
                return [
                    $userId => [
                        'userId'  => $userId,
                        'classId' => $selectedClasses[$child['parent']]
                    ]
                ];
            });
    }

    private function getSelectedUserIds(array $classesAndStudents): Collection
    {
        $userUuids = collect($classesAndStudents['children'])->pluck('value');
        if ($userUuids->isEmpty()) {
            return collect();
        }
        return User::whereUuidIn($userUuids)
            ->distinct()
            ->get(['id', 'uuid'])
            ->mapWithKeys(fn($user) => [$user->uuid => $user->id]);
    }

    private function getSelectedClasses(array $classesAndStudents): Collection
    {
        $schoolClassUuids = collect($classesAndStudents['children'])->pluck('parent');
        if ($schoolClassUuids->isEmpty()) {
            return collect();
        }
        return SchoolClass::whereUuidIn($schoolClassUuids)
            ->get(['id', 'uuid'])
            ->mapWithKeys(fn($class) => [$class->uuid => $class->id]);
    }

    private function getParticipantsToCreate(Collection $participantProposals, Collection $existingParticipants): Collection
    {
        return $participantProposals->filter(function ($proposal) use ($existingParticipants) {
            return $existingParticipants->doesntContain(function ($participant) use ($proposal) {
                return $participant->user_id === $proposal['userId']
                    && $participant->school_class_id === $proposal['classId'];
            });
        });
    }

    /**
     * @param Collection $participantProposals
     * @param Collection $existingParticipants
     * @return mixed
     */
    private function getParticipantsToDelete(Collection $participantProposals, Collection $existingParticipants): Collection
    {
        return $existingParticipants
            ->where(fn($participant) => !$participant->user->guest)
            ->filter(function ($participant) use ($participantProposals) {
            return $participantProposals->doesntContain(function ($proposal) use ($participant) {
                return $participant->user_id === $proposal['userId']
                    && $participant->school_class_id === $proposal['classId'];
            });
        });
    }

    /**
     * @param mixed $participantsToDelete
     * @param Collection $participantsToCreate
     * @return mixed
     */
    private function getParticipantsToUpdate(mixed $participantsToDelete, Collection $participantsToCreate): Collection
    {
        return $participantsToDelete->filter(function ($participant) use ($participantsToCreate) {
            return $participantsToCreate->contains(fn($proposal) => $proposal['userId'] === $participant->user_id);
        })->each(function ($participant) use ($participantsToDelete, $participantsToCreate) {
            $participantsToCreate->forget(
                $participantsToCreate->search(
                    fn($participantToCreate) => $participantToCreate['userId'] === $participant->user_id
                )
            );
            $participantsToDelete->forget(
                $participantsToDelete->search(
                    fn($participantToDelete) => $participantToDelete->user_id === $participant->user_id
                )
            );
        });
    }

    private function createParticipants(Collection $participantsToCreate): void
    {
        $newParticipants = $participantsToCreate->map(function ($proposal) {
            return [
                'test_take_id'            => $this->testTake->id,
                'user_id'                 => $proposal['userId'],
                'school_class_id'         => $proposal['classId'],
                'test_take_status_id'     => TestTakeStatus::STATUS_PLANNED,
                'allow_inbrowser_testing' => $this->testTake->allow_inbrowser_testing,
                'deleted_at'              => null,
                'uuid'                    => Uuid::uuid4(),
            ];
        })->toArray();

        TestParticipant::upsert($newParticipants, ['test_take_id', 'user_id', 'school_class_id']);
    }

    private function deleteParticipants(Collection $participantsToDelete): void
    {
        if ($participantsToDelete->isEmpty()) {
            return;
        }
        TestParticipant::whereIn('user_id', $participantsToDelete->pluck('user_id'))
            ->whereTestTakeId($this->testTake->id)
            ->delete();
    }

    private function updateParticipants(Collection $participantsToUpdate, Collection $participantProposals): void
    {
        $participantsToUpdate->each(function ($participant) use ($participantProposals) {
            $participant->update(['school_class_id' => $participantProposals[$participant->user_id]['classId']]);
        });
    }

}