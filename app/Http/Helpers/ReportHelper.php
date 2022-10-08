<?php


namespace tcCore\Http\Helpers;


use Carbon\Carbon;
use tcCore\FileManagement;
use tcCore\FileManagementStatusLog;
use tcCore\GeneralTermsLog;
use tcCore\Period;
use tcCore\Question;
use tcCore\QuestionAuthor;
use tcCore\Role;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationReport;
use tcCore\SchoolLocationSchoolYear;
use tcCore\Scopes\ArchivedScope;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\TestTakeStatusLog;
use tcCore\User;
use tcCore\UserRole;

class ReportHelper
{

    protected $reference;
    protected $type;
    protected $period;

    const SCHOOLLOCATION = 'schoolLocation';
    const USER = 'user';

    public function __construct($reference)
    {
        // we only can have two different types of references: User or SchoolLocation
        $allowedClasses = [User::class, SchoolLocation::class];
        if (!in_array(get_class($reference), $allowedClasses)) {
            throw new \Exception('Report does not accept a ' . get_class($reference) . ' model');
        }
        $this->reference = $reference;
        $this->type = (get_class($reference) === User::class) ? self::USER : self::SCHOOLLOCATION;
        if ($this->type === self::SCHOOLLOCATION) {
            $this->setCurrentPeriod();
        }
    }

    protected function setCurrentPeriod()
    {
        $this->period = null;
        $this->reference->schoolLocationSchoolYears()->with('schoolYear', 'schoolYear.periods')->get()->each(function (SchoolLocationSchoolYear $y) {
            if (null !== $y->schoolYear && null !== $y->schoolYear->periods) {
                $y->schoolYear->periods->each(function (Period $p) {
                    if ($p->isActual()) {
                        if ($this->hasCurrentPeriod()) {
                            // we check if we need to expand the current period in rare case there are more than 1 current periods
                            if ($p->start_date <
                                $this->period->start_date) {
                                $this->period->start_date = $p->start_date;
                            }
                            if ($p->end_date >
                                $this->period->end_date) {
                                $this->period->end_date = $p->end_date;
                            }
                        } else {
                            $this->period = $p;
                        }
                    }
                });
            }
        });
        if (null === $this->period) {
            logger(sprintf('no current period found for school location %s (%d)', $this->reference->name, $this->reference->getKey()));
        }
    }

    protected function hasCurrentPeriod()
    {
        return (bool)$this->period != null;
    }

    protected function attachReference($builder, $table)
    {
        $tableColumn = sprintf('%s.%s', $table, $this->getColumn());
        return $builder->where($tableColumn, $this->reference->getKey());
    }

    protected function getColumn()
    {
        if ($this->type === self::USER) {
            return 'user_id';
        } else if ($this->type === self::SCHOOLLOCATION) {
            return 'school_location_id';
        } else {
            throw new \Exception('Unknown model ' . get_class($this->reference));
        }
    }

    /**
     *
     */
    public function nrLicenses()
    {
        if ($this->type === self::SCHOOLLOCATION) {
            if (!$this->hasCurrentPeriod()) {
                return -1;
            }
            $builder = \DB::table('licenses');
            $this->attachReference($builder, 'licenses');
            $builder->where(function ($q) {
                $q->where(function ($q1) {
                    // license starts earlier and ends later than current period
                    $q1->where('licenses.start', '<=', $this->period->start_date);
                    $q1->where('licenses.end', '>=', $this->period->end_date);
                });
                $q->orWhere(function ($q2) {
                    // license starts after en ends earlier than current period
                    $q2->where('licenses.start', '>', $this->period->start_date);
                    $q2->where('licenses.end', '<', $this->period->end_date);
                });
                $q->orWhere(function ($q3) {
                    // license starts earlier than current period start and ends earlier then current period end
                    $q3->where('licenses.start', '<=', $this->period->start_date);
                    $q3->where('licenses.end', '>=', $this->period->start_date);
                    $q3->where('licenses.end', '<=', $this->period->end_date);
                });
                $q->orWhere(function ($q4) {
                    // license starts later than current period start and ends later then current period end
                    $q4->where('licenses.start', '>=', $this->period->start_date);
                    $q4->where('licenses.start', '<=', $this->period->end_date);
                    $q4->where('licenses.end', '>=', $this->period->end_date);
                });

            });
            return (int)$builder->sum('licenses.amount');
        }
        throw new \Exception('Nr of licenses should not be called for a user');
    }

    public function nrUploadedClassFiles($days)
    {
        return $this->nrFileManagementByStatusIdTypeAndDays(1, 'classupload', $days);
    }


    /*
     * file management logs not correctly saved
     */
    public function nrUploadedTestFiles($days)
    {
        return $this->nrFileManagementByStatusIdTypeAndDays(1, 'testupload', $days);
    }

