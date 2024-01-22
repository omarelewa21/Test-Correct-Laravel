<?php

namespace tcCore\FactoryScenarios;

use tcCore\Test;
use tcCore\User;

class FactoryScenarioTestTakeAllStatuses
{
    public $testTakeScenarioFactories;
    protected ?User $user;

    public static function create(User $user = null, ?Test $test = null): static
    {
        $factory = new static();

        $factory->user = $user;
        $factory->setUp($test);

        return $factory;
    }

    public function getScenarioFactories()
    {
        return $this->testTakeScenarioFactories;
    }

    protected function setUp(?Test $test): void
    {
        $this->testTakeScenarioFactories = collect([
            '1' => FactoryScenarioTestTakePlanned::create($this->user, test: $test),       //test_take_status_id: 1
            '3' => FactoryScenarioTestTakeTakingTest::create($this->user, test: $test),    //test_take_status_id: 3
            '6' => FactoryScenarioTestTakeTaken::create($this->user, test: $test),         //test_take_status_id: 6
            '7' => FactoryScenarioTestTakeDiscussing::create($this->user, test: $test),    //test_take_status_id: 7
            '8' => FactoryScenarioTestTakeDiscussed::create($this->user, test: $test),     //test_take_status_id: 8
            '9' => FactoryScenarioTestTakeRated::create($this->user, test: $test),         //test_take_status_id: 9
        ]);
    }
}