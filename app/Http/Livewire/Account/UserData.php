<?php

namespace tcCore\Http\Livewire\Account;

use Illuminate\Support\Str;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Requests\Request;

class UserData implements \Livewire\Wireable
{
    public string $username;
    public string $uuid;
    public string $name_first;
    public string $name;
    public ?string $gender;
    public ?string $gender_different;
    public ?string $name_suffix = null;

    public static $rules = [
        'userData.username'         => 'required|email',
        'userData.name'             => 'required|string|regex:/^[\pL\s\-]+$/u',
        'userData.name_first'       => 'required|string|regex:/^[\pL\s\-]+$/u',
        'userData.name_suffix'      => 'sometimes|regex:/^[\pL\s\-]+$/u',
        'userData.gender'           => 'sometimes|string|in:Male,Female,Other',
        'userData.gender_different' => 'sometimes|string|regex:/^[\pL\s\-]+$/u',
    ];

    public function __construct(array $userData)
    {
        foreach ($userData as $property => $value) {
            $this->$property = $value;
        }
    }

    public function toLivewire()
    {
        foreach ($this as $property => $value) {
            Request::filter($value);
            $value = BaseHelper::returnOnlyRegularAlphaNumeric($value,'@\.');
            $this->$property = is_string($value) ? html_entity_decode($value) : $value;
        }
        return get_object_vars($this);
    }

    public static function fromLivewire($value)
    {
        Request::filter($value);
        $value = BaseHelper::returnOnlyRegularAlphaNumeric($value,'@\.');
        return new static($value);
    }
}