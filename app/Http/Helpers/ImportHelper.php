<?php

namespace tcCore\Http\Helpers;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Ramsey\Uuid\Uuid;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\Jobs\CountSchoolLocationActiveTeachers;
use tcCore\Jobs\CountSchoolLocationQuestions;
use tcCore\Jobs\CountSchoolLocationStudents;
use tcCore\Jobs\CountSchoolLocationTeachers;
use tcCore\Jobs\CountSchoolLocationTests;
use tcCore\Jobs\CountSchoolLocationTestsTaken;
use tcCore\Lib\Repositories\StatisticsRepository;
use tcCore\User;
use tcCore\Lib\User\Factory;
use tcCore\Teacher;
use tcCore\Subject;
use tcCore\Mentor;
use tcCore\Student;
use tcCore\SchoolClass;
use tcCore\SchoolLocationSchoolYear;
use tcCore\SchoolLocation;
use Carbon\Carbon;
use tcCore\Http\Requests\Request;
use tcCore\UwlrSoapResult;

class ImportHelper
{

    /**
     *
     * @var string
     */
    public $email_domain = "rttiimport.nl";

    /**
     *
     * @var string
     */
    public $log_name;

    /**
     *
     * @var string
     */
    protected $csv_file_path;

    /**
     *
     * @var int
     */
    public $csv_data;

    /**
     *
     * @var string
     */
    public $csv_file_name;

    /**
     *
     * @var int
     */
    public $csv_data_lines;

    /**
     *
     * @var array
     */
    private $educationLevelArray = [
        'b'   => 'Vmbo bb',
        'b/k' => 'Vmbo kb',
        'k'   => 'Vmbo kb',
        'k/t' => 'Mavo / Vmbo tl',
        't'   => 'Mavo / Vmbo tl',
        'm/h' => 'Havo',
    ];

    /**
     *
     * @var array
     */
    private $errorMessages = [];

    /**
     *
     * @var array counting created students, teachers and classes
     */
    public $create_tally = [
        'students' => 0,
        'classes'  => 0,
        'teachers' => 0,
        'mentors'  => 0,
    ];

    /**
     *
     * @var array counting updated user models for students, teachers and classes
     */
    public $update_tally = [
        'students' => 0,
        'classes'  => 0,
        'teachers' => 0,
        'mentors'  => 0,
    ];
    /**
     *
     * @var bool rtti is not allowed to create users for teachers but magister uwlrImport is
     */
    public $can_create_users_for_teacher = false;

    public $should_use_import_email_pattern = false;

    public $should_use_import_password_pattern = false;

    public $can_find_school_class_only_by_name = false;

    public $can_find_teacher_only_by_class_id = false;

    public $skip_mentor_creation = false;

    public $can_use_dummy_subject = false;

    const DUMMY_SECTION_NAME = 'lvs import sectie';

    public $make_school_classes_visible = true;

    public $uwlr_soap_result_id;

    private $startTime;

    private $cache = [];

    private $cacheHit = 0;

    protected $teachersPerClass = [];

    /**
     *
     * @var array counting soft-deleted teachers and classes
     */
    public $delete_tally = [
        'students' => 0,
        'classes'  => 0,
        'teachers' => 0,
        'mentors'  => 0,
    ];

    public static function initWithUwlrSoapResult(UwlrSoapResult $data, $email_domain)
    {
        $instance = new self($email_domain);
        $instance->can_create_users_for_teacher = true;
        $instance->skip_mentor_creation = true;
        $instance->should_use_import_email_pattern = true;
        $instance->make_school_classes_visible = false;
        if (App::environment(['testing', 'local'])) {
            $instance->should_use_import_password_pattern = true;
        }

        $instance->can_find_school_class_only_by_name = true;
        $instance->can_find_teacher_only_by_class_id = true;

        $instance->can_use_dummy_subject = true;

        $instance->log_name = date("mdh_i_s");

        $instance->csv_data = $data->toCSV();

        $instance->uwlr_soap_result_id = $data->getKey();

        unset($data);

        return $instance;
    }

