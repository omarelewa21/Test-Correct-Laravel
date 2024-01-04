<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use tcCore\BaseSubject;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\FactoryScenarios\FactoryScenarioSchoolCreathlon;
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\User;

class CreathlonDutchOnlyItemBankSeeder extends Seeder
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

        /* This fails because the FactoryScenarioSchoolCreathlon has a default base subject of FRENCH hardcoded... */
        $this->generateCreathlonSchoolWithTests();
    }

    protected function generateCreathlonSchoolWithTests()
    {
        $factoryScenarioSchool = FactoryScenarioSchoolCreathlon::create();
        $school = $factoryScenarioSchool->schools->first();

        $primaryTestAuthor = $school->schoolLocations->first()->users()->where('username', 'info+creathlonontwikkelaar@test-correct.nl')->first();
        $secondaryTestAuthor = $school->schoolLocations->first()->users()->where('username', 'info+creathlonontwikkelaarB@test-correct.nl')->first();

        $dutchSubject = $school->schoolLocations
            ->first()
            ->schoolLocationSections
            ->where('demo', false)
            ->first()
            ->section
            ->subjects
            ->where('base_subject_id', BaseSubject::DUTCH)
            ->first();



            \tcCore\Factories\FactoryTest::create($primaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $dutchSubject->name,
                    'subject_id'         => $dutchSubject->id,
                    'abbreviation'       => 'PUBLS',
                    'scope'              => CreathlonService::getPublishScope(),
                    'education_level_id' => '1',
                    'draft'              => false,
                ])
                ->addQuestions([
                    FactoryQuestionOpenShort::create()->setProperties([
                        "question" => '<p>voorbeeld vraag creathlon:</p> <p>wat is de waarde van pi</p> ',
                    ]),
                ]);

        \tcCore\Factories\FactoryTest::create($secondaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $dutchSubject->name,
                    'subject_id'         => $dutchSubject->id,
                    'abbreviation'       => 'UNF',
                    'scope'              => CreathlonService::getNotPublishScope(),
                    'education_level_id' => '1',
                    'draft'              => false,
                ])
                ->addQuestions([
                    FactoryQuestionOpenShort::create()->setProperties([
                        "question" => <<<HTML
                            <p>voorbeeld vraag creathlon:</p> 
                            <p>
                                <math xmlns="http://www.w3.org/1998/Math/MathML">
                                  <msup>
                                    <mi>e</mi>
                                    <mrow>
                                      <mi>i</mi>
                                      <mi>&pi;</mi>
                                    </mrow>
                                  </msup>
                                  <mo>+</mo>
                                  <mn>1</mn>
                                  <mo>=</mo>
                                  <mn>?</mn>
                                </math>
                           </p> 
                        HTML,
                    ])
                ]);
    }

    protected function allowFirstSchoolLocationToViewCreathlon()
    {
        \tcCore\SchoolLocation::find(1)->allow_creathlon = true;
    }

}
