<?php

namespace tcCore\Rules;

use Illuminate\Contracts\Validation\Rule;
use tcCore\SchoolClass;
use tcCore\Scopes\TeacherSchoolLocationScope;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\User;

class TeacherWithSchoolClassAndSubjectShouldNotExist implements Rule
{
    private $schoolLocationId;
    private $userId = false;
    private $external_id;
    private $school_class;
    private $subject;
    private $attribute;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($schoolLocationId,$vars)
    {
        $this->schoolLocationId = $schoolLocationId;
        $this->setExternalId($vars);
        $this->setUserId($vars);
        $this->setSchoolClass($vars);
        $this->setSubject($vars);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        $schoolClass = $this->getSchoolClassByName();
        if ($schoolClass === null) {
            return true;
        }
        $subject = $this->getSubjectByName();
        $teacher = Teacher::where('user_id',$this->userId)
                            ->where('class_id',$schoolClass->id)
                            ->where('subject_id',$subject->id)
                            ->first();
        if(is_null($teacher)){
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->attribute.' failed on double schoolclass subject entry';
    }

    private function setUserId($vars)
    {
        if(!array_key_exists('username',$vars)){
            $this->userId = false;
        }
        $user = User::where('username',$vars['username'])->first();
        if(!is_null($user)){
            $this->userId = $user->id;
        }
    }

    private function setExternalId($vars)
    {
        if(!array_key_exists('external_id',$vars)){
            $this->external_id = false;
            return;
        }
        $this->external_id = $vars['external_id'];
    }

    private function setSchoolClass($vars)
    {
        if(!array_key_exists('school_class',$vars)){
            $this->school_class = false;
            return;
        }
        $this->school_class = $vars['school_class'];
    }

    private function setSubject($vars)
    {
        if(!array_key_exists('subject',$vars)){
            $this->subject = false;
            return;
        }
        $this->subject = $vars['subject'];
    }

    private function getSchoolClassByName()
    {
        $school_class_name = $this->school_class;
        return SchoolClass::filtered()->orderBy('created_at', 'desc')->get()->first(function ($school_class) use ($school_class_name) {
            return strtolower($school_class_name) === strtolower($school_class->name);
        });
    }


    private function getSubjectByName() {
        $subject_name = $this->subject;
        return Subject::filtered()->get()->first(function ($subject) use ($subject_name) {
            return strtolower($subject_name) === strtolower($subject->name);
        });
    }

}
