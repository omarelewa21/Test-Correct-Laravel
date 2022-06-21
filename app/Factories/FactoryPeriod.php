<?php

namespace tcCore\Factories;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Period;
use tcCore\User;

class FactoryPeriod
{
    use DoWhileLoggedInTrait;

    private User $user;

    public static function getPeriodsForUser(User $user)
    {
        $periodFactory = new self;
        $periodFactory->user = $user;

        return $periodFactory->getValidPeriods();
    }

    protected function getValidPeriods()
    {
        return $this->doWhileLoggedIn(function () {
            return Period::filtered(['filter' => ['current_school_year' => '1',]], [])->get();
        }, $this->user);
    }

    public static function getFirstPeriodForUser(User $user)
    {
        $periodFactory = new self;
        $periodFactory->user = $user;

        return $periodFactory->getValidPeriods()->first();
    }

    public static function getRandomPeriodForUser(User $user)
    {
        $periodFactory = new self;
        $periodFactory->user = $user;

        return $periodFactory->getValidPeriods()->random();
    }
}