<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use tcCore\SchoolClass;
use tcCore\Subject;
use tcCore\User;

class CreateTellATeacherRequest extends Request
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return
            Auth::user()->hasRole('Teacher');
    }

    /**
     * @inheritDoc
     */
    protected function prepareForValidation()
    {
        if (strstr($this->email_addresses, ';')) {
            $this->merge(['email_addresses' => explode(';', $this->email_addresses)]);
        } else {
            $this->merge(['email_addresses' => [$this->email_addresses]]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->filterInput();


        return [
            'email_addresses.*'  => 'email',
            'school_location_id' => 'required',
            'user_roles'         => 'required',
            'invited_by'         => 'required',
            'send_welcome_mail'  => 'sometimes',
        ];
    }

    /**
     * Get the sanitized input for the request.
     *
     * @return array
     */
    public function sanitize()
    {
        return $this->all();
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    protected function getValidatorInstance()
    {
        return parent::getValidatorInstance()->after(function($validator){
            // Call the after method of the FormRequest (see below)
            $this->after($validator);
        });
    }

    public function after($validator)
    {
        if ($emailErrors = $validator->errors()->get('email_addresses.*')) {
            $keysWithErrors = collect($emailErrors)->map(function ($error, $pattern) {
                return (int) str_replace('email_addresses.', '', $pattern);
            });

            $errorMsg = collect($this->get('email_addresses'))
                ->map(function ($emailAddress, $key) use ($keysWithErrors) {
                    if ($keysWithErrors->contains($key)) {
                        return sprintf('<strong>%s</strong>', $emailAddress);
                    }
                    return $emailAddress;
                })->implode(';');
            $pattern = 'De e-mailadressen %s zijn niet valide.';

            if (count($this->get('email_addresses')) == 1) {
                $pattern = 'Het e-mailadres %s is niet valide.';
            }
            $validator->getMessageBag()->add('form' ,sprintf($pattern, $errorMsg));
        }
    }
}
