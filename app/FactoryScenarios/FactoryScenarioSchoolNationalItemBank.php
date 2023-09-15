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
use tcCore\Services\ContentSource\FormidableService;
use tcCore\Services\ContentSource\NationalItemBankService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\User;

class FactoryScenarioSchoolNationalItemBank extends FactoryScenarioSchool
{
    public $schoolName;

    public $schoolLocationName;

    public $sectionName;

    public $schoolClassName;

    public $customer_code;

    public $teacher_one;
    public $school_location_abc;

    public function __construct()
    {
        parent::__construct();

        $this->schoolName = 'NationalItemBank vragencontent';

        $this->schoolLocationName = 'NationalItemBank';

        $this->sectionName = 'NationalItemBank section';

        $this->schoolClassName = 'NationalItemBank school class';

        $this->customer_code = config('custom.national_item_bank_school_customercode');
    }

    public static function create()
    {
        $factory = new static;

        SchoolLocationCreator::createNationalItemBankSchool($factory);
        SchoolLocationCreator::createSimpleSchoolWithOneTeacher($factory);

        $factory->teachers->add($factory->teacher_one);
//        $factory->school_location_abc = $schoolLocation;

        return $factory;
    }

    public function getData()
    {
        return parent::getData() + [
                'teacherOne'          => $this->teacher_one,
                'school_location_abc' => $this->school_location_abc,
            ];
    }
}
