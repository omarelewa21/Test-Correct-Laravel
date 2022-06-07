<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryUser;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

/**
 * Scenario based on written testScenario
 * Education level was not specified, so all will use '1' VWO
 *
 * school 1: school_location 1, school_location 2
 * school 2: school_location 3
 *
 * all school_locations: 3 education levels: 1, 2, 3
 *                       3 school years, this year, last year and the year before last year
 *
 */
class FactoryScenarioSchool001 extends FactoryScenarioSchool
{

    public function __construct()
    {
        parent::__construct();



        //change the following values to customize the scenario:
        $this->school1Name = 'F900';
        $this->school1Location1Name = 'F999';
        $this->school1Location2Name = 'F990';
        $this->school2Name = 'F600';
        $this->school2Location1Name = 'F666';

        //continues in $this->setupProperties()...
    }

    protected function setupProperties()
    {
        $current_year = Carbon::today()->format('Y');
        $last_year = Carbon::today()->subYear()->format('Y');
        $year_before_last_year = Carbon::today()->subYears(2)->format('Y');

        $this->teacher1Properties = [
            'name_first'   => 'Piet',
            'name'         => 'Docent 1',
            'abbreviation' => 'DP',
            'username'     => $this->school1Location1Name . '-' . $this->school1Location2Name . '_DocentPiet@factory.test',
        ];
        $this->teacher2Properties = [
            'name_first'   => 'Jan',
            'name'         => 'Docent 2',
            'abbreviation' => 'DJ',
            'username'     => $this->school1Location1Name . '_DocentJan@factory.test',
        ];
        $this->teacher3Properties = [
            'name_first'   => 'Klaas',
            'name'         => 'Docent 3',
            'abbreviation' => 'DK',
            'username'     => $this->school1Location1Name . '_DocentKlaas@factory.test',
        ];
        $this->teacher4Properties = [
            'name_first'   => 'Nienke',
            'name'         => 'Docent 4',
            'abbreviation' => 'DN',
            'username'     => $this->school1Location2Name . '_DocentNienke@factory.test',
            'gender'       => 'Female', //default: 'Male'
        ];
        $this->teacher5Properties = [
            'name_first'   => 'Johan',
            'name'         => 'Docent 5',
            'abbreviation' => 'DJO',
            'username'     => $this->school2Location1Name . '_DocentJohan@factory.test',
        ];

        $this->school1Location1SchoolClass1CurrentYearName = 'Klas 1 ' . $this->school1Location1Name . ' ' . $current_year;
        $this->school1Location1SchoolClass2PreviousYearName = 'Klas 2 ' . $this->school1Location1Name . ' ' . $last_year;
        $this->school1Location1SchoolClass3YearBeforeLastYearName = 'Klas 3 ' . $this->school1Location1Name . ' ' . $year_before_last_year;

        $this->school1Location2SchoolClass1CurrentYearName = 'Klas 1 ' . $this->school1Location2Name . ' ' . $current_year;

        $this->school2Location1SchoolClass1CurrentYearName = 'Klas 1 ' . $this->school2Location1Name . ' ' . $current_year;

        //school 1 Location 1 Students This year
        $number = 1;
        $this->school1Location1StudentThisYear1 = [
            'name_first'   => 'Student',
            'name'         => 'Student ' . $number,
            'abbreviation' => 'S' . $number,
            'username'     => $this->school1Location1Name . '_Student' . $number++ . '' . '@factory.test',
        ];
        $this->school1Location1StudentThisYear2 = [
            'name_first'   => 'Student',
            'name'         => 'Student ' . $number,
            'abbreviation' => 'S' . $number,
            'username'     => $this->school1Location1Name . '_Student' . $number++ . '' . '@factory.test',
        ];
        $this->school1Location1StudentThisYear3 = [
            'name_first'   => 'Student',
            'name'         => 'Student ' . $number,
            'abbreviation' => 'S' . $number,
            'username'     => $this->school1Location1Name . '_Student' . $number++ . '' . '@factory.test',
        ];

        //school 1 Location 1 Students Last Year
        //$number = 1; //uncomment to start numbering from 1 again, old students are now 4,5,6 etc.
        $this->school1Location1StudentLastYear1 = [
            'name_first'   => 'Student',
            'name'         => 'VorigJaarStudent ' . $number,
            'abbreviation' => 'VJS' . $number,
            'username'     => $this->school1Location1Name . '_VorigJaarStudent' . $number++ . '' . '@factory.test',
        ];
        $this->school1Location1StudentLastYear2 = [
            'name_first'   => 'Student',
            'name'         => 'VorigJaarStudent ' . $number,
            'abbreviation' => 'VJS' . $number,
            'username'     => $this->school1Location1Name . '_VorigJaarStudent' . $number++ . '' . '@factory.test',
        ];
        $this->school1Location1StudentLastYear3 = [
            'name_first'   => 'Student',
            'name'         => 'VorigJaarStudent ' . $number,
            'abbreviation' => 'VJS' . $number,
            'username'     => $this->school1Location1Name . '_VorigJaarStudent' . $number++ . '' . '@factory.test',
        ];

        //school 1 Location 1 Students Year before Last Year
        //$number = 1;
        $this->school1Location1StudentYearBeforeLastYear1 = [
            'name_first'   => 'Student',
            'name'         => 'OudStudent ' . $number,
            'abbreviation' => 'OS' . $number,
            'username'     => $this->school1Location1Name . '_TweeJaarGeledenStudent' . $number++ . '' . '@factory.test',
        ];
        $this->school1Location1StudentYearBeforeLastYear2 = [
            'name_first'   => 'Student',
            'name'         => 'OudStudent ' . $number,
            'abbreviation' => 'OS' . $number,
            'username'     => $this->school1Location1Name . '_TweeJaarGeledenStudent' . $number++ . '' . '@factory.test',
        ];
        $this->school1Location1StudentYearBeforeLastYear3 = [
            'name_first'   => 'Student',
            'name'         => 'OudStudent ' . $number,
            'abbreviation' => 'OS' . $number,
            'username'     => $this->school1Location1Name . '_TweeJaarGeledenStudent' . $number++ . '' . '@factory.test',
        ];

        //school 1 Location 2 Students This year
        $number = 1;
        $this->school1Location2StudentThisYear1 = [
            'name_first'   => 'Student',
            'name'         => 'Student ' . $number,
            'abbreviation' => 'S' . $number,
            'username'     => $this->school1Location2Name . '_Student' . $number++ . '' . '@factory.test',
        ];
        $this->school1Location2StudentThisYear2 = [
            'name_first'   => 'Student',
            'name'         => 'Student ' . $number,
            'abbreviation' => 'S' . $number,
            'username'     => $this->school1Location2Name . '_Student' . $number++ . '' . '@factory.test',
        ];
        $this->school1Location2StudentThisYear3 = [
            'name_first'   => 'Student',
            'name'         => 'Student ' . $number,
            'abbreviation' => 'S' . $number,
            'username'     => $this->school1Location2Name . '_Student' . $number++ . '' . '@factory.test',
        ];

        //school 2 Location 1 Students This year
        $number = 1;
        $this->school2Location1StudentThisYear1 = [
            'name_first'   => 'Student',
            'name'         => 'Student ' . $number,
            'abbreviation' => 'S' . $number,
            'username'     => $this->school2Location1Name . '_Student' . $number++ . '' . '@factory.test',
        ];
        $this->school2Location1StudentThisYear2 = [
            'name_first'   => 'Student',
            'name'         => 'Student ' . $number,
            'abbreviation' => 'S' . $number,
            'username'     => $this->school2Location1Name . '_Student' . $number++ . '' . '@factory.test',
        ];
        $this->school2Location1StudentThisYear3 = [
            'name_first'   => 'Student',
            'name'         => 'Student ' . $number,
            'abbreviation' => 'S' . $number,
            'username'     => $this->school2Location1Name . '_Student' . $number++ . '' . '@factory.test',
        ];
    }

