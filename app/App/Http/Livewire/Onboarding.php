<?php

namespace tcCore\App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\DemoTeacherRegistration;
use tcCore\User;

class Onboarding extends Component
{
    public $registration;

    public $step = 2;

    public $password;

    public $password_confirmation;

    public $btnDisabled = true;

    protected $queryString = ['step', 'email', 'confirmed'];

    public $email;
    public $confirmed;
    public $gender = 'male';

    public $warningStepOne = 0;
    public $warningStepTwo = 0;
    public $warningStepOneConfirmed = 0;
    public $warningStepTwoConfirmed = 0;

    public function rules()
    {
        $default = [
            'registration.school_location'  => 'sometimes',
            'registration.website_url'      => 'sometimes',
            'registration.address'          => 'sometimes',
            'registration.house_number'     => 'sometimes',
            'registration.postcode'         => 'sometimes',
            'registration.city'             => 'sometimes',
            'registration.gender'           => 'sometimes',
            'registration.gender_different' => 'sometimes',
            'registration.name_first'       => 'sometimes',
            'registration.username'         => 'required|email',
            'registration.name'             => 'sometimes',
            'registration.name_suffix'      => 'sometimes',
            'registration.confirmed'        => 'sometimes',
            'password'                      => 'sometimes',
        ];


        if ($this->step === 1) {
            return array_merge($default, [
                'registration.gender'           => 'required|in:male,female,different',
                'registration.gender_different' => 'sometimes',
                'registration.name_first'       => 'required|string',
                'registration.name'             => 'required|string',
                'registration.name_suffix'      => 'sometimes',
                'password'                      => 'required|min:8|regex:/\d/|regex:/[^a-zA-Z\d]/|same:password_confirmation',
            ]);
        }
        if ($this->step === 2) {
            return array_merge($default, [
                'registration.school_location' => 'required',
                'registration.website_url'     => 'required',
                'registration.address'         => 'required',
                'registration.house_number'    => 'required',
                'registration.postcode'        => 'required',
                'registration.city'            => 'required',
            ]);
        }

        return $default;

    }

    public function mount()
    {
        $this->registration = new DemoTeacherRegistration;
        $this->registration->username = $this->email;
        $this->registration->confirmed = $this->confirmed;
        $this->registration->gender = $this->gender;


        if (!$this->step != 1) {
            $this->step = 1;
        }
        if (!$this->email) {
            $this->email = '';
        }
        if (!$this->confirmed) {
            $this->confirmed = 0;
        }
        if ($this->confirmed === 1 && !$this->email) {
            $this->confirmed = 0;
        }

        //To do
        //If email is not set, redirect to main website.
    }

    public function backToStepOne()
    {
        $this->step = 1;
        $this->btnStepOneDisabledCheck();
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
            return mb_strlen($this->password) < 8 ? false : true;
        }
    }

    public function getMinDigitRuleProperty()
    {
        if (empty($this->password)) {
            return 0;
        } else {
            return preg_match('/\d/', $this->password) ? true : false;
        }
    }

    public function getSpecialCharRuleProperty()
    {
        if (empty($this->password)) {
            return 0;
        } else {
            return preg_match('/[^a-zA-Z\d]/', $this->password) ? true : false;
        }
    }

    public function step1()
    {
        $this->validate();
        if (!$this->checkInputForLength() && $this->warningStepOneConfirmed === 0) {
            $this->warningStepOneConfirmed = 1;
            return;
        }
        $this->step = 2;
        $this->btnStepTwoDisabledCheck();
        $this->warningStepOneConfirmed = 0;
    }

    public function step2()
    {
        $this->validate();
        if (!$this->checkInputForLength() && $this->warningStepTwoConfirmed === 0) {
            $this->warningStepTwoConfirmed = 1;
            return;
        }
        $this->registration->save();
        $this->registration->addUserToRegistration($this->password);
        $this->step = 3;
    }

    public function loginUser()
    {
        $user = User::where('username', $this->registration->username)->first();
        Auth::login($user);
        if (Auth::check()) {
            redirect('dashboard');
        } else {
            redirect('/');
        }

    }

    private function btnStepOneDisabledCheck()
    {
        if ($this->registration->confirmed != 1) {
            if ($this->step == 1) {
                $this->btnDisabled = (
                    empty($this->registration->name_first)
                    || empty($this->registration->gender)
                    || empty($this->registration->name)
                    || empty($this->password_confirmation)
                    || empty($this->password)
                    || empty($this->registration->username)
                );
            }
        } else {
            if ($this->step == 1) {
                $this->btnDisabled = (
                    empty($this->registration->name_first)
                    || empty($this->registration->gender)
                    || empty($this->registration->name)
                    || empty($this->password_confirmation)
                    || empty($this->password)
                );
            }
        }
    }

    private function btnStepTwoDisabledCheck()
    {
        if ($this->step == 2) {
            $this->btnDisabled = (
                empty($this->registration->city)
                || empty($this->registration->school_location)
                || empty($this->registration->website_url)
                || empty($this->registration->address)
                || empty($this->registration->postcode)
                || empty($this->registration->house_number)
            );
        }
    }

    public function checkInputForLength()
    {
        if ($this->step == 1) {
            if (strlen($this->registration->name_first) <= 1
                || strlen($this->registration->name) <= 1) {
                $this->warningStepOne = 1;
                return false;
            } else {
                $this->warningStepOne = 0;
                return true;
            }
        }
        if ($this->step == 2) {
            if (strlen($this->registration->city) <= 1
                || strlen($this->registration->school_location) <= 1
                || strlen($this->registration->website_url) <= 1
                || strlen($this->registration->address) <= 1) {
                $this->warningStepTwo = 1;
                return false;
            } else {
                $this->warningStepTwo = 0;
                return true;
            }
        }
    }


    public
    function updated($propertyName)
    {
        $this->btnDisabled = true;

        $this->btnStepOneDisabledCheck();
        $this->btnStepTwoDisabledCheck();

        if ($propertyName === 'password_confirmation') {
            $propertyName = 'password';
        }

        if ($this->registration->gender != 'different') {
            $this->registration->gender_different = '';
        }

        if ($propertyName != 'password') {
            $this->validateOnly($propertyName);
        }
    }

    protected
        $messages = [
        'registration.name_first.required'      => 'Voornaam is verplicht',
        'registration.name.required'            => 'Achternaam is verplicht',
        'registration.gender.required'          => 'Geef uw geslacht op',
        'password.required'                     => 'Wachtwoord is verplicht',
        'password.min'                          => 'Wachtwoord moet langer zijn dan 8 karakters',
        'password.regex'                        => 'Wachtwoord voldoet niet aan de eisen',
        'password.same'                         => 'Wachtwoord komt niet overeen',
        'registration.school_location.required' => 'Schoolnaam is verplicht',
        'registration.website_url.required'     => 'Website is verplicht',
        'registration.address.required'         => 'Adres is verplicht',
        'registration.house_number.required'    => 'Huisnummer is verplicht',
        'registration.postcode.required'        => 'Postcode is verplicht',
        'registration.city.required'            => 'Plaatsnaam is verplicht',
        'registration.username.required'        => 'E-mailadres is verplicht',
        'registration.username.email'           => 'E-mailadres is niet geldig',
    ];
}