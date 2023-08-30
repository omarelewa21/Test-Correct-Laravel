<?php

namespace tcCore\FactoryScenarios;

use Database\Seeders\CreathlonDutchOnlyItemBankSeeder;
use Database\Seeders\CreathlonItemBankSeeder;
use Database\Seeders\NationalItemBankShortSeeder;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionGroup;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Http\Helpers\ActingAsHelper;

trait FactoryScenarioSeederTrait
{

    protected function seedNationalItemBank()
    {
        (new NationalItemBankShortSeeder)->run($this->getData()['user']);
        ActingAsHelper::getInstance()->reset();
        $this->getData()['user']->schoolLocation()->update(['show_national_item_bank' => true]);
        return $this;
    }

    protected function seedSomeGroupQuestions()
    {
        $some = 5;

        FactoryTest::create($this->getData()['user'])->addQuestions(
            collect(range(1, $some))->map(function ($i) {
                return FactoryQuestionGroup::create()
                    ->addQuestions([
                        FactoryQuestionOpenShort::create(),
                    ]);
            })->toArray()
        );
        return $this;
    }

    private function seedCreathlonItemBank()
    {
        (new CreathlonItemBankSeeder)->run($this->getData()['user']);
        ActingAsHelper::getInstance()->reset();
        $this->getData()['user']->schoolLocation->allow_creathlon = true;
        return $this;
    }

    private function seedCreathlonDutchOnlyItemBank()
    {
        (new CreathlonDutchOnlyItemBankSeeder)->run($this->getData()['user']);
        ActingAsHelper::getInstance()->reset();
        $this->getData()['user']->schoolLocation->allow_creathlon = true;
        return $this;
    }

    private function seedFormidableItemBank()
    {
        (new FormidableItemBankSeeder)->run($this->getData()['user']);
        ActingAsHelper::getInstance()->reset();
        $this->getData()['user']->schoolLocation->allow_formidable = true;
        return $this;
    }
}
