<?php

namespace tcCore\Http\Helpers;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Illuminate\Support\Facades\Log;
use tcCore\EducationLevel;
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

class RTTIImportHelper {

    /**
     *
     * @var string
     */
    public $email_domain;

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
    private $studydirectionarray = [];

    /**
     *
     * @var array
     */
    private $errorMessages = [];

    /**
     * The console command description.
     *
     * @var array counting created and soft-deleted students, teachers and classes
     */
    public $create_tally = ['students' => 0, 'classes' => 0, 'teachers' => 0];
    public $delete_tally = ['students' => 0, 'classes' => 0, 'teachers' => 0];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($csv_file_path = "", $email_domain = "") {

        $this->log_name = date("mdh_i_s");

        if ($email_domain != "") {
            $this->email_domain = $email_domain;
        } else {
            // find default in user settings?
            $this->email_domain = "rttiimport.nl";
        }

        $this->csv_file_path = $csv_file_path;

        $this->importLog('Loading ' . $this->csv_file_path);

        $this->studydirectionarray = [
            'b' => 'Vmbo bb',
            'b/k' => 'Vmbo kb',
            'k' => 'Vmbo kb',
            'k/t' => 'Mavo / Vmbo tl',
            't' => 'Mavo / Vmbo tl',
            'm/h' => 'Havo',
        ];
    }

    /**
     *
     * @param type $string
     * @return boolean
     */
    public function importLog($string) {

        logger($string);

        return true;
    }

    private function checkAlphaNumericAndSpace($string) {

        return preg_match('/^[a-z0-9&; .\-]+$/i', $string);
    }

