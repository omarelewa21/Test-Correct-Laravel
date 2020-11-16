<?php

namespace tcCore\App\Http\Livewire;

use Livewire\Component;
use tcCore\DemoTeacherRegistration;

class Onboarding extends Component
{
    public $registration;

    public $step = 1;

    public $password;

    public $password_confirmation;

    public function rules()
    {
        if ($this->step === 1) {
            return [
                'registration.gender' => 'required|in:male,female,different',
                'registration.gender_different' => 'sometimes',
                'registration.name_first' => 'required|string',
                'registration.name' => 'required|string',
                'password' => 'required|min:8|same:password_confirmation',
            ];
        }
        if ($this->step === 2) {
            return [
                'registration.school_location' => 'required',
                'registration.website_url' => 'required',
                'registration.address' => 'required',
                'registration.city' => 'required',
            ];
        }
    }

    public function mount()
    {
        $this->registration = new DemoTeacherRegistration;

    }

    public function backToStepOne()
    {
        $this->step = 1;
    }

    public function render()
    {
        return view('livewire.onboarding');
    }

    public function getMinCharRuleProperty()
    {
        return strlen($this->password) < 8 ? 'red' : 'green';
    }

    public function getMinDigitRuleProperty()
    {
        return preg_match('/\d/', $this->password) ? 'green' : 'red';
    }

    public function getSpecialCharRuleProperty()
    {
        return preg_match('/[^a-zA-Z\d]/', $this->password) ? 'green' : 'red';
    }

    public function step1()
    {
        $this->validate();
        $this->step = 2;
    }

    public function step2()
    {
        $this->validate();

    }


}