    protected function checkIfScenarioAlreadyExists($letter = '')
    {
        $exists = 0;
        $exists += School::whereIn('name', [
            $this->school1Name . $letter,
            $this->school2Name . $letter,
        ])->count();
        $exists += SchoolLocation::whereIn('name', [
            $this->school1Location1Name . $letter,
            $this->school1Location2Name . $letter,
            $this->school2Location1Name . $letter,
        ])->count();

        if ($exists) {
            $this->generateNewName();
        }
    }

    protected function generateNewName($letter = 'A')
    {
        if (School::where('name', $this->school1Name . $letter)->count()) {
            $this->generateNewName(++$letter);
        }
        else {
            $this->school1Name .= $letter;
            $this->school2Name .= $letter;
            $this->school1Location1Name .= $letter;
            $this->school1Location2Name .= $letter;
            $this->school2Location1Name .= $letter;
        }
    }


    public static function create()
    {
        $thisYear = (int)Carbon::today()->format('Y');
        $lastYear = (int)Carbon::today()->subYear()->format('Y');
        $yearBeforeLastYear = (int)Carbon::today()->subYears(2)->format('Y');

        $factory = new static;

        $factory->checkIfScenarioAlreadyExists();

        $factory->setupProperties();

        //create school one
        $school1 = FactorySchool::create($factory->school1Name)->school;
        $schoolLocation1 = FactorySchoolLocation::create($school1, $factory->school1Location1Name)->addEducationlevels([1, 2, 3])->schoolLocation;
        //create 3 schoolyears with 1 period each for school location
        $schoolYearLocation1currentYear = FactorySchoolYear::create($schoolLocation1, $thisYear)
            ->addPeriodFullYear()->schoolYear;
        $schoolYearLocation1LastYear = FactorySchoolYear::create($schoolLocation1, $lastYear)
            ->addPeriodFullYear()->schoolYear;
        $schoolYearLocation1YearBeforeLastYear = FactorySchoolYear::create($schoolLocation1, $yearBeforeLastYear)
            ->addPeriodFullYear()->schoolYear;

        $schoolLocation2 = FactorySchoolLocation::create($school1, $factory->school1Location2Name)->addEducationlevels([1, 2, 3])->schoolLocation;
        //create 3 schoolyears with 1 period each for school location
        $schoolYearLocation2currentYear = FactorySchoolYear::create($schoolLocation2, $thisYear)
            ->addPeriodFullYear()->schoolYear;
        $schoolYearLocation2LastYear = FactorySchoolYear::create($schoolLocation2, $lastYear)
            ->addPeriodFullYear()->schoolYear;
        $schoolYearLocation2YearBeforeLastYear = FactorySchoolYear::create($schoolLocation2, $yearBeforeLastYear)
            ->addPeriodFullYear()->schoolYear;

        //create school two unrelated to the first
        $school2 = FactorySchool::create($factory->school2Name)->school;
        $schoolLocation3 = FactorySchoolLocation::create($school2, $factory->school2Location1Name)->addEducationlevels([1, 2, 3])->schoolLocation;
        //create 3 schoolyears with 1 period each for school location
        $schoolYearLocation3currentYear = FactorySchoolYear::create($schoolLocation3, $thisYear)
            ->addPeriodFullYear()->schoolYear;
        $schoolYearLocation3LastYear = FactorySchoolYear::create($schoolLocation3, $lastYear)
            ->addPeriodFullYear()->schoolYear;
        $schoolYearLocation3YearBeforeLastYear = FactorySchoolYear::create($schoolLocation3, $yearBeforeLastYear)
            ->addPeriodFullYear()->schoolYear;


        //add sections SCHOOL LOCATION 1
        //piet
        //      Subject:                    BaseSubjects:       Sectie:
        //      Russisch                    =>  Russisch        =>  Russisch
        //      Russische literatuur        =>  Russisch        =>  Russisch
        $location1sectionRussisch = FactorySection::create($schoolLocation1, 'Russisch')
            ->addSubject(FactoryBaseSubject::find(83), 'Russisch', 'RUS')
            ->addSubject(FactoryBaseSubject::find(83), 'Russische literatuur', 'RUL')
            ->section;
        //      Klassiek Turks              =>  Turks           =>  Turks
        $location1sectionTurks = FactorySection::create($schoolLocation1, 'Turks')
            ->addSubject(FactoryBaseSubject::find(84), 'Klassiek Turks', 'KTU')
            ->section;
        //      Italiaanse literatuur       =>  Italiaans       =>  Italiaans (76 basesubject)
        $location1sectionItaliaans = FactorySection::create($schoolLocation1, 'Italiaans')
            ->addSubject(FactoryBaseSubject::find(76), 'Italiaanse literatuur', 'ITL')
            ->section;
        //      Chinees                     =>  Chinees         =>  Chinees (71 basesubject)
        $location1sectionChinees = FactorySection::create($schoolLocation1, 'Chinees')
            ->addSubject(FactoryBaseSubject::find(71), 'Chinees', 'CHI')
            ->section;
        //klaas
        //      Spaans                      =>  Spaans          =>  Spaans (25 basesubject)
        $location1sectionSpaans = FactorySection::create($schoolLocation1, 'Spaans')
            ->addSubject(FactoryBaseSubject::find(25), 'Spaans', 'SPA')
            ->section;

        //add sections SCHOOL LOCATION 2
        //piet
        //      Subject:                    BaseSubjects:       Sectie:
        //      Modern Turks                =>  Turks           =>  Turks
        $location2sectionTurks = FactorySection::create($schoolLocation2, 'Turks')
            ->addSubject(FactoryBaseSubject::find(84), 'Modern Turks', 'MTU')
            ->section;
        //      Kunstzinnige vormen         =>  Kunst           =>  Kunst (basesubject 77 is 'Kunst (Algemeen)' )
        $location2sectionKunst = FactorySection::create($schoolLocation2, 'Kunstzinnige vormen')
            ->addSubject(FactoryBaseSubject::find(77), 'Kunst', 'KUN')
            ->section;
        //Nienke
        //      Chinese leesvaardigheid     =>  Chinees         =>  Chinees
        //      Chinese tekstverwerking     =>  Chinees         =>  Chinees
        $location2sectionChinees = FactorySection::create($schoolLocation2, 'Chinees')
            ->addSubject(FactoryBaseSubject::find(71), 'Chinese leesvaardigheid', 'CHL')
            ->addSubject(FactoryBaseSubject::find(71), 'Chinese tekstverklaring', 'CHT')
            ->addSharedSchoolLocation($schoolLocation1)
            ->section;

        //add sections SCHOOL LOCATION 3
        //Johan
        //      Subject:                    BaseSubjects:       Sectie:
        //      Italiaanse literatuur       =>  Italiaans       =>  Italiaans
        $location3sectionItaliaans = FactorySection::create($schoolLocation3, 'Italiaans')
            ->addSubject(FactoryBaseSubject::find(76), 'Italiaanse literatuur', 'ITL')
            ->section;

        //create teacher(s) SCHOOL LOCATION 1
        //Piet (+ add to second school location)
        $teacherUser1Location1 = FactoryUser::createTeacher($schoolLocation1, true, $factory->teacher1Properties)
            ->addSchoolLocation($schoolLocation2)
            ->user;
        //Jan
        $teacherUser2Location1 = FactoryUser::createTeacher($schoolLocation1, true, $factory->teacher2Properties)
            ->user;
        //Klaas
        $teacherUser3Location1 = FactoryUser::createTeacher($schoolLocation1, true, $factory->teacher3Properties)
            ->user;

        //create teacher(s) SCHOOL LOCATION 2
        //Nienke
        $teacherUser1Location2 = FactoryUser::createTeacher($schoolLocation2, true, $factory->teacher4Properties)
            ->user;

        //create teacher(s) SCHOOL LOCATION 3
        //Johan
        $teacherUser1Location3 = FactoryUser::createTeacher($schoolLocation3, true, $factory->teacher5Properties)
            ->user;

        //Create school class(es) SCHOOL LOCATION 1
        //Piet, current year,           Klassiek Turks
        //Piet, current year,           Italiaanse literatuur
        //Piet, current year,           Chinees
        //Jan, current year,
        //Klaas, current year,
        $schoolClass1Location1 = FactorySchoolClass::create($schoolYearLocation1currentYear, 1, $factory->school1Location1SchoolClass1CurrentYearName)
            ->addTeacher($teacherUser1Location1, $location1sectionTurks->subjects->first())
            ->addTeacher($teacherUser1Location1, $location1sectionItaliaans->subjects->first())
            ->addTeacher($teacherUser1Location1, $location1sectionChinees->subjects->first())
            ->addTeacher($teacherUser2Location1, $location1sectionChinees->subjects->first())
            ->addTeacher($teacherUser3Location1, $location1sectionSpaans->subjects->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation1, $factory->school1Location1StudentThisYear1)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation1, $factory->school1Location1StudentThisYear2)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation1, $factory->school1Location1StudentThisYear3)->user);

        //Piet, last year,              Russisch
        $schoolClass2Location1 = FactorySchoolClass::create($schoolYearLocation1LastYear, 1, $factory->school1Location1SchoolClass2PreviousYearName)
            ->addTeacher($teacherUser1Location1, $location1sectionRussisch->subjects()->where('name', 'not like', '%ische lit%')->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation1, $factory->school1Location1StudentLastYear1)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation1, $factory->school1Location1StudentLastYear2)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation1, $factory->school1Location1StudentLastYear3)->user);

        //Piet, year before last year,  Russische literatuur
        $schoolClass3Location1 = FactorySchoolClass::create($schoolYearLocation1YearBeforeLastYear, 1, $factory->school1Location1SchoolClass3YearBeforeLastYearName)
            ->addTeacher($teacherUser1Location1, $location1sectionRussisch->subjects()->where('name', 'like', '%ische lit%')->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation1, $factory->school1Location1StudentYearBeforeLastYear1)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation1, $factory->school1Location1StudentYearBeforeLastYear2)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation1, $factory->school1Location1StudentYearBeforeLastYear3)->user);

        //Create school class(es) SCHOOL LOCATION 2
        //Piet, current year,           Modern Turks
        //Piet, current year,           Kunstzinnige Vormen
        //Nienke, current year,         Chinese leesvaardigheid
        //Nienke, current year,         Chinese tekstverklaring
        $schoolClass1Location2 = FactorySchoolClass::create($schoolYearLocation2currentYear, 1, $factory->school1Location2SchoolClass1CurrentYearName)
            ->addTeacher($teacherUser1Location1, $location2sectionTurks->subjects->first())
            ->addTeacher($teacherUser1Location1, $location2sectionKunst->subjects->first())
            ->addTeacher($teacherUser1Location2, $location2sectionChinees->subjects()->where('name', 'like', '%nese lees%')->first())
            ->addTeacher($teacherUser1Location2, $location2sectionChinees->subjects()->where('name', 'like', '%nese tekst%')->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation2, $factory->school1Location2StudentThisYear1)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation2, $factory->school1Location2StudentThisYear2)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation2, $factory->school1Location2StudentThisYear3)->user);

        //Create school class(es) SCHOOL LOCATION 3
        //Johan, current year,          Italiaanse literatuur
        $schoolClass1Location3 = FactorySchoolClass::create($schoolYearLocation3currentYear, 1, $factory->school2Location1SchoolClass1CurrentYearName)
            ->addTeacher($teacherUser1Location3, $location3sectionItaliaans->subjects->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation3, $factory->school2Location1StudentThisYear1)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation3, $factory->school2Location1StudentThisYear2)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation3, $factory->school2Location1StudentThisYear3)->user);

        $factory->schools->add($school1)->add($school2);

        //create Tests
        $factory->seedTests();

        return $factory;
    }
}