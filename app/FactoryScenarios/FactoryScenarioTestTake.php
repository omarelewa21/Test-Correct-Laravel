<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\User;

abstract class FactoryScenarioTestTake
{
    public $test;
    public FactoryTestTake $testTakeFactory;
    protected ?User $user;
    protected ?string $testName;

    /**
     * Set-up a scenario in the Database for testing
     */
    public static function create(User $user = null, ?string $testName = null, ?Test $test = null) : FactoryScenarioTestTake
    {
        $factory = new static;

        $factory->user = $user;
        $factory->testName = $testName ?? $factory::DEFAULT_TEST_NAME;
        $factory->test = $test ?? FactoryScenarioTestTestWithAllQuestionTypes::createTest($testName, $factory->user);

        $factory->testTakeFactory = $factory->createFactoryTestTake();

        return $factory;
    }

    public static function createTestTake(User $user = null, ?string $testName = null, ?Test $test = null) : TestTake
    {
        $factory = new static;

        $factory->user = $user;
        $factory->testName = $testName;
        $factory->test = $test ?? FactoryScenarioTestTestWithAllQuestionTypes::createTest($testName, $factory->user);

        return $factory->createFactoryTestTake()->testTake;
    }

    public function getTestId() : int
    {
        return $this->testTakeFactory->getTestId();
    }

    /**
     * Define the specific scenario
     */
    protected abstract function createFactoryTestTake();
}