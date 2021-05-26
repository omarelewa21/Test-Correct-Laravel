<?php


namespace tcCore\Http\Helpers;


use Carbon\Carbon;
use tcCore\FileManagement;
use tcCore\QuestionAuthor;
use tcCore\Role;
use tcCore\SchoolLocation;
use tcCore\TestTake;
use tcCore\User;
use tcCore\UserRole;

class ReportHelper
{

    protected $reference;
    protected $type;

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

    public function nrLicenses($days)
    {
        if ($this->type === self::SCHOOLLOCATION) {
            $builder = \DB::table('license_logs')
                            ->leftJoin('licenses','licenses.id','license_logs.license_id');
            $this->attachReference($builder,'licenses');
            $this->addDaysConstraintToBuilder($builder,$days,'Y-m-d H:i:s','license_logs.created_at');
            return (int) $builder->sum('license_logs.amount');
        }
        throw new \Exception('Nr of licenses should not be called for a user');
    }

    public function nrApprovedClassFiles($days)
    {
        return $this->nrFileManagementByStatusIdTypeAndDays(7, 'classupload', $days);
    }


    public function nrApprovedTestFiles($days)
    {
        return $this->nrFileManagementByStatusIdTypeAndDays(7, 'testupload', $days);
    }

    protected function nrFileManagementByStatusIdTypeAndDays($statusId, $type, $days)
    {
        $builder = FileManagement::leftJoin('file_management_status_logs', 'file_management_status_logs.file_management_id', 'file_managements.id')
            ->where('file_managements.type', $type)
            ->where('file_management_status_logs.file_management_status_id', $statusId);

        $this->attachReference($builder, 'file_managements');

        $this->addDaysConstraintToBuilder($builder, $days,'Y-m-d H:i:s','file_management_status_logs.created_at');

        return $builder->count();
    }

    public function nrAddedQuestionItems($days)
    {

        if ($this->type === self::USER) {
            $builder = QuestionAuthor::where('user_id', $this->reference->getKey());
        } else {
            $builder = QuestionAuthor::leftJoin('users', function ($join) {
                $join->on('question_authors.user_id', '=', 'users.id');
            });
            $this->attachReference($builder, 'users');
        }

        $this->addDaysConstraintToBuilder($builder, $days,'Y-m-d H:i:s','question_authors.created_at');

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

    /**
     * there should nothing be left to check (in the frontend the normeren button should show)
     * participants => answer => needs rating
     */
    public function nrTestsChecked($days)
    {

        return $this->nrTestTakesByStatusIdAndDays(8, $days);
    }

    public function nrTestsRated($days)
    {
        return $this->nrTestTakesByStatusIdAndDays(9, $days);
    }

    public function nrColearningSessions($days)
    {
        return $this->nrTestTakesByStatusIdAndDays(7, $days);
    }

    public function nrUniqueStudentsTakenTest($days)
    {

        $builder = \DB::table('test_participants')
            ->select('test_participants.user_id')
            ->distinct('test_participants.user_id')
            ->leftJoin('test_takes', 'test_takes.id', 'test_participants.test_take_id')
            ->whereNull('test_takes.deleted_at')
            ->whereNull('test_participants.deleted_at');

        $this->attachReference($builder, 'test_takes');

        $this->addDaysConstraintToBuilder($builder, $days, 'Y-m-d 00:00:00','test_takes.time_start');

        return $builder->count();
    }

    public function nrTestTakesByStatusIdAndDays($statusId, $days)
    {

        $builder = TestTake::leftJoin('test_take_status_logs', 'test_take_status_logs.test_take_id', 'test_takes.id')
            ->where('test_take_status_logs.test_take_status_id', $statusId);

        $this->attachReference($builder, 'test_takes');

        $this->addDaysConstraintToBuilder($builder, $days);

        return $builder->count();
    }

    protected function addDaysConstraintToBuilder($builder, $days, $format = 'Y-m-d H:i:s', $column = 'test_take_status_logs.created_at')
    {
        if ($days > 0) {
            $end_date = Carbon::now()->format($format);
            $start_date = Carbon::now()->subDays((int) $days)->format($format);
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

//        $count = $this->schoolLocation->users()->whereIn('id', function ($query) {
//            $userRole = new UserRole();
//            $query->select('user_id')->from($userRole->getTable())->whereIn('role_id', function ($query) {
//                $role = new Role();
//                $query->select($role->getKeyName())->from($role->getTable())->where('name', 'Teacher')->whereNull('deleted_at');
//            })->whereNull('deleted_at');
//        })->where('count_last_test_taken', '>=', $date->format('Y-m-d H:i:s'))->count();
        $builder2 = TestTake::groupBy('test_takes.user_id')->havingRaw('COUNT(test_takes.user_id) > '.(int) $minimalNrTestTakes)->select('test_takes.user_id');
        $this->addDaysConstraintToBuilder($builder2, $days, 'Y-m-d 00:00:00','test_takes.time_start');
        $builder = User::where('school_location_id', $this->reference->getKey())
            ->join('user_roles', 'user_roles.user_id', 'users.id')
            ->where('user_roles.role_id', 1) // teacher
            ->whereNull('user_roles.deleted_at')
            ->whereIn('users.id', $builder2);

        return $builder->count();
    }

    public function nrActivatedAccounts($days)
    {

        $builder = \DB::table('login_logs')
            ->distinct('login_logs.user_id')
            ->leftJoin('users', 'users.id', 'login_logs.user_id')
            ->whereNull('users.deleted_at');

        $this->attachReference($builder, 'users');

        $this->addDaysConstraintToBuilder($builder, $days,'Y-m-d H:i:s','login_logs.created_at');

        return $builder->count();
    }
}