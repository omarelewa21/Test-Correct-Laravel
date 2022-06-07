<?php

namespace tcCore\Factories;

use tcCore\EducationLevel;
use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Period;
use tcCore\User;

class FactoryEducationLevel
{
    use DoWhileLoggedInTrait;

    private User $user;

    public static function getEducationLevelsForUser(User $user)
    {
        $educationLevelFactory = new self;
        $educationLevelFactory->user = $user;

        return $educationLevelFactory->getValidEducationLevels();
    }

    protected function getValidEducationLevels()
    {
        return $this->doWhileLoggedIn(function () {
            return EducationLevel::filtered(['user_id' => (string)$this->user->id], [])->get();
        }, $this->user);
    }

    public static function getFirstEducationLevelForUser(User $user)
    {
        $educationLevelFactory = new self;
        $educationLevelFactory->user = $user;

        return $educationLevelFactory->getValidEducationLevels()->first();
    }

    public static function getRandomEducationLevelForUser(User $user)
    {
        $educationLevelFactory = new self;
        $educationLevelFactory->user = $user;

        return $educationLevelFactory->getValidEducationLevels()->random();
    }
}