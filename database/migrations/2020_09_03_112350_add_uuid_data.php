<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use tcCore\SchoolYear;
use Ramsey\Uuid\Uuid;
use tcCore\Address;
use tcCore\Answer;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\CompletionQuestion;
use tcCore\Contact;
use tcCore\DrawingQuestion;
use tcCore\EducationLevel;
use tcCore\FileManagement;
use tcCore\GradingScale;
use tcCore\GroupQuestion;
use tcCore\GroupQuestionQuestion;
use tcCore\InfoscreenQuestion;
use tcCore\Invigilator;
use tcCore\License;
use tcCore\Manager;
use tcCore\MatchingQuestion;
use tcCore\MatrixQuestion;
use tcCore\Mentor;
use tcCore\Message;
use tcCore\MultipleChoiceQuestion;
use tcCore\OnboardingWizard;
use tcCore\OnboardingWizardStep;
use tcCore\OpenQuestion;
use tcCore\Period;
use tcCore\Question;
use tcCore\RankingQuestion;
use tcCore\SalesOrganization;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationAddress;
use tcCore\SchoolLocationContact;
use tcCore\SchoolLocationIp;
use tcCore\SchoolLocationSchoolYear;
use tcCore\Section;
use tcCore\Student;
use tcCore\Subject;
use tcCore\Tag;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\TestTakeEvent;
use tcCore\TestTakeEventType;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class AddUuidData extends Migration
{

    protected $tables = ['users','periods','sections','subjects','school_classes','school_locations','school_location_ips','contacts','school_location_contacts','school_location_contacts',
        'school_location_school_years','school_location_addresses','mentors','managers','addresses','answers','onboarding_wizard_steps','tests',
        'test_questions','onboarding_wizards','group_question_questions','file_managements','test_takes','test_participants','test_take_events','education_levels',
        'invigilators','students','open_questions','attainments','teachers','sales_organizations','umbrella_organizations','schools','licenses','messages', 'grading_scales',
        'base_subjects', 'tags','group_questions','infoscreen_questions','completion_questions','multiple_choice_questions','ranking_questions','matching_questions',
        'drawing_questions','matrix_questions','questions','test_take_event_types','school_years',];

    protected $questionTables = ['completion_questions','multiple_choice_questions','infoscreen_questions','open_questions','matching_questions','ranking_questions','drawing_questions','matrix_questions','group_questions'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $start = microtime(true);
        set_time_limit(10 * 60);
        ini_set('memory_limit', '-1');

        // as per https://stackoverflow.com/a/45818109
        $uuidSelectFunction = "select unhex(replace(concat(replace(uuid_v4(),'-',''),created_at),created_at,''))";

//        DB::unprepared("
//DROP FUNCTION IF EXISTS uuid_v4;");
        /**
         * if you get an error about collate on inserting the data
         * add this function youself to the database by hand
         * Start with on the first row
         * DELIMITER //
         * and then replace END by END//
         * followed by a extra line DELIMITER;
         */

        $functionCreation = <<<FUNC
CREATE FUNCTION uuid_v4()
    RETURNS CHAR(36)
NOT DETERMINISTIC -- multiple RAND()'s
NO SQL
BEGIN
    -- Generate 8 2-byte strings that we will combine into a UUIDv4
    SET @h1 = SUBSTRING(UPPER(REPLACE(UUID(),'-','')),4,8);
    SET @h2 = '';
    SET @h3 = LPAD(HEX(FLOOR(RAND() * 0xffff)), 4, '0');
    SET @h6 = LPAD(HEX(FLOOR(RAND() * 0xffff)), 4, '0');
    SET @h7 = LPAD(HEX(FLOOR(RAND() * 0xffff)), 4, '0');
    SET @h8 = LPAD(HEX(FLOOR(RAND() * 0xffff)), 4, '0');

    -- 4th section will start with a 4 indicating the version
    SET @h4 = CONCAT('4', LPAD(HEX(FLOOR(RAND() * 0x0fff)), 3, '0'));

    -- 5th section first half-byte can only be 8, 9 A or B
    SET @h5 = CONCAT(HEX(FLOOR(RAND() * 4 + 8)),
                LPAD(HEX(FLOOR(RAND() * 0x0fff)), 3, '0'));

    -- Build the complete UUID
    RETURN LOWER(CONCAT(
        @h1, @h2, '-', @h3, '-', @h4, '-', @h5, '-', @h6, @h7, @h8
    ));
END
FUNC;


        try {
            DB::unprepared($functionCreation);
        } catch(\Exception $e){}

        try {
            $questionTables = collect($this->questionTables);
            $collectionWithoutQuestionTables = collect($this->tables)->reject(function ($val) use ($questionTables) {
                return $questionTables->contains($val);
            });

            $collectionWithoutQuestionTables->unique()->each(function ($tableName) use ($uuidSelectFunction) {
                $expression = DB::raw('select count(*) as aantal from ' . $tableName . ' where uuid is null');
                $count = DB::select($expression->getValue(DB::connection()->getQueryGrammar()))[0]->aantal;
                $numberPerRound = 100000;
                $rounds = ceil($count / $numberPerRound);
                echo sprintf('[%s] records: %d', strtoupper($tableName), $count) . PHP_EOL;
                echo sprintf('[%s] rounds : %d', strtoupper($tableName), $rounds) . PHP_EOL;
                for ($i = 0; $i <= $rounds; $i++) {
                    echo sprintf('[%s] round : %d', strtoupper($tableName), $i) . PHP_EOL;
                    DB::unprepared('update ' . $tableName . ' set uuid = (' . $uuidSelectFunction . ') where uuid is null order by created_at limit ' . $numberPerRound);
                }
                echo sprintf('[%s] done', strtoupper($tableName)) . PHP_EOL;
            });

            collect($this->questionTables)->each(function ($tableName) {
                echo sprintf('[%s] getting question uuid in sync with %s', strtoupper($tableName), $tableName) . PHP_EOL;
                DB::unprepared('update ' . $tableName . ' inner join questions on (questions.id = ' . $tableName . '.id) set ' . $tableName . '.uuid = questions.uuid');
                echo sprintf('[%s] done', strtoupper($tableName)) . PHP_EOL;
            });
        } catch (Exception $e) {
            echo $e->getMessage().PHP_EOL;
            echo 'Do you get an error concerning collate, copy the following into your database client:'.PHP_EOL;
            echo '-- ==================================='.PHP_EOL;
            echo 'DELIMITER //'.PHP_EOL;
            echo 'DROP FUNCTION IF EXISTS uuid_v4;//'.PHP_EOL;
            echo $functionCreation.'//'.PHP_EOL;
            echo 'DELIMITER ;'.PHP_EOL;
            echo '-- ==================================='.PHP_EOL;
            exit;

        }

        $duration = new Duration(microtime(true) - $start);
        echo sprintf('Duration %s',$duration->humanize()).PHP_EOL;
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        collect($this->tables)->unique()->each(function($tableName){
            if (Schema::hasColumn($tableName, 'uuid')) {
                DB::statement('update '.$tableName.' set uuid = null');
            }
        });
    }
}

