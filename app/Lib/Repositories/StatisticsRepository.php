<?php namespace tcCore\Lib\Repositories;

use Carbon\Traits\Creator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use tcCore\Answer;
use tcCore\Jobs\CountSchoolActiveTeachers;
use tcCore\Jobs\CountSchoolLocationActiveTeachers;
use tcCore\Jobs\CountSchoolLocationQuestions;
use tcCore\Jobs\CountSchoolLocationStudents;
use tcCore\Jobs\CountSchoolLocationTeachers;
use tcCore\Jobs\CountSchoolLocationTests;
use tcCore\Jobs\CountSchoolLocationTestsTaken;
use tcCore\Jobs\CountSchoolQuestions;
use tcCore\Jobs\CountSchoolStudents;
use tcCore\Jobs\CountSchoolTeachers;
use tcCore\Jobs\CountSchoolTests;
use tcCore\Jobs\CountSchoolTestsTaken;
use tcCore\Lib\User\Roles;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\TestParticipant;
use tcCore\TestTakeStatus;
use tcCore\User;

class StatisticsRepository {
    public static function runBasedOnUser(User $user) {
        $user->load('roles');
        $roles = Roles::getUserRoles($user);
        $school = $user->school;
        $schoolLocation = $user->schoolLocation;

        if ($user->getAttribute('school_id') !== $user->getOriginal('school_id')) {
            $prevSchool = School::find($user->getOriginal('school_id'));
        } else {
            $prevSchool = null;
        }

        if ($user->getAttribute('school_location_id') !== $user->getOriginal('school_location_id')) {
            #MF 2020-11-17 changed School::find to SchoolLocation::find because of errors in jobs;
            $prevSchoolLocation = SchoolLocation::find($user->getOriginal('school_location_id'));
        } else {
            $prevSchoolLocation = null;
        }
        if (in_array('Student', $roles)) {
            if ($school !== null) {
                self::runForSchoolAndRole($school,'student',$user);
            }

            if ($schoolLocation !== null) {
                self::runForSchoolLocationAndRole($schoolLocation,'student',$user);
            }

            if ($prevSchool !== null) {
                self::runForSchoolAndRole($prevSchool,'student',$user);
            }

            if ($prevSchoolLocation !== null) {
                self::runForSchoolLocationAndRole($prevSchoolLocation,'student',$user);
            }
        }

        if (in_array('Teacher', $roles)) {
            if ($school !== null) {
                self::runForSchoolAndRole($school,'teacher',$user);
            }

            if ($schoolLocation !== null) {
                self::runForSchoolLocationAndRole($schoolLocation, 'teacher',$user);
            }

            if ($prevSchool !== null) {
                self::runForSchoolAndRole($prevSchool,'teacher',$user);
            }

            if ($prevSchoolLocation !== null) {
                self::runForSchoolLocationAndRole($prevSchoolLocation, 'teacher',$user);
            }
        }
    }

    public static function runForSchoolLocationAndRole(SchoolLocation $schoolLocation, $role, $user = null)
    {
        if(strtolower($role) === 'student'){
            if($user){
                $user->addJobUnique(new CountSchoolLocationStudents($schoolLocation));
            } else {
                Queue::push(new CountSchoolLocationStudents($schoolLocation));
            }
        }
        else if(strtolower($role) === 'teacher'){
            if($user){
                $user->addJobUnique(new CountSchoolLocationTeachers($schoolLocation));
                $user->addJobUnique(new CountSchoolLocationActiveTeachers($schoolLocation));
                $user->addJobUnique(new CountSchoolLocationQuestions($schoolLocation));
                $user->addJobUnique(new CountSchoolLocationTests($schoolLocation));
                $user->addJobUnique(new CountSchoolLocationTestsTaken($schoolLocation));
            } else {
                Queue::push(new CountSchoolLocationTeachers($schoolLocation));
                Queue::push(new CountSchoolLocationActiveTeachers($schoolLocation));
                Queue::push(new CountSchoolLocationQuestions($schoolLocation));
                Queue::push(new CountSchoolLocationTests($schoolLocation));
                Queue::push(new CountSchoolLocationTestsTaken($schoolLocation));
            }
        }
    }

    public static function runForSchoolAndRole(School $school, $role, $user = null)
    {
        if(strtolower($role) === 'student'){
            if($user){
                $user->addJobUnique(new CountSchoolStudents($school));
            } else {
                Queue::push(new CountSchoolStudents($school));
            }
        }
        else if(strtolower($role) === 'teacher'){
            if($user){
                $user->addJobUnique(new CountSchoolTeachers($school));
                $user->addJobUnique(new CountSchoolActiveTeachers($school));
                $user->addJobUnique(new CountSchoolQuestions($school));
                $user->addJobUnique(new CountSchoolTests($school));
                $user->addJobUnique(new CountSchoolTestsTaken($school));
            } else {
                Queue::push(new CountSchoolTeachers($school));
                Queue::push(new CountSchoolActiveTeachers($school));
                Queue::push(new CountSchoolQuestions($school));
                Queue::push(new CountSchoolTests($school));
                Queue::push(new CountSchoolTestsTaken($school));
            }
        }
    }
}