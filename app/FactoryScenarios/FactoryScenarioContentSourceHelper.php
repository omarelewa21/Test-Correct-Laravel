<?php

namespace tcCore\FactoryScenarios;

class FactoryScenarioContentSourceHelper extends FactoryScenarioSchoolRandomComplex
{
    /**
     * create complex school with creathlon
     */
    use FactoryScenarioSeederTrait;

    public function __construct()
    {
        parent::__construct();
    }

    public static function create()
    {
        return parent::create()->seedCreathlonDutchOnlyItemBank();
    }


    public function getData()
    {
        return array_merge(parent::getData(), []);
    }
}