class Duration
{
    public $days;
    public $hours;
    public $minutes;
    public $seconds;

    public $hoursPerDay;

    private $output;
    private $daysRegex;
    private $hoursRegex;
    private $minutesRegex;
    private $secondsRegex;

    /**
     * Duration constructor.
     *
     * @param int|float|string|null $duration
     */
    public function __construct($duration = null, $hoursPerDay = 24)
    {
        $this->reset();

        $this->daysRegex = '/([0-9\.]+)\s?(?:d|D)/';
        $this->hoursRegex = '/([0-9\.]+)\s?(?:h|H)/';
        $this->minutesRegex = '/([0-9]{1,2})\s?(?:m|M)/';
        $this->secondsRegex = '/([0-9]{1,2}(\.\d+)?)\s?(?:s|S)/';

        $this->hoursPerDay = $hoursPerDay;

        if (null !== $duration) {
            $this->parse($duration);
        }
    }

    /**
     * Attempt to parse one of the forms of duration.
     *
     * @param  int|float|string|null $duration A string or number, representing a duration
     * @return self|bool returns the Duration object if successful, otherwise false
     */
    public function parse($duration)
    {
        $this->reset();

        if (null === $duration) {
            return false;
        }

        if (is_numeric($duration)) {
            $this->seconds = (float)$duration;

            if ($this->seconds >= 60) {
                $this->minutes = (int)floor($this->seconds / 60);

                // count current precision
                $precision = 0;
                if (($delimiterPos = strpos($this->seconds, '.')) !== false) {
                    $precision = strlen(substr($this->seconds, $delimiterPos + 1));
                }

                $this->seconds = (float)round(($this->seconds - ($this->minutes * 60)), $precision);
            }

            if ($this->minutes >= 60) {
                $this->hours = (int)floor($this->minutes / 60);
                $this->minutes = (int)($this->minutes - ($this->hours * 60));
            }

            if ($this->hours >= $this->hoursPerDay) {
                $this->days = (int)floor($this->hours / $this->hoursPerDay);
                $this->hours = (int)($this->hours - ($this->days * $this->hoursPerDay));
            }

            return $this;
        }

        if (preg_match('/\:/', $duration)) {
            $parts = explode(':', $duration);

            if (count($parts) == 2) {
                $this->minutes = (int)$parts[0];
                $this->seconds = (float)$parts[1];
            } else {
                if (count($parts) == 3) {
                    $this->hours = (int)$parts[0];
                    $this->minutes = (int)$parts[1];
                    $this->seconds = (float)$parts[2];
                }
            }

            return $this;
        }

        if (preg_match($this->daysRegex, $duration) ||
            preg_match($this->hoursRegex, $duration) ||
            preg_match($this->minutesRegex, $duration) ||
            preg_match($this->secondsRegex, $duration)) {
            if (preg_match($this->daysRegex, $duration, $matches)) {
                $num = $this->numberBreakdown((float) $matches[1]);
                $this->days += (int)$num[0];
                $this->hours += $num[1] * $this->hoursPerDay;
            }

            if (preg_match($this->hoursRegex, $duration, $matches)) {
                $num = $this->numberBreakdown((float) $matches[1]);
                $this->hours += (int)$num[0];
                $this->minutes += $num[1] * 60;
            }

            if (preg_match($this->minutesRegex, $duration, $matches)) {
                $this->minutes += (int)$matches[1];
            }

            if (preg_match($this->secondsRegex, $duration, $matches)) {
                $this->seconds += (float)$matches[1];
            }

            return $this;
        }

        return false;
    }

