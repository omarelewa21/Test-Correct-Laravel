<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


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
        if (strstr($this->data['email_addresses'], ';')) {
            $this->merge(['email_addresses' => explode(';', $this->data['email_addresses'])]);
        } else {
            $this->merge(['email_addresses' => [$this->data['email_addresses']]]);
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


        $rules = [
            'email_addresses.*'  => 'email:rfc', // ,dns?
            'school_location_id' => 'required',
            'user_roles'         => 'required',
            'invited_by'         => 'required',
            'send_welcome_mail'  => 'sometimes',
            'step'               => ['required', Rule::in([1,2])],
        ];


        return $this->step == 2
            ? $rules + ['data.message' => 'required|string|min:10']
            : $rules;
    }

    public function messages()
    {
        return [
            'data.message.required' => 'Het bericht is verplicht',
            'data.message.min'      => 'Het bericht moet minimaal :min karakters lang zijn.',
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


    protected function getValidatorInstance()
    {
        return parent::getValidatorInstance()->after(function ($validator) {
            // Call the after method of the FormRequest (see below)
            if ($emailErrors = $validator->errors()->get('email_addresses.*')) {
                $keysWithErrors = collect($emailErrors)->map(function ($error, $pattern) {
                    return (int) str_replace('email_addresses.', '', $pattern);
                });

                $errorMsg = collect($this->email_addresses)
                    ->map(function ($emailAddress, $key) use ($keysWithErrors) {
                        if ($keysWithErrors->contains($key)) {
                            return sprintf('<strong>%s</strong>', $emailAddress);
                        }
                        return $emailAddress;
                    })->implode(';');
                $pattern = 'De e-mailadressen %s zijn niet valide.';

                if (count($this->email_addresses) == 1) {
                    $pattern = 'Het e-mailadres %s is niet valide.';
                }
                $validator->getMessageBag()->add('form', sprintf($pattern, $errorMsg));
            }
        });
    }
}
