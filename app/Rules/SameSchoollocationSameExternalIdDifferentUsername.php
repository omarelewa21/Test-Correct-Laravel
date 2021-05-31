<?php

namespace tcCore\Rules;

use Illuminate\Contracts\Validation\Rule;
use tcCore\User;

class SameSchoollocationSameExternalIdDifferentUsername implements Rule
{

    private $schoolLocationId;
    private $userId = false;
    private $externalId;
    private $attribute;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($schoolLocationId,$username,$externalId)
    {
        $this->schoolLocationId = $schoolLocationId;
        $this->externalId = $externalId;
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
        $this->attribute = $attribute;
        $row = \DB::table('school_location_user')  ->where('school_location_id', $this->schoolLocationId)
            ->where('external_id', $this->externalId)
            ->where('user_id','!=',$this->userId)
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
        return $this->attribute.' failed on double entry';
    }

    private function setUserId($username)
    {
        $user = User::where('username',$username)->first();
        if(!is_null($user)){
            $this->userId = $user->id;
        }
    }
}