    /**
     * Returns the duration as an amount of seconds.
     *
     * For example, one hour and 42 minutes would be "6120"
     *
     * @param  int|float|string $duration A string or number, representing a duration
     * @param  int|bool $precision Number of decimal digits to round to. If set to false, the number is not rounded.
     * @return int|float
     */
    public function toSeconds($duration = null, $precision = false)
    {
        if (null !== $duration) {
            $this->parse($duration);
        }
        $this->output = ($this->days * $this->hoursPerDay * 60 * 60) + ($this->hours * 60 * 60) + ($this->minutes * 60) + $this->seconds;

        return $precision !== false ? round($this->output, $precision) : $this->output;
    }

    /**
     * Returns the duration as an amount of minutes.
     *
     * For example, one hour and 42 minutes would be "102" minutes
     *
     * @param  int|float|string $duration A string or number, representing a duration
     * @param  int|bool $precision Number of decimal digits to round to. If set to false, the number is not rounded.
     * @return int|float
     */
    public function toMinutes($duration = null, $precision = false)
    {
        if (null !== $duration) {
            $this->parse($duration);
        }

        // backward compatibility, true = round to integer
        if ($precision === true) {
            $precision = 0;
        }

        $this->output = ($this->days * $this->hoursPerDay * 60 * 60) + ($this->hours * 60 * 60) + ($this->minutes * 60) + $this->seconds;
        $result = intval($this->output()) / 60;

        return $precision !== false ? round($result, $precision) : $result;
    }

