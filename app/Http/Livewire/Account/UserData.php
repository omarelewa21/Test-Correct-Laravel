<?php

namespace tcCore\Http\Livewire\Account;

class UserData implements \Livewire\Wireable
{
    public readonly string $username;
    public readonly string $uuid;
    public string $name_first;
    public string $name;
    public ?string $gender;
    public ?string $gender_different;
    public ?string $name_suffix = null;

    public static $rules = [
        'userData.username'         => 'required|email',
        'userData.name'             => 'required|string',
        'userData.name_first'       => 'required|string',
        'userData.name_suffix'      => 'sometimes',
        'userData.gender'           => 'sometimes|string|in:Male,Female,Other',
        'userData.gender_different' => 'sometimes|string',
    ];

    public function __construct(array $userData)
    {
        foreach ($userData as $property => $value) {
            $this->$property = $value;
        }
    }

    public function toLivewire()
    {
        return get_object_vars($this);
    }

    public static function fromLivewire($value)
    {
        return new static($value);
    }
}