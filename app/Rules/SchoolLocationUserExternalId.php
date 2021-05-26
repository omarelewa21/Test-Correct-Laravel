<?php

namespace tcCore\Rules;

use Illuminate\Contracts\Validation\Rule;
use tcCore\User;

class SchoolLocationUserExternalId implements Rule
{
    private $schoolLocationId;
    private $userId = false;
    private $type = 'external_id';
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($schoolLocationId,$username)
    {
        $this->schoolLocationId = $schoolLocationId;
        $this->setUserId($username);
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
        $this->attribute  = $attribute;
//        if($this->userId){
//            $row = \DB::table('school_location_user')  ->where('school_location_id', $this->schoolLocationId)
//                ->where('user_id', $this->userId)
//                ->first();
//            if(!is_null($row)){
//                $this->type = 'school_location';
//                return false;
//            }
//        }
        $row = \DB::table('school_location_user')  ->where('school_location_id', $this->schoolLocationId)
                                            ->where('external_id', $value)
                                            ->first();
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
        //if($this->type == 'external_id'){
            return $this->attribute.' failed on double entry';
//        }
//        return $this->attribute.' failed on double user entry';
    }

    private function setUserId($username)
    {
        $user = User::where('username',$username)->first();
        if(!is_null($user)){
            $this->userId = $user->id;
        }
    }
}
