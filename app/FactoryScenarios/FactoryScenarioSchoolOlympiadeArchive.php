<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use tcCore\BaseSubject;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryUser;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Factories\SchoolLocationCreator;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Services\ContentSource\OlympiadeService;
use tcCore\User;

class FactoryScenarioSchoolOlympiadeArchive extends FactoryScenarioSchool
{
    public $schoolName;
    public $schoolLocationName;
    public $schoolYearYear;

    public $sectionName;

    public $schoolClassName;

    public $customer_code;
    public $teacher_one;



    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Olympiade content';

        $this->schoolLocationName = 'Olympiade Archive';

        $this->sectionName = 'Olympiade Archive section';

        $this->schoolClassName = 'Olympiade Archive school class';

        $this->customer_code = config('custom.olympiade_archive_school_customercode');
    }

    public static function create()
    {
        $factory = new static;
        SchoolLocationCreator::createOlympiadeArchiveSchool($factory);

        SchoolLocationCreator::createSimpleSchoolWithOneTeacher($factory);

        $factory->teachers->add($factory->teacher_one);

        return $factory;
    }

    public function createUsernameForSecondUser($username): string
    {
        return Arr::join([
            Str::before($username, '@'),
            '-B',
            '@',
            Str::after($username, '@'),
        ], '');
    }

    public function getData()
    {
        return parent::getData() + [
                'teacherOne'          => $this->teacher_one,
            ];
    }


}
