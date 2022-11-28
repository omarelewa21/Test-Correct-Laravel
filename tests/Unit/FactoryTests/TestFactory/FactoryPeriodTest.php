<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryPeriod;
use tcCore\User;
use Tests\TestCase;

class FactoryPeriodTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function can_get_periods_for_User()
    {
        $userId = 1486;

        $periodsCollection = FactoryPeriod::getPeriodsForUser(User::find($userId));

        $this->assertInstanceOf("Illuminate\Database\Eloquent\Collection", $periodsCollection );
        $this->assertInstanceOf("tcCore\Period", $periodsCollection->first() );
    }

    /** @test */
    public function can_get_first_period_for_User()
    {
        $userId = 1486;

        $periodsModel = FactoryPeriod::getFirstPeriodForUser(User::find($userId));

        $this->assertInstanceOf("tcCore\Period", $periodsModel);
    }

    /** @test */
    public function can_get_random_period_for_User()
    {
        $userId = 1486;

        $periodsModel = FactoryPeriod::getRandomPeriodForUser(User::find($userId));

        $this->assertInstanceOf("tcCore\Period", $periodsModel);
    }

}