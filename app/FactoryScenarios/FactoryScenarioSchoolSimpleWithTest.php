<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use Database\Seeders\NationalItemBankShortSeeder;
use Illuminate\Support\Collection;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionGroup;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\School;
use tcCore\User;

class FactoryScenarioSchoolSimpleWithTest extends FactoryScenarioSchoolSimple
{
    public static function create()
    {
        return
            parent::create()
               ->seedTests();
    }

    public function getData()
    {
        return array_merge(
            parent::getData(), [
        ]);
    }
}
