<?php


namespace tcCore\Http\Helpers;


use Carbon\Carbon;
use tcCore\FileManagement;
use tcCore\QuestionAuthor;
use tcCore\SchoolLocation;
use tcCore\TestTake;
use tcCore\User;

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
        if(!in_array(get_class($reference), $allowedClasses)){
            throw new \Exception('Report does not accept a '.get_class($reference).' model');
        }
        $this->reference = $reference;
        $this->type = (get_class($reference) === User::class) ? self::USER : self::SCHOOLLOCATION;
    }

    protected function attachReference($builder, $table)
    {
        $tableColumn = sprintf('%s.%s',$table,$this->getColumn());
        return $builder->where($tableColumn,$this->reference->getKey());
    }

    protected function getColumn()
    {
        if($this->type === self::USER) {
            return 'user_id';
        } else if($this->type === self::SCHOOLLOCATION){
            return 'school_location_id';
        } else {
            throw new \Exception('Unknown model '.get_class($this->reference));
        }
    }

    public function getActiveLicenses()
    {
        if($this->type === self::SCHOOLLOCATION) {
            $date = Carbon::now();
            return $this->reference->licenses()
                ->where('start', '<=', $date->format('Y-m-d'))
                ->where(function ($query) use ($date) {
                    $query->whereNull('end')
                        ->orWhere('end', '>=', $date->format('Y-m-d'));
                })
                ->sum('amount');
        }
    }

    public function nrApprovedClassFiles($days)
    {
        return $this->nrFileManagementByStatusIdTypeAndDays(7,'classupload',$days);
    }


    public function nrApprovedTestFiles($days)
    {
        return $this->nrFileManagementByStatusIdTypeAndDays(7,'testupload',$days);
    }

    protected function nrFileManagementByStatusIdTypeAndDays($statusId,$type,$days)
    {
        $builder = FileManagement::leftJoin('file_management_status_logs','file_management_status_logs.file_management_id','file_managements.id')
            ->where('file_managements.type',$type)
            ->where('file_management_status_logs.file_management_status_id', $statusId);

        $this->attachReference($builder, 'file_managements');

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);

            $builder->whereBetween('file_management_status_logs.created_at', [$start_date, $end_date]);
        }

        return $builder->count();
    }

    public function nrAddedQuestionItems($days)
    {

        if($this->type === self::USER) {
            $builder = QuestionAuthor::where('user_id',$this->reference->getKey());
        } else {
            $builder = QuestionAuthor::leftJoin('users', function ($join) {
                $join->on('question_authors.user_id', '=', 'users.id');
            });
            $this->attachReference($builder, 'users');
        }

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);

            $builder->whereBetween('question_authors.created_at', [$start_date, $end_date]);
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

    /**
     * there should nothing be left to check (in the frontend the normeren button should show)
     * participants => answer => needs rating
     */
    public function nrTestsChecked($days)
    {

        return $this->nrTestTakesByStatusIdAndDays(8,$days);
    }

    public function nrTestsRated($days)
    {
        return $this->nrTestTakesByStatusIdAndDays(9,$days);
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
            ->leftJoin('test_takes','test_takes.id','test_participants.test_take_id')
            ->whereNull('test_takes.deleted_at')
            ->whereNull('test_participants.deleted_at');

        $this->attachReference($builder,'test_takes');

        if ($days != 0) {

            $end_date = Carbon::now()->format('Y-m-d 00:00:00');
            $start_date = Carbon::now()->subDays($days)->format('Y-m-d 00:00:00');

            $builder->whereBetween('test_takes.time_start', [$start_date, $end_date]);
        }

        return $builder->count();
    }

    public function nrTestTakesByStatusIdAndDays($statusId, $days)
    {

        $builder = TestTake::leftJoin('test_take_status_logs','test_take_status_logs.test_take_id','test_takes.id')
            ->where('test_take_status_logs.test_take_status_id', $statusId);

        $this->attachReference($builder,'test_takes');

        if ($days != 0) {

            $end_date = Carbon::now()->toDateTimeString();
            $start_date = Carbon::now()->subDays($days);

            $builder->whereBetween('test_take_status_logs.created_at', [$start_date, $end_date]);
        }

        return $builder->count();
    }
}