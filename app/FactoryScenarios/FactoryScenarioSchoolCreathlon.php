<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
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
use tcCore\Services\ContentSource\CreathlonService;
use tcCore\User;

class FactoryScenarioSchoolCreathlon extends FactoryScenarioSchool
{
    public $schoolName;
    public $schoolLocationName;
    public $schoolYearYear;

    public $sectionName;

    public $schoolClassName;

    public $customer_code;

    public $teacher_one;

    public $baseSubjectId = BaseSubject::FRENCH;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'Creahtlon vragencontent';

        $this->schoolLocationName = 'Creahtlon vragencontent';

        $this->sectionName = 'Creathlon section';

        $this->schoolClassName = 'Creathlon school class';

        $this->customer_code = 'CREATHLON';
    }

    public static function create()
    {
        $factory = new static;

        SchoolLocationCreator::createCreathlonSchool($factory);
        SchoolLocationCreator::createSimpleSchoolWithOneTeacher($factory);

        return $factory;
    }

    public function getData()
    {
        return parent::getData() + [
                'teacherOne'          => $this->teacher_one,
            ];
    }
}
