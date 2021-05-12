<?php

namespace Tests\Feature;


use Illuminate\Database\Eloquent\Model;
use tcCore\Http\Helpers\ActingAsHelper;
use Tests\TestCase;
use tcCore\Http\Helpers\RTTIImportHelper;
use tcCore\SchoolLocationSection;
use tcCore\SchoolLocation;
use tcCore\SchoolClass;
use tcCore\SchoolLocationSchoolYear;
use tcCore\SchoolYear;
use tcCore\Period;
use tcCore\Teacher;
use tcCore\Mentor;
use tcCore\Section;
use tcCore\Student;
use tcCore\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use tcCore\Lib\User\Factory;
use tcCore\User;

class RttiImportTest extends TestCase
{

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /**
     *
     * brincode or external_main_code
     * stored for use in functions
     *
     */
    private $brincode = 0;

    /**
     *
     * Contains the data for test location
     *
     * @var array
     */
    private $location_data = [];

    protected function setUp(): void
    {

        parent::setUp();

//        Artisan::call('config:clear');
//        Artisan::call('test:refreshdb');

        $this->setup_location();
    }

    public function setup_location()
    {

        $this->RTTIImportHelper = RTTIImportHelper::initWithCVS();

        $this->brincode = 8888;

        $this->create_location_and_sections($this->brincode);

        return true;
    }

    protected function create_location_and_sections($brincode)
    {

        $this->location_data = [];

        $this->location_data['school_location_id'] = $this->createSchoolLocation($brincode, '01',
            'RTTI School '.$brincode);
        $this->location_data['schoolbeheerder_id'] = $this->createSchoolbeheerder($brincode,
            $this->location_data['school_location_id']);

        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        $this->location_data['school_year_id'] = $this->createSchoolYear(2020, '2020-2021',
            $this->location_data['school_location_id'], $this->location_data['schoolbeheerder_id']);
        $this->location_data['period_id'] = $this->createPeriod($this->location_data['school_year_id'],
            $this->location_data['schoolbeheerder_id'], 'Current Year', '2020-01-09', '2021-08-31');
        $this->location_data['section_id'] = $this->createSection($this->location_data['school_location_id'], 'Frans');
        $this->location_data['subject_id'] = $this->createSubject('Frans', 'FRS', $this->location_data['section_id']);
        $this->location_data['section_id'] = $this->createSection($this->location_data['school_location_id'], 'Duits');
        $this->location_data['subject_id'] = $this->createSubject('Duits', 'DTS', $this->location_data['section_id']);
        $this->location_data['section_id'] = $this->createSection($this->location_data['school_location_id'],
            'Amparsance');
        $this->location_data['subject_id'] = $this->createSubject('Amp', 'A&M', $this->location_data['section_id']);


        ActingAsHelper::getInstance()->setUser(Auth::user());

        return true;
    }

    public function upload_data($csv_file_content)
    {

        $csv_array = $this->read_data_into_array($csv_file_content);

        $this->RTTIImportHelper->csv_data = $csv_array;
        $this->RTTIImportHelper->csv_data_lines = count($csv_array) - 1;
        $this->RTTIImportHelper->csv_log_path = storage_path('logs/laravel.log');

        $this->RTTIImportHelper->create_tally = ['students' => 0, 'classes' => 0, 'teachers' => 0];
        $this->RTTIImportHelper->delete_tally = ['students' => 0, 'classes' => 0, 'teachers' => 0];

        return $this->RTTIImportHelper->process();
    }

    /**
     *
     * no transactions
     *  The years in the import need to exist
     * *
     * @test
     * */
    public function rtti_import_unknown_school_year()
    {
        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",01,VWO,1,2026-2021,10001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",01,VWO,1,2020-2021,10002,02,,Berend,FransTest,FRS,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);

        $this->assertContains(
            'Het schooljaar 2026 in het bestand kon niet gevonden worden in de database voor de schoollocatie met Brincode 8888 en locatiecode 01. Neem contact op met de schoolbeheerder om het schooljaar te laten aanmaken.',
            $output['errors']
        );

