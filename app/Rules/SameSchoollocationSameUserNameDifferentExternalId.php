<?php

namespace tcCore\Rules;

use Illuminate\Contracts\Validation\Rule;
use tcCore\User;

class SameSchoollocationSameUserNameDifferentExternalId implements Rule
{
    private $schoolLocationId;
    private $userId = false;
    private $attribute;
    private $ignoreEmpty;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($schoolLocationId,$username,$ignoreEmpty = false)
    {
        $this->schoolLocationId = $schoolLocationId;
        $this->setUserId($username);
        $this->ignoreEmpty = $ignoreEmpty;
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
        $builder = \DB::table('school_location_user')->where('school_location_id', $this->schoolLocationId)
            ->where('user_id','=',$this->userId)
            ->where('external_id','!=', $value);
        if(!$this->ignoreEmpty){
            $builder = $builder->whereNotNull('external_id')
                                ->where('external_id','!=', '');
        }
        $row = $builder->first();

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
        return $this->attribute.' failed on same username same schoollocation different external_id';
    }

    private function setUserId($username)
    {
        $user = User::where('username',$username)->first();
        if(!is_null($user)){
            $this->userId = $user->id;
        }
    }
}
