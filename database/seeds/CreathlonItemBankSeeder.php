<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use tcCore\BaseSubject;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\FactoryScenarios\FactoryScenarioSchoolCreathlon;
use tcCore\Lib\User\Factory;
use tcCore\Period;
use tcCore\SchoolYear;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\User;

class CreathlonItemBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->reset();

        if (User::where('username', 'info+creathlonontwikkelaar@test-correct.nl')->exists()) {
            return;
        }

        $this->generateCreathlonSchoolWithTests();

        $this->allowFirstSchoolLocationToViewCreathlon();


    }

    protected function generateCreathlonSchoolWithTests()
    {
        $factoryScenarioSchool = FactoryScenarioSchoolCreathlon::create();
        $school = $factoryScenarioSchool->schools->first();

        $primaryTestAuthor = $school->schoolLocations->first()->users()->where('username', 'info+creathlonontwikkelaar@test-correct.nl')->first();
        $secondaryTestAuthor = $school->schoolLocations->first()->users()->where('username', 'info+creathlonontwikkelaarB@test-correct.nl')->first();

        $collection = $school->schoolLocations->first()->schoolLocationSections->where('demo', false)->first()->section->subjects->split(2);

        $firstHalf = $collection[0];
        $secondHalf = $collection[1] ?? collect();


        $firstHalf->each(function ($subject) use ($primaryTestAuthor) {
            \tcCore\Factories\FactoryTest::create($primaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $subject->name,
                    'subject_id'         => $subject->id,
                    'abbreviation'       => 'PUBLS',
                    'scope'              => 'published_creathlon',
                    'education_level_id' => '1',
                ])
                ->addQuestions([
                    FactoryQuestionOpenShort::create(),
                ]);
        });
        $secondHalf->each(function ($subject) use ($secondaryTestAuthor) {
            \tcCore\Factories\FactoryTest::create($secondaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $subject->name,
                    'subject_id'         => $subject->id,
                    'abbreviation'       => 'UNF',
                    'scope'              => 'not_published_creathlon',
                    'education_level_id' => '1',
                ])
                ->addQuestions([
                    FactoryQuestionOpenShort::create(),
                ]);
        });
    }

    protected function allowFirstSchoolLocationToViewCreathlon()
    {
        \tcCore\SchoolLocation::find(1)->allow_creathlon = true;
    }

}
