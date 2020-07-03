<?php namespace tcCore\Lib\User;

use tcCore\User;
use Illuminate\Support\Str;

class Factory {

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function generate($data)
    {
        $this->user = new User();

        if(isset($data['password'])){
            $data['password'] = \Hash::make($data['password']);
        }

        $this->user->fill($data);

        $this->user->setAttribute('api_key', Str::random(40));

        if($this->user->save()){
            return $this->user;
        } else {
            return false;
        }
    }

    public function generateNewPassword() {
        $password = Str::random(8);
        $this->user->setAttribute('password', \Hash::make($password));
        $this->user->save();
        return $password;
    }
}