<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTest;
use tcCore\Test;
use tcCore\User;

abstract class FactoryScenarioTest
{
    public FactoryTest $testFactory;
    protected ?string $testName;
    protected ?User $user;

    /**
     * Set-up a scenario in the Database for testing
     */
    public static function create(string $testName = null, User $user = null) : FactoryScenarioTest
    {
        $factory = new static;
        $factory->user = $user ?? User::find(1486);
        $factory->testName = $testName;
        $factory->testFactory = $factory->createFactoryTest();

        return $factory;
    }

    public static function createTest(string $testName = null, User $user = null) : Test
    {
        $factory = self::create($testName, $user);

        return $factory->getTestModel();
    }

    public function getTestId()
    {
        return $this->testFactory->getTestId();
    }
    public function getTestModel()
    {
        return $this->testFactory->getTestModel();
    }

    /**
     * Define the specific scenario
     */
    protected abstract function createFactoryTest();
}