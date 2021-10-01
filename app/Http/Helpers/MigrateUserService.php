<?php

namespace tcCore\Http\Helpers;

use tcCore\User;

class MigrateUserService
{

    private $oldId;
    private $id;

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
            $this->testOldIdNotBiggerId();

            $oldUser= $this->findOrFailOldUser();
            $user= $this->findOrFailUser();


            $this->entreeHelper->copyEckIdNameNameSuffixNameFirstAndTransferClassesUpdateTestParticipantsAndDeleteUser($oldUser,
                $user);
        } catch (\Exception $e) {
            return sprintf('An error occured: %s', $e->getMessage());
        }
    }

    private function testOldIdNotEqualId(): void
    {
        if ($this->oldId == $this->id) {
            throw new \Exception(
                sprintf('oldId can not be Id (%d, %d)', $this->oldId, $this->id)
            );
        }
    }

    private function testOldIdNotBiggerId(): void
    {
        if ($this->oldId > $this->id) {
            throw new \Exception(
                sprintf('oldId should be smaller then the new Id (%d, %d)', $this->oldId, $this->id)
            );
        }
    }

    private function findOrFailOldUser()
    {
        try {
            $oldUser = User::findOrFail($this->oldId);
        } catch (\Exception $e) {
            throw new \Exception(sprintf('oldId %d is not a propper user_id', $this->oldId));
        }
        return $oldUser;
    }

    private function findOrFailUser()
    {
        try {
            $user = User::findOrFail($this->id);
        } catch (\Exception $e) {
            throw new \Exception(sprintf('id %d is not a propper user_id', $this->id));
        }
        return $user;
    }
}
