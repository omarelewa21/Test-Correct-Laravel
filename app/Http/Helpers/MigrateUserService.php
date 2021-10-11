<?php

namespace tcCore\Http\Helpers;

use tcCore\User;

class MigrateUserService
{

    private $oldId;
    private $id;

    private $oldUser;
    private $user;

    public function __construct($oldId, $id)
    {
        $this->oldId = $oldId;
        $this->id = $id;

        $this->entreeHelper = new EntreeHelper([], '');
    }

    public function handle()
    {
        try {
            $this->testOldIdNotEqualId();
            $this->findOrFailOldUser();
            $this->findOrFailUser();

            ActingAsHelper::getInstance()->setUser($this->user);

            $this->testUsersAreInSameSchoolLocation();
            $this->testUserNameAttributesShouldMatch();
            $this->testUserNameFirstAttributesShouldMatch();
            $this->testEmailAdressIsImportAddressForUser();
            $this->testUsersHaveSameRole();


            $this->entreeHelper->copyEckIdNameNameSuffixNameFirstAndTransferClassesUpdateTestParticipantsAndDeleteUser(
                $this->oldUser,
                $this->user
            );
        } catch (\Exception $e) {
            return sprintf('An error occured: %s', $e->getMessage());
        }
        return true;
    }

    private function testOldIdNotEqualId(): void
    {
        if ($this->oldId == $this->id) {
            throw new \Exception(
                sprintf('oldId can not be Id (%d, %d)', $this->oldId, $this->id)
            );
        }
    }


    private function findOrFailOldUser()
    {
        try {
            $this->oldUser = User::findOrFail($this->oldId);
        } catch (\Exception $e) {
            throw new \Exception(sprintf('oldId %d is not a propper user_id', $this->oldId));
        }
        return $this->oldUser;
    }

    private function findOrFailUser()
    {
        try {
            $this->user = User::findOrFail($this->id);
        } catch (\Exception $e) {
            throw new \Exception(sprintf('id %d is not a propper user_id', $this->id));
        }
        return $this->user;
    }

    private function testUsersAreInSameSchoolLocation()
    {
        if ($this->user->inSchoolLocationAsUser($this->oldUser)) {
            return true;
        }

        throw new \Exception('SchoolLocation are not the same');
    }

    private function testUsersHaveSameRole()
    {
        if ($this->user->isA('teacher') && ($this->oldUser->isA('teacher'))) {
            return true;
        }

        if ($this->user->isA('student') && ($this->oldUser->isA('student'))) {
            return true;
        }

        throw new \Exception('Roles are not the same.');
    }

    private function testUserNameAttributesShouldMatch()
    {
        if ($this->user->name === $this->oldUser->name) {
            return true;
        }

        throw new \Exception (
            sprintf(
                'names are not the same [%s] , [%s].',
                $this->oldUser->name,
                $this->user->name
            )
        );
    }

    private function testUserNameFirstAttributesShouldMatch()
    {
        if ($this->user->name_first === $this->oldUser->name_first) {
            return true;
        }

        throw new \Exception (
            sprintf(
                'first names are not the same [%s] , [%s].',
                $this->oldUser->name_first,
                $this->user->name_first
            )
        );
    }

    private function testEmailAdressIsImportAddressForUser()
    {
        if ($this->user->hasImportMailAddress()) {
            return true;
        }
        throw new \Exception('user should have importMailAddress.');
    }


}