        return true;
    }

    /**
     * no transactions
     * You can't use a school layer year that does not exist
     * *
     * @test
     * */
    public function rtti_import_year_layer_incorrect()
    {
        $user = User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2002',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);
        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,9,2020-2021,10001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);

        $this->assertContains(
            'De les jaar laag 9 is niet correct. De Studierichting (niveau) VWO kan maximaal 6 jaren zijn. Pas dit in het bestand aan of neem contact op met ICT',
            $output['errors']
        );

        return true;
    }

    /**
     * no transactions
     * You can't use a school layer year that does not exist
     * *
     * @test
     * */
    public function rtti_import_will_squawk_when_user_record_for_teacher_is_missing()
    {
        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10301,01,,4Albert,FransTest,A&M,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);

        $this->assertEquals([
            "missing_teachers" => [
                ["Mark", "", "Docent1"]
            ]
        ],
            $output['errors']
        );

        return true;
    }

    /**
     * no transactions
     * You can't use a school layer year that does not exist
     * *
     * @test
     * */
    public function rtti_import_use_amparsand2()
    {
        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        $user = User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2002',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10001,01,,1Albert,FransTest,A&M,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10201,01,,2Albert,FransTest,A&M,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10101,01,,3Albert,FransTest,A&M,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10301,01,,4Albert,FransTest,A&M,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);

        $this->assertStringContainsString('Er zijn 4 leerlingen aangemaakt, 1 docent en 1 klas.', $output['data']);

        return true;
    }

    /**
     * when a import rule is marked als isMentor = 0 the school_class should not be registered as a stam klas tcp-888
     * *
     * @test
     * */
    public function isMentorZeroMeansNoStamClass()
    {
        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        $teacherOne = User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2002',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        $teacherTwo = User::createTeacher([
            'name_first'         => 'Eddy',
            'name_suffix'        => '',
            'name'               => 'Docent2',
            'external_id'        => '2003',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);


        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10001,01,,1Albert,FransTest,A&M,2002,Docent1,,Mark,0\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10001,01,,1Albert,FransTest,A&M,2003,Docent2,,Eddy,1\n";

        $output = $this->upload_data($csv_file_content);

        $this->assertStringContainsString('Er is 1 leerling aangemaakt, 2 docenten en 1 klas.', $output['data']);

        $classTeacherOne = $teacherOne->teacher->map(function(Teacher $teacher) {
            return $teacher->schoolClass;
        })->first(function(SchoolClass $class) {
            return $class->demo == 0;
        });

        $classTeacherTwo = $teacherOne->teacher->map(function(Teacher $teacher) {
            return $teacher->schoolClass;
        })->first(function(SchoolClass $class) {
            return $class->demo == 0;
        });

        // only one class should have been created;
        $this->assertTrue(
            $classTeacherOne->is($classTeacherTwo)
        );
        // because teacher two is the mentor for this class it should be marced as is_main_school_class = 1 not 0
        $this->assertEquals(1, $classTeacherOne->is_main_school_class);

    }
    /**
     * @test geen stamklas als beide docenten 0 hebben
     */
    function when_no_teacher_is_marked_as_mentor_the_school_class_should_not_be_marked_as_main_school_class(){
        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        $teacherOne = User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2002',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        $teacherTwo = User::createTeacher([
            'name_first'         => 'Eddy',
            'name_suffix'        => '',
            'name'               => 'Docent2',
            'external_id'        => '2003',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10001,01,,1Albert,FransTest,A&M,2002,Docent1,,Mark,0\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10001,01,,1Albert,FransTest,A&M,2003,Docent2,,Eddy,0\n";

        $output = $this->upload_data($csv_file_content);

        $this->assertStringContainsString('Er is 1 leerling aangemaakt, 2 docenten en 1 klas.', $output['data']);

        $classTeacherOne = $teacherOne->teacher->map(function(Teacher $teacher) {
            return $teacher->schoolClass;
        })->first(function(SchoolClass $class) {
            return $class->demo == 0;
        });

        $classTeacherTwo = $teacherOne->teacher->map(function(Teacher $teacher) {
            return $teacher->schoolClass;
        })->first(function(SchoolClass $class) {
            return $class->demo == 0;
        });

        // only one class should have been created;
        $this->assertTrue(
            $classTeacherOne->is($classTeacherTwo)
        );
        // because both teachers are not a mentor for this class it should be marked as is_main_school_class = 0
        $this->assertEquals(0, $classTeacherOne->is_main_school_class);


    }


    /**
     *  No transactions
     *
     *   It is not allowed to have more than one schoolyear in your RTTI file
     *
     * @test
     * */
    public function rtti_import_can_have_two_mentors()
    {
        $user = User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2902',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        $user = User::createTeacher([
            'name_first'         => 'Mark1',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2903',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        $user->save();

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10001,01,,Albert,FransTest12,FRS,2902,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10004,01,,XAlbert,FransTest12,FRS,2902,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10005,01,,YAlbert,FransTest12,FRS,2902,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",01,VWO,3,2020-2021,10001,01,,Albert,FransTest12,FRS,2903,Docent1,,Mark1,1";

        $output = $this->upload_data($csv_file_content);


        $class_id = SchoolClass::where('name', 'FransTest12')->value('id');

        $teachers_count = Teacher::where('class_id', $class_id)->count();
        $mentor_count = Mentor::where('school_class_id', $class_id)->count();

        $this->assertEquals(2, $teachers_count);
        $this->assertEquals(2, $mentor_count);

        // remove mentors

        User::createTeacher([
            'name_first'         => 'Steve',
            'name_suffix'        => '',
            'name'               => 'Docent2',
            'external_id'        => '2777',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,3,2020-2021,10001,01,,Albert,FransTest12,FRS,2902,Docent1,,Mark,0\n"
            ."RTTI School,".$this->brincode.",1,VWO,3,2020-2021,10004,01,,XAlbert,FransTest12,FRS,2902,Docent1,,Mark,0\n"
            ."RTTI School,".$this->brincode.",1,VWO,3,2020-2021,10005,01,,YAlbert,FransTest12,FRS,2902,Docent1,,Mark,0\n"
            ."RTTI School,".$this->brincode.",1,VWO,3,2020-2021,10005,01,,YAlbert,FransTest12,FRS,2777,Docent2,,Steve,0\n"
            ."RTTI School,".$this->brincode.",1,VWO,3,2020-2021,10001,01,,Albert,FransTest12,FRS,2903,Docent1,,Mark1,1";

        $output = $this->upload_data($csv_file_content);

        $teachers_count = Teacher::where('class_id', $class_id)->count();
        $mentor_count = Mentor::where('school_class_id', $class_id)->count();


        $this->assertEquals(3, $teachers_count);
        $this->assertEquals(1, $mentor_count);

        return true;
    }

    /**
     *  No transactions
     *
     *   It is not allowed to have more than one schoolyear in your RTTI file
     *
     * @test
     * */
    public function rtti_import_multiple_school_years()
    {
        User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2002',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,3,2020-2021,10001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,3,2021-2022,10002,01,,Berend,FransTest,FRS,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);

        $this->assertContains('Meerdere lesjaren in RTTI bestand 2020,2021', $output['errors']);

        return true;
    }

    /**
     * No transactions
     *
     *  The brin code of the location has to exist
     *  in TC DB
     *
     * @test
     * */
    public function rtti_import_unknown_brin_code()
    {
        User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2002',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,0000,1,VWO,1,2020-2021,10001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,0000,1,VWO,1,2020-2021,10002,02,,Berend,FransTest,FRS,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);

        $this->assertContains(
            'De Brincode/locatiecode 0000 01 in het bestand kon niet gevonden worden in de database. Vraag aan de Test-Correct admin om een schoollocatie aan te maken met de juiste Brincode en locatiecode.',
            $output['errors']
        );

        return true;
    }

    /**
     *
     *
     *  The subject must be known in the DB
     *
     *
     * @test
     * */
    public function rtti_import_test_subject_lookup()
    {

        $helper = RTTIImportHelper::initWithCVS();

        $output = $helper->getSubjectId('NED', 1);

        $this->assertEquals(1, $output);
    }

    /**
     *
     *
     *  The subject must be known in the DB
     *
     *
     * @test
     * */
    public function rtti_import_test_subject_unknown()
    {

        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        $helper = RTTIImportHelper::initWithCVS();

        $output = $helper->getSubjectId('XXX', 4);

        $this->assertEquals(null, $output);
    }

    /**
     * No transaction
     *
     *  The subject must be known in the DB
     *
     *
     * @test
     * */
    public function rtti_import_unknown_subject()
    {
        User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2002',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,10001,01,,Albert,FransTest,ERR,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,10002,02,,Berend,FransTest,ERR,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);

        $this->assertContains(
            'Het vak met de afkorting ERR in het bestand kon niet gevonden worden in de database voor de schoollocatie met Brincode/locatiecode: 8888 01. Neem contact op met de schoolbeheerder om het vak te laten aanmaken',
            $output['errors']
        );

        return true;
    }

    /**
     *
     *  Testing the creation of teachers and students
     *
     * @test
     * */
    public function rtti_import_test_class_with_protection()
    {
        User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2002',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        // 3 non-existent class created for schoolyear

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12002,02,,Berend,FransTest,FRS,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);
        $this->assertStringContainsString('Er zijn 2 leerlingen aangemaakt, 1 docent en 1 klas.', $output['data']);

        $class = SchoolClass::where('name', 'FransTest')->first();//->value('id');


        $this->assertEquals(2, $class->studentUsers()->count());//Student::where('class_id', $class_id)->count());

        // try to remove one student
        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1";
        // student Berend no longer in this class

        $output = $this->upload_data($csv_file_content);
        $this->assertStringContainsString(
            'Versie 0.1. De import was succesvol. Er zijn 0 leerlingen aangemaakt, 0 docenten en 0 klassen.',
            $output['data']
        );
        $this->assertEquals(1, $class->studentUsers()->count());
        $this->assertEquals('Albert', $class->studentUsers()->first()->name_first);

        // protect this class against removal
        $class->do_not_overwrite_from_interface = 1;
        $class->save();

        User::createTeacher([
            'name_first'         => 'Fred',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2003',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12001,01,,Albert,FransTest_2,FRS,2003,Docent1,,Fred,1";
        $output = $this->upload_data($csv_file_content);
        // check if created
        $this->assertStringContainsString('0 leerlingen aangemaakt, 1 docent en 1 klas', $output['data']);
        // student Berend no longer in this class

        // check class was created;
        $this->assertNotEmpty(SchoolClass::where('name', 'FransTest')->first());
        // check if other class still exists
        $this->assertNotEmpty($class = SchoolClass::where('name', 'FransTest')->first());
    }

    /**
     *
     *  Testing the creation of teachers and students
     *
     * @test
     * */
    public function rtti_import_test_class_without_protection()
    {
        User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2002',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        User::createTeacher([
            'name_first'         => 'Fred',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2003',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12002,02,,Berend,FransTest,FRS,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);
        $this->assertStringContainsString('Er zijn 2 leerlingen aangemaakt, 1 docent en 1 klas.', $output['data']);

        $class = SchoolClass::where('name', 'FransTest')->first();
        $this->assertEquals(2, $class->studentUsers()->count());//Student::where('class_id', $class_id)->count());

        // remove protection
        $class->do_not_overwrite_from_interface = 0;
        $class->save();

        $this->assertNotNull(SchoolClass::find($class->getKey()));

        $fred = User::where(['name_first' => 'Fred', 'name' => 'Docent1'])->first();
        $classFransTest = ($fred->teacherSchoolClasses()->get()->first(function ($class) {
            return $class->name === 'FransTest_2';
        }));
        // fred has no class with name FransTest_2
        $this->assertNull($classFransTest);

        // load other class again
        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12001,01,,Albert,FransTest_2,FRS,2003,Docent1,,Fred,1";

        $output = $this->upload_data($csv_file_content);
        // the old class had been deleted;
        $class = SchoolClass::withTrashed()->find($class->getKey());
        $this->assertTrue($class->trashed());
        $fred = User::where(['name_first' => 'Fred', 'name' => 'Docent1'])->first();
        // the new class has been created;
        $classFransTest = ($fred->teacherSchoolClasses()->get()->first(function ($class) {
            return $class->name === 'FransTest_2';
        }));
        $this->assertInstanceOf(SchoolClass::class, $classFransTest);

        $this->assertStringContainsString(
            'Versie 0.1. De import was succesvol. Er zijn 0 leerlingen aangemaakt, 1 docent en 1 klas. Er zijn 0 leerlingen verwijderd, 0 docenten en 1 klas.',
            $output['data']
        );
    }

    /**
     *
     *  Testing the creation of teachers and students
     *
     * @test
     * */
    public function rtti_import_can_load_multiple_students_and_subjects()
    {
        User::createTeacher([
            'name_first'         => 'Mark',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2002',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);


        User::createTeacher([
            'name_first'         => 'Pete',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2012',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);


        User::createTeacher([
            'name_first'         => 'Steve',
            'name_suffix'        => '',
            'name'               => 'Docent1',
            'external_id'        => '2013',
            'school_location_id' => $this->location_data['school_location_id'],
        ]);

        Auth::loginUsingId($this->location_data['schoolbeheerder_id']);

        // 3 non-existent class created for schoolyear

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12002,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12003,01,,Albert,FransTest2,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12004,01,,Albert,FransTest,DTS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12005,01,,Albert,Duitstest1,DTS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12006,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12007,01,,Albert,Duitstest1,DTS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12005,01,,Albert,Duitstest2,DTS,2012,Docent1,,Pete,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12006,01,,Albert,Duitstest2,DTS,2012,Docent1,,Pete,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12007,01,,Albert,Duitstest2,DTS,2012,Docent1,,Pete,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12008,02,,Berend,FransTest2,FRS,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);
        $this->assertStringContainsString('Er zijn 8 leerlingen aangemaakt, 5 docenten en 4 klassen. Er zijn 0 leerlingen verwijderd, 0 docenten en 0 klassen.', $output['data']);
        $class_id = SchoolClass::where('name', 'FransTest')
            ->value('id');

        $student_count = Student::where('class_id', $class_id)->count();

        $this->assertEquals(4, $student_count);

        $csv_file_content = "Schoolnaam,Brincode,Locatiecode,Studierichting,lesJaarlaag,Schooljaar,leeStamNummer,leeAchternaam,leeTussenvoegsels,leeVoornaam,lesNaam,vakNaam,docStamNummer,docAchternaam,docTussenvoegsels,docVoornaam,IsMentor\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12001,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12005,01,,Albert,Duitstest1,DTS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12006,01,,Albert,FransTest,FRS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12007,01,,Albert,Duitstest1,DTS,2002,Docent1,,Mark,1\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12005,01,,Albert,Duitstest2,DTS,2013,Docent1,,Steve,0\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12006,01,,Albert,Duitstest2,DTS,2013,Docent1,,Steve,0\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12007,01,,Albert,Duitstest2,DTS,2013,Docent1,,Steve,0\n"
            ."RTTI School,".$this->brincode.",1,VWO,1,2020-2021,12008,02,,Berend,FransTest2,FRS,2002,Docent1,,Mark,1";

        $output = $this->upload_data($csv_file_content);

        $this->assertStringContainsString('Er zijn 0 leerlingen aangemaakt, 1 docent en 0 klassen', $output['data']);
    }

    protected function verbose_out($string)
    {

        fwrite(STDERR, print_r($string, true));
    }

    protected function createSchoolYear($year, $year_name, $school_location_id, $school_beheerder_id)
    {

        $school_year = SchoolYear::create(['year' => $year]);

        $school_location_school_year = SchoolLocationSchoolYear::create([
            'school_location_id' => $school_location_id, 'school_year_id' => $school_year->id
        ])->getKey();

        return $school_location_school_year['school_year_id'];
    }

    protected function createSchoolLocation($brincode, $location, $name)
    {

        $data = [];
        $data['name'] = $name;
        $data['school_id'] = '';
        $data['education_levels'] = '';
        $data['customer_code'] = $name;
        $data['user_id'] = '519';
        $data['grading_scale_id'] = '1';
        $data['activated'] = '0';
        $data['number_of_students'] = '1';
        $data['number_of_teachers'] = '1';
        $data['external_main_code'] = $brincode;
        $data['external_sub_code'] = $location;
        $data['is_rtti_school_location'] = '0';
        $data['is_rtti_school_location'] = '1';
        $data['is_open_source_content_creator'] = '0';
        $data['is_allowed_to_view_open_source_content'] = '0';
        $data['main_address'] = '1';
        $data['invoice_address'] = '1';
        $data['visit_address'] = '1';
        $data['main_postal'] = '1';
        $data['invoice_postal'] = '1';
        $data['visit_postal'] = '1';
        $data['main_city'] = '1';
        $data['invoice_city'] = '1';
        $data['visit_city'] = '1';
        $data['main_country'] = '1';
        $data['invoice_country'] = '1';
        $data['visit_country'] = '1';

        $school_location_id = SchoolLocation::create($data)->id;

        return $school_location_id;
    }

    protected function createSchoolbeheerder($external_main_code, $school_location_id)
    {

        $data = [];
        $data['school_location_id'] = $school_location_id;
        $data['name_first'] = 'Mr';
        $data['name_suffix'] = 'de';
        $data['name'] = 'Schoolbeheerder';
        $data['user_roles'] = '6';
        $data['username'] = $external_main_code.'@testimport.nl';
        $data['password'] = 'Repelsteeltje';

        $userFactory = new Factory(new User());

        $user = $userFactory->generate($data);

        return $user->id;
    }

    protected function createSection($school_location_id, $section_name)
    {

        $section = Section::create(['name' => $section_name]);

        SchoolLocationSection::firstOrCreate([
            'school_location_id' => $school_location_id,
            'section_id'         => $section->id
        ], []
        );

        return $section->id;
    }

    protected function createSubject($subject_name, $abbreviation, $section_id)
    {

        $subject = Subject::create([
            'section_id'      => $section_id,
            'base_subject_id' => 30,
            'name'            => $subject_name,
            'abbreviation'    => $abbreviation
        ])->getKey();

        return $subject;
    }

    protected function createPeriod($school_year_id, $schoolbeheerder_id, $name, $start_date, $end_date)
    {

        Auth::loginUsingId($schoolbeheerder_id);

        return Period::create([
            'school_year_id' => $school_year_id, 'name' => $name, 'start_date' => $start_date, 'end_date' => $end_date
        ])->getKey();
    }

    public function read_data_into_array($data)
    {

        $rows = explode("\n", $data);

        $csv_array = [];

        foreach ($rows as $row) {
            $csv_row = str_getcsv($row, ',');
            if ($csv_row[0] != null) {
                $csv_array[] = $csv_row;
            }
        }

        return $csv_array;
    }

    private function assertErrorsContainsString($string, $errors)
    {
        if (is_string($errors)) {
            $this->assertStringContainsString($string, $errors);
        }
        $pass = false;
        foreach ($errors as $error) {
            if (stristr($string, $error)) {
                $pass = true;
                break;
            }
        }
        $this->assertTrue($pass);
    }
}
