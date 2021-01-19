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
        // trim whitepaces
        $emailAddresses = trim($this->data['email_addresses']);
        // trim ; if last char
        $emailAddresses = rtrim($emailAddresses,';');
        // trim , if last char
        $emailAddresses = rtrim($emailAddresses, ',');
        // split on , or ; (if you want to add another split character, make sure to rtrim that one as well)
        $this->merge(['email_addresses' => array_map('trim',preg_split('/(,|;)/',$emailAddresses))]);
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
            'email_addresses.*'  => ['email:rfc',function ($attribute, $value, $fail) {

                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {

                    return $fail(sprintf('The email address contains international characters.', $value));

                }
            }],
            'school_location_id' => 'required',
            'user_roles'         => 'required',
            'invited_by'         => 'required',
            'send_welcome_mail'  => 'sometimes',
            'step'               => ['required', Rule::in([1,2])],
        ];


        return $this->step == 2
            ? $rules + ['data.message' => 'required|string|min:10|max:640']
            : $rules;
    }

    public function messages()
    {
        return [
            'data.message.required' => 'Het bericht is verplicht',
            'data.message.min'      => 'Het bericht moet minimaal :min karakters lang zijn.',
            'data.message.max'      => 'Het bericht mag maximaal :max karakters lang zijn.',
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
                            return sprintf('<ins>%s</ins>', $emailAddress);
                        }
                        return $emailAddress;
                    })->implode(';');
                $pattern = 'Uit de volgende e-mailadressen zijn de onderstreepte niet valide: %s .';

                if (count($this->email_addresses) == 1) {
                    $pattern = 'Het e-mailadres %s is niet valide.';
                }
                $validator->getMessageBag()->add('form', sprintf($pattern, $errorMsg));
            }
        });
    }
}
