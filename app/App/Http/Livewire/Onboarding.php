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

    public $btnDisabled = true;

    public function rules()
    {
        if ($this->step === 1) {
            return [
                'registration.gender' => 'required|in:male,female,different',
                'registration.gender_different' => 'sometimes',
                'registration.name_first' => 'required|string',
                'registration.email' => 'required|email',
                'registration.name' => 'required|string',
                'registration.name_suffix' => 'sometimes',
                'password' => 'required|min:8|regex:/\d/|regex:/[^a-zA-Z\d]/|same:password_confirmation',
                'registration.school_location' => 'sometimes',
                'registration.website_url' => 'sometimes',
                'registration.address' => 'sometimes',
                'registration.house_number' => 'sometimes',
                'registration.postcode' => 'sometimes',
                'registration.city' => 'sometimes',
            ];
        }
        if ($this->step === 2) {
            return [
                'registration.gender' => 'sometimes',
                'registration.gender_different' => 'sometimes',
                'registration.name_first' => 'sometimes',
                'registration.email' => 'sometimes',
                'registration.name' => 'sometimes',
                'registration.name_suffix' => 'sometimes',
                'password' => 'sometimes',
                'registration.school_location' => 'required',
                'registration.website_url' => 'required',
                'registration.address' => 'required',
                'registration.house_number' => 'required',
                'registration.postcode' => 'required',
                'registration.city' => 'required',
            ];
        }
        return [];
    }

    public function mount()
    {
//        dd('aa');
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
        if (empty($this->password)) {
            return 0;
        } else {
            return mb_strlen($this->password) < 8 ? 'red' : 'green';
        }
    }

    public function getMinDigitRuleProperty()
    {
        if (empty($this->password)) {
            return 0;
        } else {
            return preg_match('/\d/', $this->password) ? 'green' : 'red';
        }
    }

    public function getSpecialCharRuleProperty()
    {
        if (empty($this->password)) {
            return 0;
        } else {
            return preg_match('/[^a-zA-Z\d]/', $this->password) ? 'green' : 'red';
        }
    }

    public function step1()
    {
        $this->validate();
        $this->step = 2;

    }

    public function step2()
    {
        $this->validate();
        $this->registration->save();
        $this->step = 3;
    }


    public function updated($propertyName)
    {
        $this->btnDisabled = false;

        if ($this->step == 1) {
            $this->btnDisabled = (
                empty($this->registration->name_first)
                || empty($this->registration->gender)
                || empty($this->registration->email)
                || empty($this->registration->name)
                || empty($this->password_confirmation)
                || empty($this->password)
            );
        }
            $this->btnDisabled = false;
        if ($this->step == 2) {
            $this->btnDisabled = (
                empty($this->registration->school_location)
                || empty($this->registration->website)
                || empty($this->registration->address)
                || empty($this->registration->house_number)
                || empty($this->registration->postcode)
                || empty($this->registration->city)
            );
            }

        if ($propertyName === 'password_confirmation') {
            $propertyName = 'password';
        }

        $this->validateOnly($propertyName);
    }

    protected $messages = [
        'registration.name_first.required' => 'Voornaam is verplicht',
        'registration.name.required' => 'Achternaam is verplicht',
        'registration.email.required' => 'E-mailadres is verplicht',
        'registration.email.email' => 'E-mailadres is niet correct',
        'registration.gender.required' => 'Geef uw geslacht op',
        'password.required' => 'Wachtwoord is verplicht',
        'password.min' => 'Wachtwoord moet langer zijn dan 8 karakters',
        'password.regex' => 'Wachtwoord voldoet niet aan de eisen',
        'password.same' => 'Wachtwoord komt niet overeen',
        'registration.school_location.required' => 'Schoolnaam is verplicht',
        'registration.website_url.required' => 'Website is verplicht',
        'registration.address.required' => 'Adres is verplicht',
        'registration.house_number.required' => 'Huisnummer is verplicht',
        'registration.postcode.required' => 'Postcode is verplicht',
        'registration.city.required' => 'Plaatsnaam is verplicht',
    ];
}