    private function convert($size)
    {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).' '.$unit[$i];
    }


    public static function initWithCSV($csv_file_path = "", $email_domain = "")
    {
        $instance = new self($email_domain);

        $instance->log_name = date("mdh_i_s");
        $instance->csv_file_path = $csv_file_path;
        $instance->importLog('Loading '.$instance->csv_file_path);

        return $instance;
    }

    private function __construct($email_domain)
    {
        $this->startTime = time();

        if ($email_domain != "") {
            $this->email_domain = $email_domain;
        }
    }

    /**
     *
     * @param  type  $string
     * @return boolean
     */
    public function importLog($string)
    {
//        logger($string);

        return true;
    }

    private function checkAlphaNumericAndSpace($string)
    {
        return preg_match('/^[a-z0-9&; .\-]+$/i', $string);
    }

    private function logMemoryUsage($line)
    {
        $size = memory_get_usage(true);

        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        $usage = @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).' '.$unit[$i];
        $this->importLog(sprintf('[%s of %s] usage [%s] [%s seconds] [%d cacheHits] ', $line, $this->csv_data_lines, $usage,
            time() - $this->startTime, $this->cacheHit));
    }

    /** @noinspection UnsupportedStringOffsetOperationsInspection */
    public function process()
    {

        GlobalStateHelper::getInstance()->setQueueAllowed(false);

        $this->csv_data_lines = count($this->csv_data) - 1;// first row is header

        // Temporary datastore
        $studentsPerClass = [];
        $classTeacherCheck = [];
        $classMentorCheck = [];
        $yearCheck = [];
        $allClasses = [];
        $this->errorMessages = [];

        $this->importLog(sprintf('Total of %d lines to handle', $this->csv_data_lines));


        try {
            foreach ($this->csv_data as $index => $row) {
                if ($index == 0) {
                    $column_index = array_flip($row);
                } else {

                    $this->logMemoryUsage($index);
                    $this->updateUwlrSoapResultProgress($index);

                    $external_main_code = $row[$column_index['Brincode']];
                    $external_sub_code = $row[$column_index['Locatiecode']];

                    $study_direction = $row[$column_index['Studierichting']];
                    $study_year_layer = $row[$column_index['lesJaarlaag']];
                    $study_year = substr($row[$column_index['Schooljaar']], 0, 4);

                    $student_external_code = array_key_exists('leeStamNummer',
                        $column_index) ? $row[$column_index['leeStamNummer']] : null;
                    $student_name_first = $row[$column_index['leeVoornaam']];
                    $student_name_suffix = $row[$column_index['leeTussenvoegsels']];
                    $student_name_last = $row[$column_index['leeAchternaam']];

                    $student_eckid = array_key_exists('leeEckid',
                        $column_index) ? $row[$column_index['leeEckid']] : null;

                    $student_email = array_key_exists('leeEmail',
                        $column_index) ? $row[$column_index['leeEmail']] : null;

                    $class_name = $row[$column_index['lesNaam']];
                    $subject_abbreviation = $row[$column_index['vakNaam']];

                    $teacher_external_code = array_key_exists('docStamNummer',
                        $column_index) ? $row[$column_index['docStamNummer']] : null;
                    $teacher_name_first = $row[$column_index['docVoornaam']];
                    $teacher_name_suffix = $row[$column_index['docTussenvoegsels']];
                    $teacher_name_last = $row[$column_index['docAchternaam']];

                    $teacher_eckid = array_key_exists('docEckid',
                        $column_index) ? $row[$column_index['docEckid']] : null;

                    $teacher_email = array_key_exists('docEmail',
                        $column_index) ? $row[$column_index['docEmail']] : null;

                    $teacher_is_mentor = $row[$column_index['IsMentor']];


                    if (strlen($external_sub_code) == 1) {
                        $external_sub_code = "0".$external_sub_code;
                    }

                    $school_location_id = $this->getSchoolLocationId($external_sub_code, $external_main_code);
                    if ($school_location_id == null) {
                        $this->importLog('Cannot find school location by brin/location code '.$external_main_code.' '.$external_sub_code);
                        $this->errorMessages[] = 'De Brincode/locatiecode '.$external_main_code.' '.$external_sub_code.' in het bestand kon niet gevonden worden in de database. Vraag aan de Test-Correct admin om een schoollocatie aan te maken met de juiste Brincode en locatiecode.';
                        continue;
                        //throw new \Exception('De Brincode/locatiecode ' . $external_main_code . ' ' . $external_sub_code . ' in het bestand kon niet gevonden worden in de database. Vraag aan de Test-Correct admin om een schoollocatie aan te maken met de juiste Brincode en locatiecode.');
                    }


                    $student_email = $this->generateEmailAddress($student_external_code, Uuid::uuid4()->toString());

////                    $student_email = 'rtti_' . $student_external_code . '_' . $external_main_code . '_' . $external_sub_code . '@' . $this->email_domain;
                    $teacher_email = $this->generateEmailAddress('rtti_'.$teacher_external_code.'_'.$external_main_code.'_'.$external_sub_code,
                        Uuid::uuid4()->toString());


                    if (!in_array($study_year, range((now()->year - 10), (now()->year + 10)))) {
                        $this->errorMessages[] = 'Invalid study year '.$study_year;
                        //throw new \Exception('Invalid study year ' . $study_year);
                    }

                    // collect years
                    $yearCheck[$study_year] = 1;

                    if (count($yearCheck) > 1) {
                        $this->errorMessages[] = 'Meerdere lesjaren in RTTI bestand '.implode(',',
                                array_keys($yearCheck));
                    }

                    $school_year_id = $this->getSchoolYearId($school_location_id, $study_year);
                    if (!$school_year_id) {
                        $this->importLog('Cannot find school year id for study year '.$study_year);
                        $this->errorMessages[] = 'Het schooljaar '.$study_year.' in het bestand kon niet gevonden '
                            .'worden in de database voor de schoollocatie met Brincode '
                            .$external_main_code.' en locatiecode '.$external_sub_code.'. '
                            .'Neem contact op met de schoolbeheerder om het schooljaar te laten aanmaken.';
//                        throw new \Exception('Het schooljaar ' . $study_year . ' in het bestand kon niet gevonden '
//                        . 'worden in de database voor de schoollocatie met Brincode '
//                        . $external_main_code . ' en locatiecode ' . $external_sub_code . '. '
//                        . 'Neem contact op met de schoolbeheerder om het schooljaar te laten aanmaken.');
                    }

                    $education_level_id = $this->getEducationLevelId($study_direction);

                    if ($education_level_id === null) {
                        $this->errorMessages[] = 'Onbekende studierichting '.$study_direction;
                        throw new \Exception('Onbekende studierichting '.$study_direction);
                    }

                    // check if education level is allowed
                    $education_level_max_years = $this->getEducationLevelMaxYearsForEducationLevelId($education_level_id);
                    if ($study_year_layer > $education_level_max_years) {
                        $this->errorMessages[] = 'De les jaar laag '.$study_year_layer.' is niet correct. De Studierichting (niveau) '.$study_direction.' kan maximaal '.$education_level_max_years.' jaren zijn. Pas dit in het bestand aan of neem contact op met ICT';
                        //throw new \Exception('De les jaar laag ' . $study_year_layer . ' is niet correct. De Studierichting (niveau) ' . $study_direction . ' kan maximaal ' . $education_level_max_years . ' jaren zijn. Pas dit in het bestand aan of neem contact op met ICT');
                    }

                    $school_class_id = $this->getSchoolClassId(
                        $class_name,
                        $school_location_id,
                        $study_year,
                        $study_year_layer,
                        $education_level_id
                    );
                    $teacher_id = $this->getUserIdForTeacherInLocation($teacher_external_code, $school_location_id,
                        $teacher_eckid);
                    $student_id = $this->getUserIdForLocation($student_external_code, $school_location_id,
                        $student_eckid);
                    $subject_id = $this->getSubjectId($subject_abbreviation, $school_location_id, $teacher_eckid);

                    $this->importLog("subject id is ".$subject_id." for abbreviation ".$subject_abbreviation." and location ".$school_location_id);

                    if (isset($allClasses[$school_location_id]['school_class_id'])) {
                        if (!in_array($school_class_id, $allClasses[$school_location_id]['school_class_id'])) {
                            $allClasses[$school_location_id]['school_class_id'][] = $school_class_id;
                        }
                    } else {
                        $allClasses[$school_location_id]['school_class_id'][] = $school_class_id;
                    }

                    $allClasses[$school_location_id]['school_year_id'] = $school_year_id;

                    if (!$subject_id) {

                        $this->importLog('Cannot find subject '.$subject_abbreviation);
                        $this->errorMessages[] = 'Het vak met de afkorting '.$subject_abbreviation.' in het bestand kon niet gevonden '
                            .'worden in de database voor de schoollocatie met Brincode/locatiecode: '.$external_main_code.' '
                            .$external_sub_code.'. Neem contact op met de schoolbeheerder om het vak te laten aanmaken';
//                        throw new \Exception('Het vak met de afkorting ' . $subject_abbreviation . ' in het bestand kon niet gevonden '
//                        . 'worden in de database voor de schoollocatie met Brincode/locatiecode: ' . $external_main_code . ' '
//                        . $external_sub_code . '. Neem contact op met de schoolbeheerder om het vak te laten aanmaken');
                    }

                    $this->importLog('school location '.$school_location_id.' sub '.$external_sub_code.' main '.$external_main_code);
                    $this->importLog('Start inserting record '.$index.' for location '.$school_location_id.'  BRIN '.$external_main_code);

                    // class doesnt exist, create it else use it
                    if ($school_class_id == null) {
                        $this->importLog('Restoring school class');

                        $school_class_id = $this->createOrRestoreSchoolClass([
                            'school_location_id'   => $school_location_id,
                            'education_level_id'   => $education_level_id,
                            'school_year_id'       => $school_year_id,
                            'name'                 => $class_name,
                            'education_level_year' => $study_year_layer,

                            'is_main_school_class'            => $teacher_is_mentor,
                            'do_not_overwrite_from_interface' => 0,
                            'visible'                         => $this->make_school_classes_visible,
                        ]);

                        $this->create_tally['classes']++;

                        $this->importLog('Class '.$class_name.' with id '.$school_class_id.'  created ');
                    } else {
                        $schoolClass = SchoolClass::withoutGlobalScope('visibleOnly')->find($school_class_id);
                        if ($schoolClass->is_main_school_class === 0 && $teacher_is_mentor == 1) {
                            $schoolClass->is_main_school_class = 1;
                            $schoolClass->save();
                            $this->importLog('Class '.$class_name.' with id '.$school_class_id.' exists and was updated to is_main_school_class=1');
                        } else {
                            $this->importLog('Class '.$class_name.' with id '.$school_class_id.' exists');
                        }
                    }


                    // rule 1 if class exists in import file and TC then set do_not_overwrite to 0 at all times
                    SchoolClass::withoutGlobalScope('visibleOnly')->where('id',
                        $school_class_id)->update(['do_not_overwrite_from_interface' => 0]);

                    if (!isset($studentsPerClass[$school_class_id])) {
                        $studentsPerClass[$school_class_id] = [];
                    }

//                    // student is known
//                    if ($student_id != null) {
//                        if ($student = User::find($student_id)) {
//                            $student->name_first = $student_name_first;
//                            $student->name_suffix = $student_name_suffix;
//                            $student->name = $student_name_last;
//                            $student->eckid = $student_eckid;
//
//                            if ($student->isDirty()) {
//                                $this->update_tally['students']++;
//                            }
//                            $student->save();
//                        }
//
//                        // student not in class (always the case with a new class)
//                        if (!$this->getStudentIdForClass($student_id, $school_class_id)) {
//
//                            $this->createOrRestoreStudent([
//                                'user_id'  => $student_id,
//                                'class_id' => $school_class_id
//                            ]);
//
//                            $this->importLog('Added student with id '.$student_id.' to class '.$school_class_id);
//                        } else {
//
//                            $this->importLog('Student with id '.$student_id.' exists in class '.$school_class_id);
//                        }
//
//
//                        $this->raiseDoubleEntryError($student_eckid, $student_external_code, $school_location_id);
//                    } else {
//
//                        $this->importLog("Create student with external code ".$student_external_code);
//
//                        $user_data = [
//                            'external_id'        => $student_external_code,
//                            'name_first'         => $student_name_first,
//                            'name_suffix'        => $student_name_suffix,
//                            'name'               => $student_name_last,
//                            'eckid'              => $student_eckid,
//                            'username'           => $student_email, // moet email zijn?
//                            'school_location_id' => $school_location_id,
//                            'user_roles'         => [3],
//
//                        ];
//
//                        $user_id = $this->createOrRestoreUser($user_data, 'student');
//
//                        $this->importLog('User created for student with id '.$user_id.' and external code '.$student_external_code);
//
//                        $this->createOrRestoreStudent([
//                            'user_id'  => $user_id,
//                            'class_id' => $school_class_id
//                        ]);
//
//                        $this->create_tally['students']++;
//
//                        $student_id = $user_id;
//                    }


                    $user_data = [
                        'external_id'        => $student_external_code,
                        'name_first'         => $student_name_first,
                        'name_suffix'        => $student_name_suffix,
                        'name'               => $student_name_last,
                        'eckid'              => $student_eckid,
                        'username'           => $student_email, // moet email zijn?
                        'school_location_id' => $school_location_id,
                        'user_roles'         => [3],
                        'send_welcome_email' => 1,

                    ];

                    $student_id = $this->createOrRestoreUser($user_data, 'student');

                    if (!$this->getStudentIdForClass($student_id, $school_class_id)) {

                        $this->createOrRestoreStudent([
                            'user_id'  => $student_id,
                            'class_id' => $school_class_id
                        ]);

                        $this->importLog('Added student with id '.$student_id.' to class '.$school_class_id);
                    } else {

                        $this->importLog('Student with id '.$student_id.' exists in class '.$school_class_id);
                    }


                    $studentsPerClass[$school_class_id][] = $student_id;

                    if ($teacher_id != null) {

                        $user = null;
                        if ($teacher_external_code) {
                            $user_collection = User::join('school_location_user', 'users.id', '=',
                                'school_location_user.user_id')
                                ->where('school_location_user.school_location_id', $school_location_id)
                                ->where('school_location_user.external_id', $teacher_external_code)
                                ->get();

                            if ($user_collection->count() > 1) {

                                throw new \Exception('Dubbele externe id voor leraar met externe code '.$teacher_external_code);
                            }
                            $user = $user_collection->first();
                        }
                        if ($user == null && $teacher_eckid) {
                            $user = User::findByEckIdAndSchoolLocationIdForTeacher($teacher_eckid,
                                $school_location_id)->first();
                        }

                        if ($user === null) {
                            throw new \Exception('User niet gevonden blijkbaar gaat er hier iets mis');
                        }


                        $user->name_first = $teacher_name_first;
                        $user->name_suffix = $teacher_name_suffix;
                        $user->name = $teacher_name_last;
                        $user->eckid = $teacher_eckid;

                        if ($user->isDirty()) {
                            $this->update_tally['teachers']++;
                        }
                        $user->save();

                        $teacher_table_id = $this->getTeachersForClassSubject($teacher_id, $school_class_id,
                            $subject_id);

                        if ($teacher_table_id == null && $subject_id !== null) {

                            $teacher = $this->createOrRestoreTeacher([
                                'user_id'    => $user->id,
                                'class_id'   => $school_class_id,
                                'subject_id' => $subject_id
                            ], $school_location_id, $class_name);

                            $this->create_tally['teachers']++;


                            $this->importLog('Assigned teacher with id '.$user->id.' to class id '.$school_class_id.' and subject id '.$subject_id);
                        } else {

                            $this->importLog("Teacher already assigned with id ".$school_class_id." and subject id ".$subject_id);
                        }
                    } else {
                        if ($this->can_create_users_for_teacher) {
                            $user_data = [
                                'external_id'        => $teacher_external_code,
                                'eckid'              => $teacher_eckid,
                                'name_first'         => $teacher_name_first,
                                'name_suffix'        => $teacher_name_suffix,
                                'name'               => $teacher_name_last,
                                'username'           => $teacher_email,
                                'school_location_id' => $school_location_id,
                                'user_roles'         => [1],
                                'send_welcome_email' => 1,
                            ];

                            $user_id = $this->createOrRestoreUser($user_data, 'teacher');

                            $this->importLog('Teacher user created with id '.$user_id);
                            if ($subject_id !== null) {
                                $teacher = $this->createOrRestoreTeacher([
                                    'user_id'    => $user_id,
                                    'class_id'   => $school_class_id,
                                    'subject_id' => $subject_id
                                ], $school_location_id, $class_name);

                                $this->create_tally['teachers']++;

                                $teacher_id = $teacher->user_id;
                            }
                        } else {
                            $missing_user = [
                                $teacher_name_first,
                                $teacher_name_suffix,
                                $teacher_name_last
                            ];
                            if (!array_key_exists('missing_teachers', $this->errorMessages)) {
                                $this->errorMessages['missing_teachers'] = [];
                            }
                            $this->errorMessages['missing_teachers'][] = $missing_user;
//                        throw new \Exception('
//                        Voor de onderstaande docenten bestaat nog geen account. Maak die eerst aan voordat u de RTTI importer draait:
//                        '. $missing_user);


                            $this->importLog("User missing Teacher not created ".implode(';', $missing_user));
                            continue;
                        }
                    }

                    $this->addToTeachersPerClass($teacher_id, $subject_id, $school_class_id);

                    // collect teacher class combinations
                    $classTeacherCheck[$teacher_id] = $school_class_id;

                    foreach ($this->teachersPerClass as $teacher_id => $class_subjects) {
                        foreach ($class_subjects as $class_subject_tuple) {
                            $this->importLog('Assigned teacher '.$teacher_id.' where class '.$class_subject_tuple['class_id'].' and subject '.$class_subject_tuple['subject_id']);
                        }
                    }

                    // set mentor state
                    if ($this->skip_mentor_creation === false && $teacher_is_mentor && $teacher_id) {

                        $classMentorCheck[$school_class_id][] = $teacher_id;

                        $this->importLog('Setting teacher as mentor for '.$school_class_id.' '.$teacher_id);

                        $this->setTeacherAsMentor($teacher_id, $school_class_id);

                    }

                    $this->importLog('-------- index '.$index.' data lines '.$this->csv_data_lines);

                    // only execute after the last line is processed
                    if ($index == $this->csv_data_lines) {

                        $this->importLog('---- Deleting unmentioned teachers and students');

                        /* check if due to a type multiple teacher id are associated with one class
                          if(count(array_flip($classTeacherCheck)) != count(array_values($classTeacherCheck)))
                          throw new \Exception('Multiple teachers assigned to the same class');
                         */

                        $this->removeTeacherAsMentor($classMentorCheck);

                        $class_ids = array_keys($studentsPerClass);

                        foreach ($studentsPerClass as $class_id => $students) {

                            $this->importLog('Students in class '.$class_id.' '.implode(',', $students));

                            $this->delete_tally['students'] += Student::whereNotIn('user_id', $students)
                                ->where('class_id', $class_id)
                                ->count();

                            Student::whereNotIn('user_id', $students)
                                ->where('class_id', $class_id)
                                ->delete();

                            $this->importLog("Deleted students from class ".$class_id);
                        }

                        $class_subjects_combined = [];
                        // remove teachers from classes where they are not assigned
                        // according to the upload

                        $teachers_per_class_subject = [];

                        foreach ($this->teachersPerClass as $teacher_id => $class_subjects) {
                            foreach ($class_subjects as $class_subject_tuple) {
                                $class_subjects_combined[$class_subject_tuple['class_id']][] = $class_subject_tuple['subject_id'];
                                $teachers_per_class_subject[$class_subject_tuple['class_id']][$class_subject_tuple['subject_id']][] = $teacher_id;
                            }
                        }

                        foreach ($teachers_per_class_subject as $class => $subject_teachers) {
                            foreach ($subject_teachers as $subject => $teachers) {

                                $this->delete_tally['teachers'] += Teacher::whereNotIn('user_id', $teachers)
                                    ->where('class_id', $class)
                                    ->where('subject_id', $subject)
                                    ->count();

                                // @TODO er kunnen meerdere docenten hetzelfde vak geven aan dezelfde klas, dus er moet iets anders bedacht worden
                                // voor het verwijderen van docenten die ECHT NIET MEER gekoppeld zijn aan deze klas met dit vak
                                Teacher::whereNotIn('user_id', $teachers)
                                    ->where('class_id', $class)
                                    ->where('subject_id', $subject)
                                    ->delete();

                                $this->importLog('deleting other teachers from class '.$class_subject_tuple['class_id'].' and subject '.$class_subject_tuple['subject_id']);
                            }
                        }

                        foreach ($class_subjects_combined as $class_id => $subject_ids) {

                            // delete teachers where the subject is not in the import for the class
                            $deleted_teachers = Teacher::leftjoin('school_classes', 'school_classes.id', '=',
                                'teachers.class_id')
                                ->where(function ($q) {
                                    $q->where('school_classes.do_not_overwrite_from_interface', 0)
                                        ->orWhereNull('school_classes.do_not_overwrite_from_interface');
                                })
                                ->where('teachers.class_id', $class_id)
                                ->whereNotIn('teachers.subject_id', $subject_ids)
                                ->delete();
                        }

                        $this->importLog('teachers deleted due to subject not in import '.$deleted_teachers);

                        $this->delete_tally['teachers'] += $deleted_teachers;

                        foreach ($allClasses as $school_location_id => $data) {

                            $ids = SchoolClass::withoutGlobalScope('visibleOnly')
                                ->select('id')
                                ->where('school_location_id', $school_location_id)
                                ->where(function ($query) {
                                    $query->where('do_not_overwrite_from_interface', 0)
                                        ->orWhereNull('do_not_overwrite_from_interface');
                                })
                                ->where('school_year_id', $data['school_year_id'])
                                ->whereNotIn('id', array_unique($class_ids))
                                ->get()
                                ->toArray();

                            foreach ($ids as $id) {

                                $this->delete_tally['classes']++;

                                // remove student from class
                                Student::where('class_id', $id['id'])->delete();
                                Teacher::where('class_id', $id['id'])->delete();
                                Mentor::where('school_class_id', $id['id'])->delete();
                                SchoolClass::withoutGlobalScope('visibleOnly')->where('id', $id['id'])->delete();
                            }
                        }

                        GlobalStateHelper::getInstance()->setQueueAllowed(true);
                        $schoolLocation = SchoolLocation::find($school_location_id);
                        StatisticsRepository::runForSchoolLocationAndRole($schoolLocation, 'student');
                        StatisticsRepository::runForSchoolLocationAndRole($schoolLocation, 'teacher');
                    }
                }
            }
            if (count($this->errorMessages) > 0) {
                throw new \Exception('collected errors');
            }
        } catch (\Throwable $e) {
            $failure_messages = [sprintf('Error detected: %s', $e->getTraceAsString())];
            $failure_messages[] = $e->getMessage();
            $failure_messages[] = $e->getFile();
            $failure_messages[] = $e->getLine();

            $failure_messages[] = 'Initiating rollback;';
            DB::rollback();
            $failure_messages[] = 'Rollback completed;';
            $this->importLog("Transaction failed with message ".$e->getMessage());
            logger('----- uwlr import log ERROR ------');
            logger($failure_messages);
            if ($e->getMessage() == 'collected errors') {
                return ['errors' => $this->makeErrorsUnique()];
            } else {
                $result = UwlrSoapResult::find($this->uwlr_soap_result_id);
                $result->error_messages = $e->getMessage();
                $result->failure_messages = implode('<BR>', $failure_messages);
                $result->status = 'ERROR';
                $result->save();
            }
            // MF merge the errorMessages of helper on the return to fix schoolyear error;
            return [
                'errors' => array_merge(
                    [
                        sprintf('[line:%d, file: %s] %s', $e->getLine(), $e->getFile(), $e->getMessage())
                    ], $this->errorMessages
                )
            ];
        }

        if (!App::runningUnitTests()) {
            DB::commit();
        }
        $this->importLog(sprintf('DONE [%s seconds] [%d cacheHits] ', time() - $this->startTime, $this->cacheHit));

        $this->importLog('import done');

        return [
            'data' => sprintf(
                'Versie 0.1. De import was succesvol. %s %s %s',
                $this->createTally(),
                $this->updateTally(),
                $this->deleteTally()
            )
        ];
    }

    // $teacher_id is actually user_id;
    protected function addToTeachersPerClass($teacher_id, $subject_id, $school_class_id)
    {
        if (isset($this->teachersPerClass[$teacher_id])) {
            if (!in_array(['subject_id' => $subject_id, 'class_id' => $school_class_id],
                $this->teachersPerClass[$teacher_id])) {
                $this->teachersPerClass[$teacher_id][] = [
                    'subject_id' => $subject_id, 'class_id' => $school_class_id
                ];
            }
        } else {
            $this->teachersPerClass[$teacher_id][] = [
                'subject_id' => $subject_id, 'class_id' => $school_class_id
            ];
        }
    }

    private function createTally()
    {
        $return = '';

        if ($this->create_tally['students'] === 1) {
            $return .= 'Er is 1 leerling aangemaakt, ';
        } else {
            $return .= sprintf('Er zijn %d leerlingen aangemaakt, ', $this->create_tally['students']);
        }

        if ($this->create_tally['teachers'] === 1) {
            $return .= '1 docent en ';
        } else {
            $return .= sprintf('%d docenten en ', $this->create_tally['teachers']);
        }

        if ($this->create_tally['classes'] === 1) {
            $return .= '1 klas. ';
        } else {
            $return .= sprintf('%s klassen. ', $this->create_tally['classes']);
        }

        return $return;
    }


    private function updateTally()
    {
        $return = '';

        if ($this->update_tally['students'] === 1) {
            $return .= 'Er is 1 leerling geupdate, ';
        } else {
            $return .= sprintf('Er zijn %d leerlingen geupdate, ', $this->update_tally['students']);
        }

        if ($this->update_tally['teachers'] === 1) {
            $return .= '1 docent en ';
        } else {
            $return .= sprintf('%d docenten en ', $this->update_tally['teachers']);
        }

        if ($this->update_tally['classes'] === 1) {
            $return .= '1 klas. ';
        } else {
            $return .= sprintf('%s klassen. ', $this->update_tally['classes']);
        }

        return $return;
    }


    private function deleteTally()
    {
        $return = '';

        if ($this->delete_tally['students'] === 1) {
            $return .= 'Er is 1 leerling verwijderd, ';
        } else {
            $return .= sprintf('Er zijn %d leerlingen verwijderd, ', $this->delete_tally['students']);
        }

        if ($this->delete_tally['teachers'] === 1) {
            $return .= '1 docent en ';
        } else {
            $return .= sprintf('%d docenten en ', $this->delete_tally['teachers']);
        }

        if ($this->delete_tally['classes'] === 1) {
            $return .= '1 klas.';
        } else {
            $return .= sprintf('%s klassen.', $this->delete_tally['classes']);
        }

        return $return;
    }

    /**
     *
     * @return string
     */
    public function validate()
    {

        $errors = [];
        $lines = 0;


        foreach ($this->csv_data as $index => $row) {

            if ($index == 0) {

                $columns = $row;
            } else {

                $lines++;

                foreach ($columns as $fieldindex => $field) {

                    switch ($field):
                        case "Schoolnaam":
                            // no rules
                            if ($row[$fieldindex] == "") {
                                $errors[] = $field." missing ";
                            }
                            break;
                        case "Brincode":
                            // alphanumeric max 4 chars
                            if ($row[$fieldindex] == "" || !ctype_alnum($row[$fieldindex]) || strlen($row[$fieldindex]) > 4) {
                                $errors[] = $field." error ";
                            }
                            break;
                        case "Locatiecode":
                            if ($row[$fieldindex] == "") {
                                $errors[] = $field." missing ".$row[$fieldindex];
                            }
                            if (\strlen($row[$fieldindex]) > 2 || !ctype_digit($row[$fieldindex])) {
                                $errors[] = $field." niet numeriek of te lang ".$row[$fieldindex];
                            }
                            break;
                        case "Studierichting":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || \strlen($row[$fieldindex]) > 45) {
                                $errors[] = $field." incorrect (".$row[$fieldindex].")";
                            }
                            break;
                        case "lesJaarlaag":
                            if (!ctype_digit($row[$fieldindex])) {
                                $errors[] = $field." incorrect (".$row[$fieldindex].")";
                            }
                            break;
                        case "Schooljaar":
                            if (!ctype_digit(substr($row[$fieldindex], 0, 4))) {
                                $errors[] = $field." incorrect (".$row[$fieldindex].").";
                            }
                            break;
                        case "leeStamNummer":
                            if ($row[$fieldindex] == "" || !ctype_alnum($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een stamnummer (".$row[$fieldindex].") van een leerling kan maximaal 45 tekens lang zijn en mag niet leeg zijn.";
                            }
                            break;
                        case "leeAchternaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een achternaam (".$row[$fieldindex].") van een leerling kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "leeTussenvoegsels":
                            if ($row[$fieldindex] != '' && (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45)) {
                                $errors[] = "Een tussenvoegsel (".$row[$fieldindex].")  van een leerling kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "leeVoornaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een voornaam  (".$row[$fieldindex].")  van een leerling kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "lesNaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een lesnaam  (".$row[$fieldindex].")  kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "vakNaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 10) {
                                $errors[] = "Een vaknaam (".$row[$fieldindex].") is een afkorting en kan maximaal 10 tekens lang zijn.";
                            }
                            break;
                        case "docStamNummer":
                            if (!ctype_alnum($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een stamNummer (".$row[$fieldindex].") kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "docAchternaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een achternaam  (".$row[$fieldindex].")  van een docent kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "docTussenvoegsels":
                            if ($row[$fieldindex] != '' && (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45)) {
                                $errors[] = "Een tussenvoegsel  (".$row[$fieldindex].")  van een docent kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "docVoornaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een voornaam (".$row[$fieldindex].")  van een docent kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "IsMentor":
                            if ($row[$fieldindex] != 1 && $row[$fieldindex] != 0) {
                                $errors[] = " Het IsMentor veld moet een 0 of een 1 zijn. 1 betekent dat de docent een mentor is en 0 dat de docent geen mentor is. De waarde is nu (".$row[$fieldindex].")";
                            }
                            break;
                        default:
                            break;
                    endswitch;
                }
            }
        }

        $validation_report['errors'] = $errors;
        $this->csv_data_lines = $lines;

        return $validation_report;
    }

    /**
     *
     * @param  type  $email_domain
     * @return boolean
     * @throws \Exception
     */
    public function validateEmailDomain($email_domain)
    {

        // email must be valid or empty
        if ($email_domain != "") {
            $email_domain_parts = explode('.', $email_domain);
            if (count($email_domain_parts) != 2) {
                throw new \Exception('Email domein ken leeg zijn of een valided domein bevatten, bv. test.nl');
            }
            foreach (explode('.', $email_domain) as $email_domain_part) {
                if (!\ctype_alnum($email_domain_part)) {
                    throw new \Exception('Email domein ken leeg zijn of een valided domein bevatten, bv. test.nl');
                }
            }
        }

        return false;
    }

    /**
     *
     * @param  type  $external_sub_code
     * @param  type  $external_main_code
     * @return type
     */
    public function getSchoolLocationId($external_sub_code, $external_main_code)
    {
        return SchoolLocation::select('id')
            ->where('external_sub_code', $external_sub_code)
            ->where('external_main_code', $external_main_code)
            ->value('id');
    }

    /**
     *
     * @param  type  $class_name
     * @param  type  $school_location_id
     * @param  type  $year
     * @param  type  $education_level_year
     * @param  type  $education_level_id
     * @return type
     */
    public function getSchoolClassId(
        $class_name,
        $school_location_id,
        $year,
        $education_level_year,
        $education_level_id
    ) {

        $school_year_id = $this->cache(
            function () use ($school_location_id, $year) {
                return SchoolLocationSchoolYear::select('school_year_id')
                    ->leftjoin('school_years', 'school_years.id', '=', 'school_location_school_years.school_year_id')
                    ->whereNull('school_location_school_years.deleted_at')
                    ->where('school_location_school_years.school_location_id', '=', $school_location_id)
                    ->where('school_years.year', $year)
                    ->value('school_year_id');
            }, 'school_year_id', [$school_location_id, $year]
        );

        if ($school_year_id != null) {
            return $this->cache(
                function () use (
                    $class_name,
                    $school_location_id,
                    $school_year_id,
                    $education_level_year,
                    $education_level_id
                ) {
                    return SchoolClass::withoutGlobalScope('visibleOnly')
                        ->where('name', $class_name)
                        ->where('school_location_id', $school_location_id)
                        ->where('school_year_id', $school_year_id)
                        ->where('education_level_year', $education_level_year)
                        ->where('education_level_id', $education_level_id)
                        ->whereNull('school_classes.deleted_at')
                        ->value('id');
                },
                'schoolClass',
                [$class_name, $school_location_id, $school_year_id, $education_level_year, $education_level_id]
            );
        } else {
            return null;
        }
    }

    /**
     *
     * @param  type  $user_data
     * @return type
     */
    public function createOrRestoreUser($user_data, $forRole = 'student')
    {
        $user = null;

        if (!empty($user_data['eckid'])) {
            if (strtolower($forRole) === 'teacher') {
                $user = User::withTrashed()->findByEckidAndSchoolLocationIdForTeacher($user_data['eckid'],
                    $user_data['school_location_id'])->first();
            } else {
                $user = User::withTrashed()->findByEckidAndSchoolLocationIdForUser($user_data['eckid'],
                    $user_data['school_location_id'])->first();
            }
        }

        if ($user == null && $user_data['external_id']) {
            $user = User::withTrashed()
                ->where('external_id', $user_data['external_id'])
                ->where('school_location_id', $user_data['school_location_id'])
                ->first();
        }


        if ($user != null) {
            $restored = false;
            if ($user->trashed()){
                if($forRole === 'student') {
                    $this->create_tally['students']++;
                } else if($forRole === 'teacher') {
                    $schoolLocation = SchoolLocation::find($user_data['school_location_id']);
                    $user->addSchoolLocation($schoolLocation);
                    $this->create_tally['teachers']++;

                }
                $user->restore();
                $restored = true;
            }

            foreach (['eckid', 'name_first', 'name','name_suffix'] as $key) {
                $user->$key = $user_data[$key];
            }
            if (!$restored && $user->isDirty() && $forRole === 'student') {
                $this->update_tally['students']++;
            }
            $user->save();

            if ($forRole === 'student') {
                $this->raiseDoubleEntryError($user->eckid, $user->external_id, $user->school_location_id);
            }

        } else {

            $userFactory = new Factory(new User());
            $user = $userFactory->generate($user_data);
            if (array_key_exists('eckid', $user_data)) {
                if ($user_data['eckid']) {
                    $user->eckid = $user_data['eckid'];

                }
            }
            if ($forRole == 'teacher') {
                $user->account_verified = now();
            }

            $user->save();
            if ($this->should_use_import_email_pattern) {
                $pattern = ($forRole === 'teacher') ? User::TEACHER_IMPORT_EMAIL_PATTERN : User::STUDENT_IMPORT_EMAIL_PATTERN;
                $user->username = sprintf($pattern, $user->id);
            }

            if ($this->should_use_import_password_pattern) {
                $pattern = ($forRole === 'teacher') ? User::TEACHER_IMPORT_PASSWORD_PATTERN : User::STUDENT_IMPORT_PASSWORD_PATTERN;
                $user->password = sprintf($pattern, $user->id);
            }

            if ($user->isDirty()) {
                $user->save();
            }
            if ($forRole === 'student') {
                $this->create_tally['students']++;
            }
        }

        return $user->id;
    }

    /**
     *
     * @param  type  $teacher_data
     * @return type
     */
    public function createOrRestoreTeacher($teacher_data, $schoolLocationId, $className)
    {
        $class_id = $teacher_data['class_id'];
        $user_id = $teacher_data['user_id'];
        $subject_id = $teacher_data['subject_id'];

        $teacher = $this->cache(
            function () use ($class_id, $user_id, $subject_id) {
                Teacher::withTrashed()
                    ->where('class_id', $class_id)
                    ->where('user_id', $user_id)
                    ->where('subject_id', $subject_id)
                    ->first();
            }, 'createOrRestoreTeacher', [$class_id, $user_id, $subject_id]
        );

        if ($teacher != null) {
            $teacher->restore();

            return $teacher;
        }

        $builder = Teacher::where('class_id', $teacher_data['class_id'])
                    ->where('user_id', $teacher_data['user_id']);
        $teacher = $builder->first();
        if(null === $teacher){
            $teacher = $builder->withTrashed()->first();
        }

        if ($this->can_find_teacher_only_by_class_id && $teacher != null) {
            $teacher->restore();
            $this->addToTeachersPerClass($teacher['user_id'], $teacher->subject_id, $teacher_data['class_id']);

            return $teacher;
        } else {
            if ($this->can_find_school_class_only_by_name && self::isDummySubject($subject_id)) {
                $teacher = null;
                // try to find old teacher records by class name
                $oldSchoolClass = self::getOldSchoolClassByNameOptionalyLeaveCurrentOut($schoolLocationId, $className,
                    $teacher_data['class_id']);
                if ($oldSchoolClass) {
                    Teacher::where('user_id', $teacher_data['user_id'])->where('class_id',
                        $oldSchoolClass->getKey())->get()->each(function (Teacher $t) use (&$teacher, $teacher_data) {
                        $teacher_data['subject_id'] = $t->subject_id;
                        $teacher = Teacher::Create($teacher_data);
                        $this->addToTeachersPerClass($teacher_data['user_id'], $t->subject_id,
                            $teacher_data['class_id']);
                    });
                    if ($teacher) {
                        return $teacher;
                    }
                }
            }
            return Teacher::Create($teacher_data);
        }
    }

    /**
     *
     * @param  type  $student_data
     * @return type
     */
    public function createOrRestoreStudent($student_data)
    {
        $student = Student::withTrashed()
            ->where('class_id', $student_data['class_id'])
            ->where('user_id', $student_data['user_id'])
            ->first();

        if ($student) {
            return $student->restore();
        }
        return Student::Create($student_data);
    }

    /**
     *
     * @param  type  $data
     * @return type
     */
    public function createOrRestoreSchoolClass($data)
    {
        $schoolClass = SchoolClass::withoutGlobalScope('visibleOnly')
            ->withTrashed()
            ->where('school_location_id', $data['school_location_id'])
            ->where('education_level_id', $data['education_level_id'])
            ->where('school_year_id', $data['school_year_id'])
            ->where('name', $data['name'])
            ->where('education_level_year', $data['education_level_year'])
            ->first();

        // uwlr
        if (null !== $this->uwlr_soap_result_id) {
            $data['created_by'] = 'lvs';
        }
        if ($schoolClass === null && $this->can_find_school_class_only_by_name) {
            return $this->getOrCreateSchoolClassIfAllowedByName($data);
        }

        if ($schoolClass) {
            if ($schoolClass->trashed()) {
                $schoolClass->restore();
                $schoolClass->created_by = 'lvs';
                $schoolClass->save();
            }

            return $schoolClass->getKey();
        }

        return SchoolClass::create($data)->getKey();
    }

    protected function getOrCreateSchoolClassIfAllowedByName($data)
    {
        $schoolClass = SchoolClass::withoutGlobalScope('visibleOnly')
            ->withTrashed()
            ->where('school_location_id', $data['school_location_id'])
            ->where('school_year_id', $data['school_year_id'])
            ->where('name', $data['name'])
            ->first();


        if ($schoolClass === null) {
            $oldSchoolClass = self::getOldSchoolClassByNameOptionalyLeaveCurrentOut($data['school_location_id'],
                $data['name']);

            if ($oldSchoolClass) {
                $data['education_level_id'] = $oldSchoolClass->education_level_id;
                $data['education_level_year'] = $oldSchoolClass->education_level_year;
            }
            return SchoolClass::create($data)->getKey();
        }
        $schoolClass->name = $data['name']; // set the name the capitalized way we get it from the data array
        $schoolClass->is_main_school_class = $data['is_main_school_class']; // the import is leading in telling whether this is a mainSchoolClass even if set differently earlier
        $schoolClass->created_by = $data['created_by'];
        $schoolClass->save();
        $schoolClass->restore();
        return $schoolClass->getKey();
    }

    public static function getOldSchoolClassByNameOptionalyLeaveCurrentOut($schooLlocationId, $name, $currentId = null)
    {
        $builder = SchoolClass::withoutGlobalScope('visibleOnly')
//            ->withTrashed()
            ->where('school_location_id', $schooLlocationId)
            ->where('name', $name)
            ->orderBy('created_at', 'desc');
        if ($currentId) {
            $builder->where('id', '<>', $currentId);
        }
        $model = $builder->first();
        if(null !== $model){
            return $model;
        }
        $builder->withTrashed();
        return $builder->first();
    }

    /**
     * @param  type  $abbreviation
     * @param  type  $school_location_id
     * @return type
     */
    public function getSubjectId($abbreviation, $school_location_id)
    {
        $subject = Subject::select('subjects.id as id')
            ->join('sections as SEC', 'SEC.id', '=', 'subjects.section_id')
            ->join('school_location_sections as SLS', 'SLS.section_id', '=', 'SEC.id')
            ->where('subjects.abbreviation', $abbreviation)
            ->where('SLS.school_location_id', $school_location_id)
            ->whereNull('SEC.deleted_at')
            ->whereNull('SLS.deleted_at')
            ->whereNull('subjects.deleted_at')
            ->first();

        if (is_object($subject)) {
            return $subject->getKey();
        }

        if ($this->can_use_dummy_subject) {
            $magisterSection = SchoolLocation::find($school_location_id)->schoolLocationSections->first(function (
                $school_location_section
            ) {

                return optional($school_location_section->section)->name === self::DUMMY_SECTION_NAME;
            });
            if (!$magisterSection) {
                $this->errorMessages[] = sprintf('Als u gebruik maakt van de uwlr import dient u een sectie in de school te hebben met de naam [%s]',
                    self::DUMMY_SECTION_NAME);
            }

            $subject = Subject::withTrashed()->firstOrCreate([
                'section_id'      => $magisterSection->section->getKey(),
                'base_subject_id' => BaseSubject::where('name', DemoHelper::SUBJECTNAME)->first()->getKey(),
                'abbreviation'    => 'IMP',
                'name'            => self::DUMMY_SECTION_NAME,
            ]);

            if ($subject && !$subject->trashed()) {
                $subject->delete();
            }

            return $subject->getKey();
        }

        return null;
    }

    public static function isDummySubject($subjectId)
    {
        $subject = Subject::withTrashed()->find($subjectId);
        return optional($subject)->abbreviation === 'IMP';
    }

    /**
     *
     * @param  type  $name
     * @return type
     */
    public function getEducationLevelId($name)
    {
        if ($name === 'uwlr_education_level') {
            return 0;
        }
        return EducationLevel::where('name', $this->translateEducationLevelName($name))->value('id');
    }

    protected function getEducationLevelMaxYearsForEducationLevelId($education_level_id)
    {
        return $this->cache(
            function () use ($education_level_id) {
                return Educationlevel::withTrashed()->select('max_years')
                    ->where('id', $education_level_id)
                    ->value('max_years');
            }, 'maxYearsForEducationLevelId', [$education_level_id]
        );
    }

    /**
     *
     * @param  type  $name
     * @return type string
     */
    public function translateEducationLevelName($name)
    {
        return array_key_exists($name, $this->educationLevelArray) ? $this->educationLevelArray[$name] : $name;
    }

    /**
     *
     * @param  type  $external_id
     * @param  type  $school_location_id
     * @return type int
     */
    public function getUserIdForLocation($external_id, $school_location_id, $eckId = null)
    {
        if ($eckId) {
            if ($user_id = User::findByEckidAndSchoolLocationIdForUser($eckId, $school_location_id)->value('id')) {
                return $user_id;
            }
        }

        if ($external_id) {
            return User::where('external_id', $external_id)
                ->where('school_location_id', $school_location_id)
                ->value('id');
        }

        return null;
    }

    /**
     *
     * @param  type  $external_id
     * @param  type  $school_location_id
     * @return type int
     */
    public function getUserIdForTeacherInLocation($external_id, $school_location_id, $eckId = null)
    {
        if ($eckId) {
            $user = $this->cache(function () use ($eckId, $school_location_id) {
                return User::findByEckidAndSchoolLocationIdForTeacher($eckId, $school_location_id)->value('id');
            }, 'getUserIdForTeacherInLocationEckId', [$eckId]);
            if ($user) {
                return $user;
            }
            return $this->cache(function () use ($eckId, $school_location_id) {
                $users = User::filterByEckId($eckId)->get();
                if ($users->count() < 1) {
                    return null;
                }
                $schoolLocation = SchoolLocation::find($school_location_id);
                $user = $users->first(function (User $user) use ($schoolLocation) {
                    return optional($user->schoolLocation)->belongsToSameSchool($schoolLocation);
                });
                if ($user) {
                    // this teacher belongs to the same school, so therefor it should be added to the school_location_user table
                    $user->addSchoolLocation($schoolLocation);
                    return $user->getKey();
                }
            }, 'getUserIdForTeacherInLocationEckId', [$eckId]);
        }
        if ($external_id) {
            return User::join('school_location_user', 'users.id', '=', 'school_location_user.user_id')
                ->where('school_location_user.school_location_id', $school_location_id)
                ->where('school_location_user.external_id', $external_id)
                ->value('id');
        }
        return null;
    }

    /**
     *
     * @param  type  $user_id
     * @param  type  $class_id
     * @return type int
     */
    public function getStudentIdForClass($user_id, $class_id)
    {
        return Student::where('user_id', $user_id)
            ->where('class_id', $class_id)
            ->value('user_id');
    }

    /**
     *
     * @param  type  $user_id
     * @param  type  $class_id
     * @param  type  $subject_id
     * @return type
     */
    public function getTeachersForClassSubject($user_id, $class_id, $subject_id)
    {
        return Teacher::where('user_id', $user_id)
            ->where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->value('user_id');
    }

    /**
     *
     * @param  type  $teacher_id
     * @param  type  $school_class_id
     * @return type
     */
    public function setTeacherAsMentor($teacher_id, $school_class_id)
    {
        // only mentors in the file are touched
        $mentor = Mentor::withTrashed()
            ->where('user_id', $teacher_id)
            ->where('school_class_id', $school_class_id)->first();

        if ($mentor) {
            $mentor->restore();
        } else {
            $mentor = Mentor::create([
                'user_id'         => $teacher_id,
                'school_class_id' => $school_class_id
            ]);
        }

        return $mentor->value('user_id');
    }

    /**
     *
     * @param  type  $teacher_id
     * @param  type  $school_class_id
     * @return boolean
     */
    public function removeTeacherAsMentor($class_mentor_check)
    {
        if ($this->skip_mentor_creation === false) {
            foreach ($class_mentor_check as $class_id => $mentor_ids) {
                Mentor::whereNotIn('user_id', array_unique($mentor_ids))
                    ->where('school_class_id', $class_id)
                    ->delete();
//            $this->delete_tally['mentors']++;
            }
        }

        return true;
    }

    /**
     *
     * @param  type  $school_location_id
     * @param  type  $year
     * @return type
     */
    public function getSchoolYearId($school_location_id, $year)
    {
        return SchoolLocationSchoolYear::leftJoin('school_years as SY', 'id', '=', 'school_year_id')
            ->where('school_location_id', $school_location_id)
            ->where('SY.year', $year)
            ->whereNull('school_location_school_years.deleted_at')
            ->whereNull('SY.deleted_at')
            ->value('school_year_id');
    }

    /**
     *
     * @param  type  $file
     * @return type
     */
    public function getDataFromFile($file, $separator)
    {
        $rows = [];

        if (!in_array($separator, [';', ','])) {
            throw new \Exception('Scheidingsteken '.$separator.' is incorrect');
        }

        // read csv and put into array
        if (($handle = fopen($file, "r")) !== false) {
            while (($row = fgetcsv($handle, 1000, $separator)) !== false) {
                Request::filter($row);
                $rows[] = $row;
                if (count($row) == 1) {
                    throw new \Exception('De file is leeg of u heeft een incorrect scheidingsteken gekozen');
                }
            }
            fclose($handle);
        }

        $this->csv_data = $rows;

        return $rows;
    }

    private function makeErrorsUnique()
    {
        $returnArray = [];
        foreach ($this->errorMessages as $key => $value) {
            if (is_array($value)) {
                $returnArray[$key] = $value;
                continue;
            }
            if (!in_array($value, $returnArray)) {
                $returnArray[] = $value;
            }
        }
        return $returnArray;
    }

    private function generateEmailAddress($pattern, $uniqueString = null)
    {
        if (!empty($uniqueString)) {
            $pattern = $uniqueString;
        }

        return sprintf('%s@%s', $pattern, $this->email_domain);
    }

    /**
     * @param $student_eckid
     * @param $student_external_code
     * @param  $school_location_id
     */
    public function raiseDoubleEntryError($student_eckid, $student_external_code, $school_location_id): void
    {
        if (!$student_eckid && User::where('external_id', $student_external_code)
                ->where('school_location_id', $school_location_id)->count() > 1) {
            $this->errorMessages[] = 'Dubbele externe id voor dezelfde gebruiker '.$student_external_code;
            //throw new \Exception('Dubbele externe id voor dezelfde gebruiker ' . $student_external_code);
        }
    }

    private function cache(callable $callable, string $entity, array $args)
    {
        $key = implode('-', $args);
        if (array_key_exists($entity, $this->cache) && array_key_exists($key, $this->cache[$entity])) {
            $this->cacheHit++;
//            $this->importLog(sprintf('retrieved from cache %s %s', $entity, $key ));
            return $this->cache[$entity][$key];
        }
        if ($value = $callable()) {
            $this->cache[$entity][$key] = $value;
        }
        return $value;
    }

    private function updateUwlrSoapResultProgress($index)
    {
        UwlrSoapResult::find($this->uwlr_soap_result_id)->updateProgress($index, $this->csv_data_lines);
    }
}