    public function process() {

        // Temporary datastore
        $studentsPerClass = [];
        $teachersPerClass = [];
        $classTeacherCheck = [];
        $classMentorCheck=[];
        $yearCheck = [];
        $allClasses = [];
        $this->errorMessages = [];

        $this->importLog('----- ' . $this->csv_data_lines . ' data lines in input file');

        \DB::beginTransaction();
        try {

            foreach ($this->csv_data as $index => $row) {

                if ($index == 0) {

                    $column_index = array_flip($row);
                } else {

                    $this->importLog('Processing line ' . $index);

                    $external_main_code = $row[$column_index['Brincode']];
                    $external_sub_code = $row[$column_index['Locatiecode']];

                    $study_direction = $row[$column_index['Studierichting']];
                    $study_year_layer = $row[$column_index['lesJaarlaag']];
                    $study_year = substr($row[$column_index['Schooljaar']], 0, 4);

                    $student_external_code = $row[$column_index['leeStamNummer']];
                    $student_name_first = $row[$column_index['leeVoornaam']];
                    $student_name_suffix = $row[$column_index['leeTussenvoegsels']];
                    $student_name_last = $row[$column_index['leeAchternaam']];

                    $class_name = $row[$column_index['lesNaam']];
                    $subject_abbreviation = $row[$column_index['vakNaam']];

                    $teacher_external_code = $row[$column_index['docStamNummer']];
                    $teacher_name_first = $row[$column_index['docVoornaam']];
                    $teacher_name_suffix = $row[$column_index['docTussenvoegsels']];
                    $teacher_name_last = $row[$column_index['docAchternaam']];
                    $teacher_is_mentor = $row[$column_index['IsMentor']];

                    $now = Carbon::now();

                    $school_location_id = $this->getSchoolLocationId($external_sub_code, $external_main_code);
                    if ($school_location_id == NULL) {
                        $this->importLog('Cannot find school location by brin/location code ' . $external_main_code . ' ' . $external_sub_code);
                        $this->errorMessages[] = 'De Brincode/locatiecode ' . $external_main_code . ' ' . $external_sub_code . ' in het bestand kon niet gevonden worden in de database. Vraag aan de Test-Correct admin om een schoollocatie aan te maken met de juiste Brincode en locatiecode.';
                        continue;
                        //throw new \Exception('De Brincode/locatiecode ' . $external_main_code . ' ' . $external_sub_code . ' in het bestand kon niet gevonden worden in de database. Vraag aan de Test-Correct admin om een schoollocatie aan te maken met de juiste Brincode en locatiecode.');
                    }

                    if (strlen($external_sub_code) == 1) {
                        $external_sub_code = "0" . $external_sub_code;
                    }

                    $student_email = sprintf('%s@%s', $student_external_code, $this->email_domain);

////                    $student_email = 'rtti_' . $student_external_code . '_' . $external_main_code . '_' . $external_sub_code . '@' . $this->email_domain;
//                    $teacher_email = 'rtti_' . $teacher_external_code . '_' . $external_main_code . '_' . $external_sub_code . '@' . $this->email_domain;


                    if (!in_array($study_year, range(($now->year - 10), ($now->year + 10)))) {
                        $this->errorMessages[] = 'Invalid study year ' . $study_year;
                        //throw new \Exception('Invalid study year ' . $study_year);
                    }

                    // collect years
                    $yearCheck[$study_year] = 1;

                    if (count($yearCheck) > 1) {
                        $this->errorMessages[] = 'Meerdere lesjaren in RTTI bestand ' . implode(',', array_keys($yearCheck));
                        //throw new \Exception('Meerdere lesjaren in RTTI bestand ' . implode(',', array_keys($yearCheck)));
                    }

                    $school_year_id = $this->getSchoolYearId($school_location_id, $study_year);
                    if (!$school_year_id) {
                        $this->importLog('Cannot find school year id for study year ' . $study_year);
                        $this->errorMessages[] = 'Het schooljaar ' . $study_year . ' in het bestand kon niet gevonden '
                            . 'worden in de database voor de schoollocatie met Brincode '
                            . $external_main_code . ' en locatiecode ' . $external_sub_code . '. '
                            . 'Neem contact op met de schoolbeheerder om het schooljaar te laten aanmaken.';
//                        throw new \Exception('Het schooljaar ' . $study_year . ' in het bestand kon niet gevonden '
//                        . 'worden in de database voor de schoollocatie met Brincode '
//                        . $external_main_code . ' en locatiecode ' . $external_sub_code . '. '
//                        . 'Neem contact op met de schoolbeheerder om het schooljaar te laten aanmaken.');
                    }

                    $education_level_id = $this->getStudyDirectionId($study_direction);
                    if (!$education_level_id) {
                        $this->errorMessages[] = 'Onbekende studierichting ' . $study_direction;
                        throw new \Exception('Onbekende studierichting ' . $study_direction);
                    }

                    // check if education level is allowed
                    $education_level_max_years = Educationlevel::select('max_years')->where('id', $education_level_id)->value('max_years');

                    if ($study_year_layer >= $education_level_max_years) {
                        $this->errorMessages[] = 'De les jaar laag ' . $study_year_layer . ' is niet correct. De Studierichting (niveau) ' . $study_direction . ' kan maximaal ' . $education_level_max_years . ' jaren zijn. Pas dit in het bestand aan of neem contact op met ICT';
                        //throw new \Exception('De les jaar laag ' . $study_year_layer . ' is niet correct. De Studierichting (niveau) ' . $study_direction . ' kan maximaal ' . $education_level_max_years . ' jaren zijn. Pas dit in het bestand aan of neem contact op met ICT');
                    }

                    $school_class_id = $this->getSchoolClassId($class_name, $school_location_id, $study_year, $study_year_layer, $education_level_id);
                    $teacher_id = $this->getUserIdForTeacherInLocation($teacher_external_code, $school_location_id);
                    $student_id = $this->getUserIdForLocation($student_external_code, $school_location_id);
                    $subject_id = $this->getSubjectId($subject_abbreviation, $school_location_id);

                    $this->importLog("subject id is " . $subject_id . " for abbreviation " . $subject_abbreviation . " and location " . $school_location_id);

                    if (isset($allClasses[$school_location_id]['school_class_id'])) {
                        if (!in_array($school_class_id, $allClasses[$school_location_id]['school_class_id'])) {
                            $allClasses[$school_location_id]['school_class_id'][] = $school_class_id;
                        }
                    } else {
                        $allClasses[$school_location_id]['school_class_id'][] = $school_class_id;
                    }

                    $allClasses[$school_location_id]['school_year_id'] = $school_year_id;

                    if (!$subject_id) {

                        $this->importLog('Cannot find subject ' . $subject_abbreviation);
                        $this->errorMessages[] = 'Het vak met de afkorting ' . $subject_abbreviation . ' in het bestand kon niet gevonden '
                            . 'worden in de database voor de schoollocatie met Brincode/locatiecode: ' . $external_main_code . ' '
                            . $external_sub_code . '. Neem contact op met de schoolbeheerder om het vak te laten aanmaken';
//                        throw new \Exception('Het vak met de afkorting ' . $subject_abbreviation . ' in het bestand kon niet gevonden '
//                        . 'worden in de database voor de schoollocatie met Brincode/locatiecode: ' . $external_main_code . ' '
//                        . $external_sub_code . '. Neem contact op met de schoolbeheerder om het vak te laten aanmaken');
                    }

                    $this->importLog('school location ' . $school_location_id . ' sub ' . $external_sub_code . ' main ' . $external_main_code);
                    $this->importLog('Start inserting record ' . $index . ' for location ' . $school_location_id . '  BRIN ' . $external_main_code);



                    // class doesnt exist, create it else use it
                    if ($school_class_id == NULL) {

                        $this->importLog('Restoring school class');

                        $school_class_id = $this->createOrRestoreSchoolClass([
                            'school_location_id' => $school_location_id,
                            'education_level_id' => $education_level_id,
                            'school_year_id' => $school_year_id,
                            'name' => $class_name,
                            'education_level_year' => $study_year_layer,
                            'is_main_school_class' => $teacher_is_mentor,
                            'do_not_overwrite_from_interface' => 0
                        ]);

                        $this->create_tally['classes'] ++;

                        $this->importLog('Class ' . $class_name . ' with id ' . $school_class_id . '  created ');
                    } else {

                        $this->importLog('Class ' . $class_name . ' with id ' . $school_class_id . ' exists');
                    }


                    // rule 1 if class exists in import file and TC then set do_not_overwrite to 0 at all times
                    SchoolClass::where('id', $school_class_id)->update(['do_not_overwrite_from_interface' => 0]);

                    if (!isset($studentsPerClass[$school_class_id])) {
                        $studentsPerClass[$school_class_id] = [];
                    }

                    // student is known
                    if ($student_id != NULL) {

                        // student not in class (always the case with a new class)
                        if (!$this->getStudentIdForClass($student_id, $school_class_id)) {

                            $this->createOrRestoreStudent([
                                'user_id' => $student_id,
                                'class_id' => $school_class_id
                            ]);

                            $this->importLog('Added student with id ' . $student_id . ' to class ' . $school_class_id);
                        } else {

                            $this->importLog('Student with id ' . $student_id . ' exists in class ' . $school_class_id);
                        }

                        $user = User::where('external_id', $student_external_code)
                            ->where('school_location_id', $school_location_id);


                        if ($user->count() > 1) {
                            $this->errorMessages[] = 'Dubbele externe id voor dezelfde gebruiker ' . $student_external_code;
                            //throw new \Exception('Dubbele externe id voor dezelfde gebruiker ' . $student_external_code);
                        }
                    } else {

                        $this->importLog("Create student with external code " . $student_external_code);

                        $user_data = ['external_id' => $student_external_code,
                            'name_first' => $student_name_first,
                            'name_suffix' => $student_name_suffix,
                            'name' => $student_name_last,
                            'username' => $student_email, // moet email zijn?
                            'school_location_id' => $school_location_id,
                            'user_roles' => [3]
                        ];

                        $user_id = $this->createOrRestoreUser($user_data);

                        $this->importLog('User created for student with id ' . $user_id . ' and external code ' . $student_external_code);

                        $this->createOrRestoreStudent([
                            'user_id' => $user_id,
                            'class_id' => $school_class_id
                        ]);

                        $this->create_tally['students'] ++;

                        $student_id = $user_id;
                    }

                    $studentsPerClass[$school_class_id][] = $student_id;

                    if ($teacher_id != NULL) {
                        $user_collection =  User::join('school_location_user', 'users.id', '=','school_location_user.user_id')
                            ->where('school_location_user.school_location_id', $school_location_id)
                            ->where('school_location_user.external_id', $teacher_external_code)
                            ->get();

                        if ($user_collection->count() > 1) {

                            throw new \Exception('Dubbele externe id voor leraar met externe code ' . $teacher_external_code);
                        }

                        $user = $user_collection->first();

                        $teacher_table_id = $this->getTeachersForClassSubject($teacher_id, $school_class_id, $subject_id);

                        if ($teacher_table_id == NULL) {

                            $teacher = $this->createOrRestoreTeacher([
                                'user_id' => $user->id,
                                'class_id' => $school_class_id,
                                'subject_id' => $subject_id
                            ]);

                            $this->importLog('Assigned teacher with id ' . $user->id . ' to class id ' . $school_class_id . ' and subject id ' . $subject_id);
                        } else {

                            $this->importLog("Teacher already assigned with id " . $school_class_id . " and subject id " . $subject_id);
                        }
                    } else {
                        $missing_user =  [
                            $teacher_name_first,
                            $teacher_name_suffix,
                            $teacher_name_last
                        ];
                        if(!array_key_exists('missing_teachers',$this->errorMessages)){
                            $this->errorMessages['missing_teachers'] = [];
                        }
                        $this->errorMessages['missing_teachers'][] = $missing_user;
//                        throw new \Exception('
//                        Voor de onderstaande docenten bestaat nog geen account. Maak die eerst aan voordat u de RTTI importer draait:
//                        '. $missing_user);


                        $this->importLog("User missing Teacher not created " . implode(';',$missing_user));
                        continue;
                    }

                    if (isset($teachersPerClass[$teacher_id])) {
                        if (!in_array(['subject_id' => $subject_id, 'class_id' => $school_class_id], $teachersPerClass[$teacher_id])) {
                            $teachersPerClass[$teacher_id][] = ['subject_id' => $subject_id, 'class_id' => $school_class_id];
                        }
                    } else {
                        $teachersPerClass[$teacher_id][] = ['subject_id' => $subject_id, 'class_id' => $school_class_id];
                    }

                    // collect teacher class combinations
                    $classTeacherCheck[$teacher_id] = $school_class_id;

                    foreach ($teachersPerClass as $teacher_id => $class_subjects) {
                        foreach ($class_subjects as $class_subject_tuple) {
                            $this->importLog('Assigned teacher ' . $teacher_id . ' where class ' . $class_subject_tuple['class_id'] . ' and subject ' . $class_subject_tuple['subject_id']);
                        }
                    }

                    // set mentor state
                    if ($teacher_is_mentor) {

                        $classMentorCheck[$school_class_id][]=$teacher_id;

                        $this->importLog('Setting teacher as mentor for ' . $school_class_id . ' ' . $teacher_id);

                        $this->setTeacherAsMentor($teacher_id, $school_class_id);

                    }

                    $this->importLog('-------- index ' . $index . ' data lines ' . $this->csv_data_lines);

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

                            $this->importLog('Students in class ' . $class_id . ' ' . implode(',', $students));

                            $this->delete_tally['students'] += Student::whereNotIn('user_id', $students)
                                ->where('class_id', $class_id)
                                ->count();

                            Student::whereNotIn('user_id', $students)
                                ->where('class_id', $class_id)
                                ->delete();

                            $this->importLog("Deleted students from class " . $class_id);
                        }

                        $class_subjects_combined = [];
                        // remove teachers from classes where they are not assigned
                        // according to the upload

                        $teachers_per_class_subject = [];

                        foreach ($teachersPerClass as $teacher_id => $class_subjects) {
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

                                $this->importLog('deleting other teachers from class ' . $class_subject_tuple['class_id'] . ' and subject ' . $class_subject_tuple['subject_id']);
                            }
                        }

                        /*
                          // loop through the class/subjects by teacher
                          foreach ($teachersPerClass as $teacher_id => $class_subjects) {

                          // loop through the class subjects
                          foreach ($class_subjects as $class_subject_tuple) {

                          $class_subjects_combined[$class_subject_tuple['class_id']][] = $class_subject_tuple['subject_id'];
                          $class_teachers_combined[$class_subject_tuple['class_id']][] = $teacher_id;

                          $this->delete_tally['teachers'] += Teacher::where('user_id', '<>', $teacher_id)
                          ->where('class_id', $class_subject_tuple['class_id'])
                          ->where('subject_id', $class_subject_tuple['subject_id'])
                          ->count();

                          // @TODO er kunnen meerdere docenten hetzelfde vak geven aan dezelfde klas, dus er moet iets anders bedacht worden
                          // voor het verwijderen van docenten die ECHT NIET MEER gekoppeld zijn aan deze klas met dit vak
                          Teacher::where('user_id', '<>', $teacher_id)
                          ->where('class_id', $class_subject_tuple['class_id'])
                          ->where('subject_id', $class_subject_tuple['subject_id'])
                          ->delete();

                          $this->importLog('deleting other teachers from class ' . $class_subject_tuple['class_id'] . ' and subject ' . $class_subject_tuple['subject_id']);
                          }
                          }
                         * *
                         */

                        // disconnect teachers and subjects that where not in the import file

                        foreach ($class_subjects_combined as $class_id => $subject_ids) {

                            // delete teachers where the subject is not in the import for the class
                            $deleted_teachers = Teacher::leftjoin('school_classes', 'school_classes.id', '=', 'teachers.class_id')
                                ->where('school_classes.do_not_overwrite_from_interface', 0)
                                ->where('teachers.class_id', $class_id)
                                ->whereNotIn('teachers.subject_id', $subject_ids)
                                ->delete();
                        }

                        $this->importLog('teachers deleted due to subject not in import ' . $deleted_teachers);

                        $this->delete_tally['teachers'] += $deleted_teachers;

                        foreach ($allClasses as $school_location_id => $data) {

                            $ids = SchoolClass::select('id')
                                ->where('school_location_id', $school_location_id)
                                ->where('do_not_overwrite_from_interface', 0)
                                ->where('school_year_id', $data['school_year_id'])
                                ->whereNotIn('id', array_unique($class_ids))
                                ->get()
                                ->toArray();

                            foreach ($ids as $id) {

                                $this->delete_tally['classes'] ++;

                                // remove student from class
                                Student::where('class_id', $id['id'])->delete();
                                Teacher::where('class_id', $id['id'])->delete();
                                Mentor::where('school_class_id', $id['id'])->delete();
                                SchoolClass::where('id', $id['id'])->delete();
                            }
                        }
                    }
                }
            }
            if(count($this->errorMessages)>0){
                throw new \Exception('collected errors');
            }
        } catch (\Throwable $e) {
            \DB::rollback();
            $this->importLog("Transaction failed with message " . $e->getMessage());
            if($e->getMessage()=='collected errors'){
                $uniqueErrors =  $this->makeErrorsUnique();
                return ['errors' => $uniqueErrors];
            }
            return ['errors' => [$e->getMessage()]];
        }

        \DB::commit();

        $this->importLog('import done');

        return ['data' => 'Versie 0.1. De import was succesvol. '
            . ' Er zijn ' . $this->create_tally['students'] . ' leerlingen aangemaakt, '
            . $this->create_tally['teachers'] . ' docenten en '
            . $this->create_tally['classes'] . ' klassen. '
            . 'c' . $this->delete_tally['classes'] . 't' . $this->delete_tally['teachers'] . 's' . $this->delete_tally['students']];
    }

    /**
     *
     * @return string
     */
    public function validate() {

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
                                $errors[] = $field . " missing ";
                            }
                            break;
                        case "Brincode":
                            // alphanumeric max 4 chars
                            if ($row[$fieldindex] == "" || !ctype_alnum($row[$fieldindex]) || strlen($row[$fieldindex]) > 4) {
                                $errors[] = $field . " error ";
                            }
                            break;
                        case "Locatiecode":
                            if ($row[$fieldindex] == "") {
                                $errors[] = $field . " missing " . $row[$fieldindex];
                            }
                            if (\strlen($row[$fieldindex]) > 2 || !ctype_digit($row[$fieldindex])) {
                                $errors[] = $field . " niet numeriek of te lang " . $row[$fieldindex];
                            }
                            break;
                        case "Studierichting":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || \strlen($row[$fieldindex]) > 45) {
                                $errors[] = $field . " incorrect (" . $row[$fieldindex] . ")";
                            }
                            break;
                        case "lesJaarlaag":
                            if (!ctype_digit($row[$fieldindex])) {
                                $errors[] = $field . " incorrect (" . $row[$fieldindex] . ")";
                            }
                            break;
                        case "Schooljaar":
                            if (!ctype_digit(substr($row[$fieldindex], 0, 4))) {
                                $errors[] = $field . " incorrect (" . $row[$fieldindex] . ").";
                            }
                            break;
                        case "leeStamNummer":
                            if ($row[$fieldindex] == "" || !ctype_alnum($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een stamnummer (" . $row[$fieldindex] . ") van een leerling kan maximaal 45 tekens lang zijn en mag niet leeg zijn.";
                            }
                            break;
                        case "leeAchternaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een achternaam (" . $row[$fieldindex] . ") van een leerling kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "leeTussenvoegsels":
                            if ($row[$fieldindex] != '' && (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45)) {
                                $errors[] = "Een tussenvoegsel (" . $row[$fieldindex] . ")  van een leerling kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "leeVoornaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een voornaam  (" . $row[$fieldindex] . ")  van een leerling kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "lesNaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een lesnaam  (" . $row[$fieldindex] . ")  kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "vakNaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 10) {
                                $errors[] = "Een vaknaam (" . $row[$fieldindex] . ") is een afkorting en kan maximaal 10 tekens lang zijn.";
                            }
                            break;
                        case "docStamNummer":
                            if (!ctype_alnum($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een stamNummer (" . $row[$fieldindex] . ") kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "docAchternaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een achternaam  (" . $row[$fieldindex] . ")  van een docent kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "docTussenvoegsels":
                            if ($row[$fieldindex] != '' && (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45)) {
                                $errors[] = "Een tussenvoegsel  (" . $row[$fieldindex] . ")  van een docent kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "docVoornaam":
                            if (!$this->checkAlphaNumericAndSpace($row[$fieldindex]) || strlen($row[$fieldindex]) > 45) {
                                $errors[] = "Een voornaam (" . $row[$fieldindex] . ")  van een docent kan maximaal 45 tekens lang zijn.";
                            }
                            break;
                        case "IsMentor":
                            if ($row[$fieldindex] != 1 && $row[$fieldindex] != 0) {
                                $errors[] = " Het IsMentor veld moet een 0 of een 1 zijn. 1 betekent dat de docent een mentor is en 0 dat de docent geen mentor is. De waarde is nu (" . $row[$fieldindex] . ")";
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
     * @param type $email_domain
     * @return boolean
     * @throws \Exception
     */
    public function validateEmailDomain($email_domain) {

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
     * @param type $external_sub_code
     * @param type $external_main_code
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
     * @param type $class_name
     * @param type $school_location_id
     * @param type $year
     * @param type $education_level_year
     * @param type $education_level_id
     * @return type
     */
    public function getSchoolClassId($class_name, $school_location_id, $year, $education_level_year, $education_level_id) {

        $school_year_id = SchoolLocationSchoolYear::select('school_year_id')
            ->leftjoin('school_years', 'school_years.id', '=', 'school_location_school_years.school_year_id')
            ->whereNull('school_location_school_years.deleted_at')
            ->where('school_location_school_years.school_location_id', '=', $school_location_id)
            ->where('school_years.year', $year)
            ->value('school_year_id');

        if ($school_year_id != NULL) {

            return SchoolClass::where('name', $class_name)
                ->where('school_location_id', $school_location_id)
                ->where('school_year_id', $school_year_id)
                ->where('education_level_year', $education_level_year)
                ->where('education_level_id', $education_level_id)
                ->whereNull('school_classes.deleted_at')
                ->value('id');
        } else {
            return NULL;
        }
    }

    /**
     *
     * @param type $user_data
     * @return type
     */
    public function createOrRestoreUser($user_data) {

        $user = User::withTrashed()
            ->where('external_id', $user_data['external_id'])
            ->where('school_location_id', $user_data['school_location_id'])
            ->first();

        if ($user != NULL) {

            $user->restore();
        } else {

            $userFactory = new Factory(new User());
            $user = $userFactory->generate($user_data);
        }

        return $user->id;
    }

    /**
     *
     * @param type $teacher_data
     * @return type
     */
    public function createOrRestoreTeacher($teacher_data) {

        $teacher = Teacher::withTrashed()
            ->where('class_id', $teacher_data['class_id'])
            ->where('user_id', $teacher_data['user_id'])
            ->where('subject_id', $teacher_data['subject_id'])
            ->first();

        if ($teacher != NULL) {

            $teacher->restore();

            return $teacher;
        } else {

            return Teacher::Create($teacher_data);
        }
    }

    /**
     *
     * @param type $student_data
     * @return type
     */
    public function createOrRestoreStudent($student_data) {

        $student = Student::withTrashed()
            ->where('class_id', $student_data['class_id'])
            ->where('user_id', $student_data['user_id'])
            ->first();

        if ($student != NULL) {

            $student->restore();

            return $student;
        } else {

            return Student::Create($student_data);
        }
    }

    /**
     *
     * @param type $data
     * @return type
     */
    public function createOrRestoreSchoolClass($data) {

        $schoolclass = SchoolClass::withTrashed()
            ->where('school_location_id', $data['school_location_id'])
            ->where('education_level_id', $data['education_level_id'])
            ->where('school_year_id', $data['school_year_id'])
            ->where('name', $data['name'])
            ->where('education_level_year', $data['education_level_year'])
            ->first();

        if ($schoolclass !== NULL) {
            $schoolclass->restore();

            return $schoolclass->getKey();
        } else {
            return SchoolClass::create($data)->getKey();
        }
    }

    /**
     *
     * @param type $abbreviation
     * @param type $school_location_id
     * @return type
     */
    public function getSubjectId($abbreviation, $school_location_id) {

        $result = Subject::select('subjects.id as id')
            ->join('sections as SEC', 'SEC.id', '=', 'subjects.section_id')
            ->join('school_location_sections as SLS', 'SLS.section_id', '=', 'SEC.id')
            ->where('subjects.abbreviation', $abbreviation)
            ->where('SLS.school_location_id', $school_location_id)
            ->whereNull('SEC.deleted_at')
            ->whereNull('SLS.deleted_at')
            ->whereNull('subjects.deleted_at')
            ->first();

        if (is_object($result)) {
            return $result->getKey();
        } else {
            return NULL;
        }
    }

    /**
     *
     * @param type $name
     * @return type
     */
    public function getStudyDirectionId($name) {

        $translated_name = $this->translateStudyDirectionName($name);

        return EducationLevel::where('name', $translated_name)
            ->value('id');
    }

    /**
     *
     * @param type $name
     * @return type string
     */
    public function translateStudyDirectionName($name) {

        return array_key_exists($name, $this->studydirectionarray) ? $this->studydirectionarray[$name] : $name;
    }

    /**
     *
     * @param type $external_id
     * @param type $school_location_id
     * @return type int
     */
    public function getUserIdForLocation($external_id, $school_location_id) {

        return User::where('external_id', $external_id)
            ->where('school_location_id', $school_location_id)
            ->value('id');
    }

    /**
     *
     * @param type $external_id
     * @param type $school_location_id
     * @return type int
     */
    public function getUserIdForTeacherInLocation($external_id, $school_location_id) {
        return User::join('school_location_user', 'users.id', '=','school_location_user.user_id')
            ->where('school_location_user.school_location_id', $school_location_id)
            ->where('school_location_user.external_id', $external_id)
            ->value('id');
    }

    /**
     *
     * @param type $user_id
     * @param type $class_id
     * @return type int
     */
    public function getStudentIdForClass($user_id, $class_id) {

        return Student::where('user_id', $user_id)
            ->where('class_id', $class_id)
            ->value('user_id');
    }

    /**
     *
     * @param type $user_id
     * @param type $class_id
     * @param type $subject_id
     * @return type
     */
    public function getTeachersForClassSubject($user_id, $class_id, $subject_id) {

        return Teacher::where('user_id', $user_id)
            ->where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->value('user_id');
    }

    /**
     *
     * @param type $teacher_id
     * @param type $school_class_id
     * @return type
     */
    public function setTeacherAsMentor($teacher_id, $school_class_id) {

        // only mentors in the file are touched


        $mentor = Mentor::withTrashed()
            ->where('user_id', $teacher_id)
            ->where('school_class_id', $school_class_id);

        if ($mentor->value('user_id') != NULL) {
            $mentor->restore();
        } else {

            $mentor = Mentor::create(['user_id' => $teacher_id, 'school_class_id' => $school_class_id]);
        }

        return $mentor->value('user_id');
    }

    /**
     *
     * @param type $teacher_id
     * @param type $school_class_id
     * @return boolean
     */
    public function removeTeacherAsMentor($class_mentor_check) {

        foreach ($class_mentor_check as $class_id =>$mentor_ids) {
            Mentor::whereNotIn('user_id', array_unique($mentor_ids))
                ->where('school_class_id', $class_id)
                ->delete();
        }

        return true;
    }

    /**
     *
     * @param type $school_location_id
     * @param type $year
     * @return type
     */
    public function getSchoolYearId($school_location_id, $year) {

        return SchoolLocationSchoolYear::leftJoin('school_years as SY', 'id', '=', 'school_year_id')
            ->where('school_location_id', $school_location_id)
            ->where('SY.year', $year)
            ->whereNull('school_location_school_years.deleted_at')
            ->whereNull('SY.deleted_at')
            ->value('school_year_id');
    }

    /**
     *
     * @param type $file
     * @return type
     */
    public function getDataFromFile($file, $separator) {

        $rows = [];

        if (!in_array($separator, [';', ','])) {
            throw new \Exception('Scheidingsteken ' . $separator . ' is incorrect');
        }

        // read csv and put into array
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $separator)) !== FALSE) {
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
        foreach ($this->errorMessages as $key => $value)
        {
            if(is_array($value)){
                $returnArray[$key] = $value;
                continue;
            }
            if(!in_array($value,$returnArray)){
                $returnArray[] = $value;
            }
        }
        return $returnArray;
    }
}