    /**
     * Returns the duration as a colon formatted string
     *
     * For example, one hour and 42 minutes would be "1:43"
     * With $zeroFill to true :
     *   - 42 minutes would be "0:42:00"
     *   - 28 seconds would be "0:00:28"
     *
     * @param  int|float|string|null $duration A string or number, representing a duration
     * @param  bool $zeroFill A boolean, to force zero-fill result or not (see example)
     * @return string
     */
    public function formatted($duration = null, $zeroFill = false)
    {
        if (null !== $duration) {
            $this->parse($duration);
        }

        $hours = $this->hours + ($this->days * $this->hoursPerDay);

        if ($this->seconds > 0) {
            if ($this->seconds < 10 && ($this->minutes > 0 || $hours > 0 || $zeroFill)) {
                $this->output .= '0' . $this->seconds;
            } else {
                $this->output .= $this->seconds;
            }
        } else {
            if ($this->minutes > 0 || $hours > 0 || $zeroFill) {
                $this->output = '00';
            } else {
                $this->output = '0';
            }
        }

        if ($this->minutes > 0) {
            if ($this->minutes <= 9 && ($hours > 0 || $zeroFill)) {
                $this->output = '0' . $this->minutes . ':' . $this->output;
            } else {
                $this->output = $this->minutes . ':' . $this->output;
            }
        } else {
            if ($hours > 0 || $zeroFill) {
                $this->output = '00' . ':' . $this->output;
            }
        }

        if ($hours > 0) {
            $this->output = $hours . ':' . $this->output;
        } else {
            if ($zeroFill) {
                $this->output = '0' . ':' . $this->output;
            }
        }

        return $this->output();
    }

    /**
     * Returns the duration as a human-readable string.
     *
     * For example, one hour and 42 minutes would be "1h 42m"
     *
     * @param  int|float|string $duration A string or number, representing a duration
     * @return string
     */
    public function humanize($duration = null)
    {
        if (null !== $duration) {
            $this->parse($duration);
        }

        if ($this->seconds > 0 || ($this->seconds === 0.0 && $this->minutes === 0 && $this->hours === 0 && $this->days === 0)) {
            $this->output .= $this->seconds . 's';
        }

        if ($this->minutes > 0) {
            $this->output = $this->minutes . 'm ' . $this->output;
        }

        if ($this->hours > 0) {
            $this->output = $this->hours . 'h ' . $this->output;
        }

        if ($this->days > 0) {
            $this->output = $this->days . 'd ' . $this->output;
        }

        return trim($this->output());
    }


    private function numberBreakdown($number, $returnUnsigned = false)
    {
        $negative = 1;

        if ($number < 0) {
            $negative = -1;
            $number *= -1;
        }

        if ($returnUnsigned) {
            return array(
                floor($number),
                ($number - floor($number))
            );
        }

        return array(
            floor($number) * $negative,
            ($number - floor($number)) * $negative
        );
    }


    /**
     * Resets the Duration object by clearing the output and values.
     *
     * @access private
     * @return void
     */
    private function reset()
    {
        $this->output = '';
        $this->seconds = 0.0;
        $this->minutes = 0;
        $this->hours = 0;
        $this->days = 0;
    }

    /**
     * Returns the output of the Duration object and resets.
     *
     * @access private
     * @return string
     */
    private function output()
    {
        $out = $this->output;

        $this->reset();

        return $out;
    }
}

