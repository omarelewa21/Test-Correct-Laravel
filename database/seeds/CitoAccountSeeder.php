<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use tcCore\Lib\User\Factory;
use tcCore\Period;
use tcCore\SchoolYear;
use tcCore\User;
use tcCore\BaseSubject;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\SchoolLocation;
use tcCore\Test;


class CitoAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->reset();

        if (User::where('username', 'teacher-cito@test-correct.nl')->exists()) {
            return;
        }

        $this->generateCitoSchoolWithTests();

    }

    public function generateCitoSchoolWithTests()
    {
        $factoryScenarioSchool = \tcCore\FactoryScenarios\FactoryScenarioSchoolCito::create();
        $school_location = $factoryScenarioSchool->school->schoolLocations()->where('customer_code', 'CITO-TOETSENOPMAAT')->first();

        $primaryTestAuthor = $school_location->users()->where('username', 'teacher-cito@test-correct.nl')->first();
        $secondaryTestAuthor = $school_location->users()->where('username', 'teacher-cito-b@test-correct.nl')->first();

        $collection = $school_location->schoolLocationSections->where('demo', false)->first()->section->subjects->split(2);

        $firstHalf = $collection[0];
        $secondHalf = $collection[1];

        $firstHalf->each(function ($subject) use ($primaryTestAuthor) {
            \tcCore\Factories\FactoryTest::create($primaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $subject->name,
                    'subject_id'         => $subject->id,
                    'abbreviation'       => 'CITO',
                    'scope'              => 'cito',
                    'education_level_id' => '1',
                    'draft'              => false,
                ])
                ->addQuestions([
                    \tcCore\Factories\Questions\FactoryQuestionOpenShort::create()
                        ->setProperties([
                            'scope'    => 'cito',
                            'draft'    => false,
                            "question" => '<p>voorbeeld vraag cito:</p> <p>wat is de waarde van pi</p> ',
                        ]),
                ]);
        });
        $secondHalf->each(function ($subject) use ($secondaryTestAuthor) {
            \tcCore\Factories\FactoryTest::create($secondaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $subject->name,
                    'subject_id'         => $subject->id,
                    'abbreviation'       => 'CITO',
                    'scope'              => 'cito',
                    'education_level_id' => '1',
                    'draft'              => false,
                ])
                ->addQuestions([
                    \tcCore\Factories\Questions\FactoryQuestionOpenShort::create()
                        ->setProperties([
                            'scope'    => 'cito',
                            'draft'    => false,
                            "question" => '<p>voorbeeld vraag cito:</p> <p>wat is de waarde van pi</p> ',
                        ]),
                ]);
        });

    }

}