    protected function nrFileManagementByStatusIdTypeAndDays($statusId, $type, $days)
    {
        $builder = \DB::table('file_managements')
            ->where('file_managements.type', $type)
            ->whereNull('parent_id');

        $this->attachReference($builder, 'file_managements');

        $this->addDaysConstraintToBuilder($builder, $days, 'Y-m-d H:i:s', 'file_managements.created_at');

        return $builder->count();
    }

    public function nrAddedQuestionItems($days, $returnBuilder = false)
    {

        if ($this->type === self::USER) {
            $builder = \DB::table('question_authors')
                ->where('user_id', $this->reference->getKey());
        } else {
            $builder = \DB::table('question_authors')
                ->leftJoin('users', function ($join) {
                    $join->on('question_authors.user_id', '=', 'users.id');
                })
                ->whereNull('users.deleted_at')
                ->where('users.demo', 0)
                ->whereNull('question_authors.deleted_at');
            $this->attachReference($builder, 'users');
        }
        $builder->whereNull('question_authors.deleted_at')
            ->leftJoin('questions', 'questions.id', 'question_authors.question_id')
            ->whereNull('questions.deleted_at')
            ->select('question_authors.question_id')
            ->distinct('question_authors.question_id');
        $this->addDaysConstraintToBuilder($builder, $days, 'Y-m-d H:i:s', 'questions.created_at');

        if ($returnBuilder) {
            return $builder;
        }
        return $builder->count();
    }

//    public static function nrApprovedClasses($location, $days)
//    {
//
//        $builder = SchoolClass::where('school_location_id', $location->getKey());
//
//        if ($days != 0) {
//
//            $end_date = Carbon::now()->toDateTimeString();
//            $start_date = Carbon::now()->subDays($days);
//
//            $builder->whereBetween('created_at', [$start_date, $end_date]);
//        }
//
//        return $builder->count();
//    }

    public function nrTestsTaken($days)
    {
        return $this->nrTestTakesByStatusIdAndDays(6, $days);
    }

    public function nrTestsRated($days)
    {
        return $this->nrTestTakesByStatusIdAndDays(9, $days);
    }

    public function nrColearningSessions($days)
    {
        return $this->nrTestTakesByStatusIdAndDays(7, $days);
    }

    public function getSSOType(SchoolLocation $schoolLocation)
    {
        return $schoolLocation->sso_type;
    }

    public function getCustomerCode(SchoolLocation $schoolLocation)
    {
        return $schoolLocation->customer_code;
    }

    public function getLVSType(SchoolLocation $schoolLocation)
    {
        return $schoolLocation->lvs_type;
    }

    public function getSSOActive(SchoolLocation $schoolLocation)
    {
        return ($schoolLocation->sso_active === true) ? "true" : "false";
    }

    public function getIntense(SchoolLocation $schoolLocation)
    {
        return ($schoolLocation->intense === true) ? "true" : "false";
    }

    public function getLVSActiveNoMailAllowed(SchoolLocation $schoolLocation)
    {
        return ($schoolLocation->lvs_active_no_mail_allowed === true) ? "true" : "false";
    }

    public function getAllowInbrowserTesting(SchoolLocation $schoolLocation)
    {
        return ($schoolLocation->allow_inbrowser_testing === true) ? "true" : "false";
    }

    public function getLVSActive(SchoolLocation $schoolLocation)
    {
        return ($schoolLocation->lvs_active === true) ? "true" : "false";
    }

    public function nrParticipantsTakenTest($days)
    {

        $builder = \DB::table('test_participants')
            ->leftJoin('test_takes', 'test_takes.id', 'test_participants.test_take_id')
            ->where('test_takes.demo', 0)
            ->whereNull('test_takes.deleted_at')
            ->whereNull('test_participants.deleted_at')
            ->leftJoin('users', 'users.id', 'test_participants.user_id')
            ->whereNull('users.deleted_at')
            ->where('users.demo', 0);

        $this->attachReference($builder, 'users');

        $this->addDaysConstraintToBuilder($builder, $days, 'Y-m-d 00:00:00', 'test_takes.time_start');

        return $builder->count();
    }

    public function dateGeneralTermsAccepted()
    {
        return GeneralTermsLog::whereUserId($this->reference->getKey())->value('accepted_at');
    }

    public function nrUniqueStudentsTakenTest($days, $returnBuilder = false)
    {

        $builder = \DB::table('test_participants')
            ->select('test_participants.user_id')
            ->distinct('test_participants.user_id')
            ->leftJoin('test_takes', 'test_takes.id', 'test_participants.test_take_id')
            ->whereNull('test_takes.deleted_at')
            ->whereNull('test_participants.deleted_at')
            ->where('test_takes.demo', 0)
            ->where('test_participants.test_take_status_id', '>', 2)
            ->leftJoin('users', 'users.id', 'test_participants.user_id')
            ->where('users.demo', 0);

        $this->attachReference($builder, 'users');

        $this->addDaysConstraintToBuilder($builder, $days, 'Y-m-d 00:00:00', 'test_takes.time_start');

        if ($returnBuilder) {
            return $builder;
        }
        return $builder->count();
    }

