<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;


use Carbon\Carbon;
use tcCore\Answer;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\User\Factory;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationEducationLevel;
use tcCore\SchoolLocationSection;
use tcCore\SchoolYear;
use tcCore\Section;
use tcCore\Student;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\Text2speech;
use tcCore\Text2speechLog;
use tcCore\User;

class UserHelper
{

    public function createUserFromData($data){
        $userFactory = new Factory(new User());

        $user = $userFactory->generate($data,true);

        if($user->invited_by != null){
            if(!$user->emailDomainInviterAndInviteeAreEqual()) {
                $schoolLocationId = SchoolHelper::getTempTeachersSchoolLocation()->getKey();
                ActingAsHelper::getInstance()->setUser(SchoolHelper::getSomeTeacherBySchoolLocationId($schoolLocationId));
                $user->school_location_id = $schoolLocationId;
            }
        }

        if ($user->save() !== false) {
            if (isset($data['send_welcome_mail']) && $data['send_welcome_mail'] == true) {
                dispatch_now(new SendWelcomeMail($user->getKey(), isset($data['url']) ? $data['url'] : false));
            }
            return $user;
        } else {
            return false;
        }
    }
}