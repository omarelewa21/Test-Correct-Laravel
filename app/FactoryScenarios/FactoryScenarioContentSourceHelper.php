<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryUser;

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
        return
            parent::create()
                ->seedCreathlonDutchOnlyItemBank();

    }


    public function getData()
    {
        return array_merge(
            parent::getData(), [
        ]);
    }
}