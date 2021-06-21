<?php

namespace tcCore\Rules;

use Illuminate\Contracts\Validation\Rule;

class SchoolLocationUserExternalIdUpdate implements Rule
{
    protected $schoolLocationId;
    protected $user;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($schoolLocationId,$user)
    {
        $this->schoolLocationId = $schoolLocationId;
        $this->user = $user;
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
        $row = \DB::table('school_location_user')  ->where('school_location_id', $this->schoolLocationId)
            ->where('external_id', $value)
            ->where('user_id','!=',$this->user->id)
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
        return 'The validation error message.';
    }
}