    public function nrTestTakesByStatusIdAndDays($statusId, $days, $returnBuilder = false)
    {
        $builder = TestTakeStatusLog::leftJoin('test_takes', 'test_takes.id', 'test_take_status_logs.test_take_id')
            ->whereNotNull('test_take_status_logs.test_take_status_id')
            ->where('test_take_status_logs.test_take_status_id', $statusId)
            ->whereNull('test_takes.deleted_at')
            ->where('test_takes.demo', 0);


        $this->attachReference($builder, 'test_takes');

        $this->addDaysConstraintToBuilder($builder, $days);

        if ($returnBuilder) {
            return $builder;
        }
        return $builder->count();
    }

    protected function addDaysConstraintToBuilder($builder, $days, $format = 'Y-m-d H:i:s', $column = 'test_take_status_logs.created_at')
    {
        if ($days > 0) {
            $end_date = Carbon::now()->format($format);
            $start_date = Carbon::now()->startOfDay()->subDays((int)$days)->format($format);
            $builder->whereBetween($column, [$start_date, $end_date]);
        }
    }

    protected function addPeriodConstraintToBuilder($builder, $format = 'Y-m-d H:i:s', $column = 'test_take_status_logs.created_at')
    {
        if ($this->hasCurrentPeriod()) {
            $end_date = Carbon::createFromFormat('Y-m-d', $this->period->end_date)->format($format);
            $start_date = Carbon::createFromFormat('Y-m-d', $this->period->start_date)->format($format);
            $builder->whereBetween($column, [$start_date, $end_date]);
        }
    }

    /**
     * active in this case is:
     * in last 60 days
     * at least 3 tests taken (so test_take_status_logs on status 3
     */
    public function nrActiveTeachers($minimalNrTestTakes, $days = 0)
    {
        if (!$this->hasCurrentPeriod()) {
            return -1;
        }

//        $count = $this->schoolLocation->users()->whereIn('id', function ($query) {
//            $userRole = new UserRole();
//            $query->select('user_id')->from($userRole->getTable())->whereIn('role_id', function ($query) {
//                $role = new Role();
//                $query->select($role->getKeyName())->from($role->getTable())->where('name', 'Teacher')->whereNull('deleted_at');
//            })->whereNull('deleted_at');
//        })->where('count_last_test_taken', '>=', $date->format('Y-m-d H:i:s'))->count();
        $builder2 = TestTake::withoutGlobalScope(new ArchivedScope)->groupBy('test_takes.user_id')->havingRaw('COUNT(test_takes.user_id) > ' . (int)$minimalNrTestTakes)->select('test_takes.user_id');
        $this->addDaysConstraintToBuilder($builder2, $days, 'Y-m-d 00:00:00', 'test_takes.time_start');
        $builder = User::where('school_location_id', $this->reference->getKey())
            ->join('user_roles', 'user_roles.user_id', 'users.id')
            ->where('user_roles.role_id', 1) // teacher
            ->whereNull('user_roles.deleted_at')
            ->whereIn('users.id', $builder2);

        return $builder->count();
    }

    /**
     * limit on students
     * remove @test-correct.nl domain users
     * first time login in this school year
     */
    public function nrActivatedAccounts($days)
    {

        if (!$this->hasCurrentPeriod()) {
            return -1;
        }

        $builder = \DB::table('login_logs')
            ->distinct('login_logs.user_id')
            ->leftJoin('users', 'users.id', 'login_logs.user_id')
            ->whereNull('users.deleted_at')
            ->join('user_roles', 'user_roles.user_id', 'users.id')
            ->where('user_roles.role_id', 3) // student
            ->whereNull('user_roles.deleted_at');

        $this->attachReference($builder, 'users');

        $builder2 = \DB::table('login_logs')
            ->distinct('login_logs.user_id')
            ->leftJoin('users', 'users.id', 'login_logs.user_id')
            ->whereNull('users.deleted_at')
            ->join('user_roles', 'user_roles.user_id', 'users.id')
            ->where('user_roles.role_id', 3) // student
            ->whereNull('user_roles.deleted_at')
            ->select('login_logs.user_id');

        $this->attachReference($builder, 'users');

        $format = 'Y-m-d H:i:s';
        $column = 'login_logs.created_at';

        if ($days > 0) {
            $start_date = Carbon::createFromFormat('Y-m-d', $this->period->start_date)->format($format);
            $end_date = Carbon::now()->subDays((int)$days)->format($format);
            $builder2->whereBetween($column, [$start_date, $end_date]);
            $builder->whereNotIn('login_logs.user_id', $builder2);
        }

        $this->addDaysConstraintToBuilder($builder, $days, $format, 'login_logs.created_at');
        $this->addPeriodConstraintToBuilder($builder, $format, 'login_logs.created_at');

        return $builder->count();
    }

    public function dateTrialPeriodEnds()
    {
        if($trialPeriod = $this->reference->trialPeriodsWithSchoolLocationCheck) {
            return $trialPeriod->trial_until;
        }
        return null;
    }
}