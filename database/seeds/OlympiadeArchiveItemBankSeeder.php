<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\FactoryScenarios\FactoryScenarioSchoolCreathlon;
use tcCore\FactoryScenarios\FactoryScenarioSchoolOlympiade;
use tcCore\FactoryScenarios\FactoryScenarioSchoolOlympiadeArchive;
use tcCore\User;

class OlympiadeArchiveItemBankSeeder extends Seeder
{
    public $olympiadeArchiveAuthorUserName;
    public $olympiadeArchiveAuthorBUserName;
    public $olympiadeArchiveCustomerCode;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //todo implement username in config
        //  implement allow_olympiade

        $this->olympiadeAuthorUserName = config('custom.olympiade_archive_school_author');
        $this->olympiadeAuthorBUserName = $this->createUsernameForSecondUser(
            config('custom.olympiade_archive_school_author')
        );
        $this->olympiadeCustomerCode = config('custom.olympiade_archive_school_customercode');

        \tcCore\Http\Helpers\ActingAsHelper::getInstance()->reset();

        if (User::where('username', config('custom.olympiade_archive_school_author'))->exists()) {
            return;
        }

        $this->generateSchoolWithTests();

        $this->allowFirstSchoolLocationToViewOlympiadeAchive();
    }

    protected function generateSchoolWithTests()
    {
        $factoryScenarioSchool = FactoryScenarioSchoolOlympiadeArchive::create();
        $school = $factoryScenarioSchool->schools->first();

        $primaryTestAuthor = $school->schoolLocations->first()->users()->where('username', $this->olympiadeArchiveAuthorUserName)->first();
        $secondaryTestAuthor = $school->schoolLocations->first()->users()->where('username', $this->olympiadeArchiveAuthorBUserName)->first();

        $collection = $school->schoolLocations->first()->schoolLocationSections->where('demo', false)->first()->section->subjects->split(2);

        $firstHalf = $collection[0];
        $secondHalf = $collection[1] ?? collect();


        $firstHalf->each(function ($subject) use ($primaryTestAuthor) {
            \tcCore\Factories\FactoryTest::create($primaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $subject->name,
                    'subject_id'         => $subject->id,
                    'abbreviation'       => 'SBON',
                    'scope'              => 'published_olympiade_archive',
                    'education_level_id' => '1',
                    'draft'              => false,
                ])
                ->addQuestions([
                    FactoryQuestionOpenShort::create()->setProperties([
                        "question" => '<p>voorbeeld vraag olympiade archive:</p> <p>wat is de waarde van pi</p> ',
                    ]),
                ]);
        });
        $secondHalf->each(function ($subject) use ($secondaryTestAuthor) {
            \tcCore\Factories\FactoryTest::create($secondaryTestAuthor)
                ->setProperties([
                    'name'               => 'test-' . $subject->name,
                    'subject_id'         => $subject->id,
                    'abbreviation'       => 'UNF',
                    'scope'              => 'not_published_olympiade_archive',
                    'education_level_id' => '1',
                    'draft'              => false,
                ])
                ->addQuestions([
                    FactoryQuestionOpenShort::create()->setProperties([
                        "question" => '<p>voorbeeld vraag olympiade archive:</p> <p>wat is de waarde van pi</p> ',
                    ]),
                ]);
        });
    }

    protected function allowFirstSchoolLocationToViewOlympiadeAchive()
    {
        \tcCore\SchoolLocation::find(1)->allow_olympiade_archive = true;
    }

    /**
     * @param $username
     * @return string
     */
    public function createUsernameForSecondUser($username): string
    {
        return Arr::join([
            Str::before($username, '@'),
            '-B',
            '@',
            Str::after($username, '@'),
        ], '');
    }

}
