<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\FactoryScenarios\FactoryScenarioSchoolThiemeMeulenhoff;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\User;

class ThiemeMeulenhoffItemBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->reset();

        if (User::where('username', 'info+tmontwikkelaar@test-correct.nl')->exists()) {
            return;
        }

        $this->generateSchoolWithTests();

        $this->allowAllForSchoolLocationToView();
    }

    protected function generateSchoolWithTests()
    {
        $factoryScenarioSchool = FactoryScenarioSchoolThiemeMeulenhoff::create();
        $school = $factoryScenarioSchool->schools->first();

        $primaryTestAuthor = $school->schoolLocations->first()->users()->where('username', 'info+tmontwikkelaar@test-correct.nl')->first();
        $secondaryTestAuthor = $school->schoolLocations->first()->users()->where('username', ' info+bak-TM@test-correct.nl')->first();

        $collection = $school->schoolLocations->first()->schoolLocationSections->where('demo', false)->first()->section->subjects->split(2);

        $firstHalf = $collection[0];
        $secondHalf = $collection[1] ?? collect();


        $firstHalf->each(function ($subject) use ($primaryTestAuthor) {
            \tcCore\Factories\FactoryTest::create($primaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $subject->name,
                    'subject_id'         => $subject->id,
                    'abbreviation'       => 'TM',
                    'scope'              => ThiemeMeulenhoffService::getPublishScope(),
                    'education_level_id' => '1',
                    'draft'              => false,
                ])
                ->addQuestions([
                    FactoryQuestionOpenShort::create()->setProperties([
                        "question" => '<p>voorbeeld vraag thieme meulenhoff:</p> <p>wat is de waarde van pi</p> ',
                    ]),
                ]);
        });
        $secondHalf->each(function ($subject) use ($secondaryTestAuthor) {
            \tcCore\Factories\FactoryTest::create($secondaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $subject->name,
                    'subject_id'         => $subject->id,
                    'abbreviation'       => 'UNF',
                    'scope'              => 'not_'.ThiemeMeulenhoffService::getPublishScope(),
                    'education_level_id' => '1',
                    'draft'              => false,
                ])
                ->addQuestions([
                    FactoryQuestionOpenShort::create()->setProperties([
                        "question" => '<p>voorbeeld vraag thieme meulenhoff:</p> <p>wat is de waarde van pi</p> ',
                    ]),
                ]);
        });
    }

    protected function allowAllForSchoolLocationToView()
    {
        $school_location = \tcCore\SchoolLocation::find(1);
        ThiemeMeulenhoffService::getAllFeatureSettings()->each(function ($setting) use ($school_location) {
            $value = $setting->value;
            $school_location->$value = true;
        });
        $school_location->save();
    }

}
