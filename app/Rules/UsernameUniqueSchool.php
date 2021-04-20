<?php

namespace tcCore\Rules;

use Illuminate\Contracts\Validation\Rule;
use tcCore\SchoolLocation;
use tcCore\User;

class UsernameUniqueSchool implements Rule
{
    public $schoolLocationId;
    public $type;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($schoolLocationId,$type)
    {
        $this->schoolLocationId = $schoolLocationId;
        $this->type = $type;
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
        if($this->type=='teacher'){
            $schoolLocation = SchoolLocation::find($this->schoolLocationId);
            if(is_null($schoolLocation)){
                throw new \Exception('schoolLocation not set');
            }
            $school = $schoolLocation->school;
            $schoolLocations = $school->schoolLocations()->pluck('id')->toArray();
            $user = User::where('username',$value)->whereNotIn('school_location_id',$schoolLocations )->first();
            if(!is_null($user)){
                return false;
            }
        }
        $user = User::where('username',$value)->first();
        if(!is_null($user)){
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'has already been taken';
    }
}
