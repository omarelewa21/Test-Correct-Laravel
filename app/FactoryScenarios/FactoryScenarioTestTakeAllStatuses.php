<?php

namespace tcCore\FactoryScenarios;

use tcCore\User;

class FactoryScenarioTestTakeAllStatuses
{
    public $testTakeScenarioFactories;
    protected ?User $user;

    public static function create(User $user = null)
    {
        $factory = new static;

        $factory->user = $user;
        $factory->setUp();

        return $factory;
    }

    public function getScenarioFactories()
    {
        return $this->testTakeScenarioFactories;
    }

    protected function setUp()
    {
        $this->testTakeScenarioFactories = collect([
            '1' => FactoryScenarioTestTakePlanned::create($this->user),       //test_take_status_id: 1
            '3' => FactoryScenarioTestTakeTakingTest::create($this->user),    //test_take_status_id: 3
            '6' => FactoryScenarioTestTakeTaken::create($this->user),         //test_take_status_id: 6
            '7' => FactoryScenarioTestTakeDiscussing::create($this->user),    //test_take_status_id: 7
            '8' => FactoryScenarioTestTakeDiscussed::create($this->user),     //test_take_status_id: 8
            '9' => FactoryScenarioTestTakeRated::create($this->user),         //test_take_status_id: 9
        ]);
    }
}