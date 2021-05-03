<?php

namespace tcCore\Rules;

use Illuminate\Contracts\Validation\Rule;
use tcCore\SchoolClass;
use tcCore\Scopes\TeacherSchoolLocationScope;
use tcCore\Teacher;
use tcCore\User;

class TeacherWithSchoolClassShouldNotExist implements Rule
{
    private $schoolLocationId;
    private $userId = false;
    private $externalId;
    private $schoolClass;
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
        $schoolClass = $this->getSchoolClass();
        if ($schoolClass === null) {
            return true;
        }
        $teacher = Teacher::withoutGlobalScope(TeacherSchoolLocationScope::class)->where('user_id',$this->userId)->where('class_id',$schoolClass->id)->first();
        if(is_null($row)){
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
        return 'The validation error message.';
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
        }
        $this->external_id = $vars['external_id'];
    }

    private function setSchoolClass($vars)
    {
        if(!array_key_exists('school_class',$vars)){
            $this->school_class = false;
        }
        $this->school_class = $vars['school_class'];
    }

    private function getSchoolClassByName($school_class_name) {
        return SchoolClass::filtered()->orderBy('created_at', 'desc')->get()->first(function ($school_class) use ($school_class_name) {
            return strtolower($school_class_name) === strtolower($school_class->name);
        });
    }
}
