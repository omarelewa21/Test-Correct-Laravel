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
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        logger($validator->getRules());

        $usernameErrors = [];
//            collect(request('data'))->map(function ($row, $index) use ($validator, &$usernameErrors) {
//                if(User::where('username',$row['username'])->count() > 0){
//                    $usernameErrors[] = $row['username'];
//                }
//            });
//            if (count($usernameErrors)){
//                if (count($usernameErrors) === 1){
//                    $validator->errors()->add(
//                        sprintf('username'),
//                        sprintf('Er is al een collega met e-mailadres (%s) bij ons bekend',$usernameErrors[0])
//                    );
//                }
//                else {
//                    $validator->errors()->add(
//                        sprintf('username'),
//                        sprintf('Er zijn al collegas met e-mailadressen (%s) bij ons bekend',implode(',',$usernameErrors))
//                    );
//                }
//            }
//
//            $dataCollection = collect(request('data'))->map(function($a){return $a['username'];});
//            $unique = $dataCollection->unique();
//            $dataAr = $dataCollection->toArray();
//            if($unique->count() < $dataCollection->count()) {
//                $duplicates = $dataCollection->keys()->diff($unique->keys());
//                $duplicates->each(function($duplicate) use ($validator, $dataAr) {
//                    $validator->errors()->add(
//                        sprintf('data.%d.duplicate', $duplicate),
//                        sprintf('Dit e-mailadres (%s) komt meerdere keren voor',$dataAr[$duplicate])
//                    );
//                });
//            }
//        });
    }
}
