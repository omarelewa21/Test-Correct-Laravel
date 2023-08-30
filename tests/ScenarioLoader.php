<?php

namespace Tests;

use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;

final class ScenarioLoader
{
    public static $loadedScenario = false;

    public static $data;

    public static function load($scenarioName)
    {
        $microTime = microtime(true);

        if (!self::isLoadedScenario($scenarioName) && !is_bool($scenarioName)) {
            logger('start running scenario for '.$scenarioName);
            if (!method_exists($scenarioName, 'getData')) {
                    throw new \Exception(
                        sprintf(
                            'Trying to load unkown scenario[%s]! Have you registered the scenario in [%s]',
                            $scenarioName,
                            __CLASS__ . '::' . __METHOD__
                        )
                    );
            }

            $factory = $scenarioName::create();
            self::$data = $factory->getData();
            static::$loadedScenario = $scenarioName;
            logger('done running scenario for '. $scenarioName . ' taking ' .microtime(true)-$microTime). 'milliseconds';
        }
    }



    public static function get($key) {
        if ($key === '*') {
            return self::$data;
        }
        if (array_key_exists($key, self::$data)) {
            return self::$data[$key];
        }

        throw new \Exception(
            sprintf(
                'Key [%s] not found on scenario data, current loaded scenario is: %s it hold keys for [%s]',
                $key,
                self::$loadedScenario,
                collect(self::$data)->keys()->join(',')
            )
        );
    }

    /**
     * @param $name
     * @return bool
     */
    public static function isLoadedScenario($name): bool
    {
        return static::$loadedScenario === $name;
    }
}