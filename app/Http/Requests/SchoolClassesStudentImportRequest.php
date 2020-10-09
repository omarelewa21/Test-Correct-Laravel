<?php namespace tcCore\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Ramsey\Uuid\Uuid;
use tcCore\SchoolLocation;

class SchoolClassesStudentImportRequest extends Request
{

    protected $schoolLocation;
    protected $schoolClass;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->schoolLocation = $this->route('schoolLocation');
        $this->schoolClass = $this->route('schoolClass');

        return
            Auth::user()->hasRole('School manager')
            && $this->schoolLocation !== null
            && Auth::user()->school_location_id == $this->schoolLocation->getKey();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->filterInput();

        $extra_rule = [];
        // unique constraint needs to be added on external_id can only exist within a school if it is the same user (that is username is the currect username)
        foreach ($this->data as $key => $value) {
            if (array_key_exists('username', $value)) {
                $extra_rule[sprintf('data.%d.external_id', $key)] = sprintf('unique:users,external_id,%s,username,school_location_id,%d', $value['username'], $this->schoolLocation->getKey());
            }
        }

        foreach ($this->data as $key => $value) {
            if (array_key_exists('username', $value)) {
                $extra_rule[sprintf('data.%d.username', $key)] = sprintf('unique:users,username,%s,external_id,username,%s', $value['external_id'], $value['username']);
            }
        }

        $rules = collect([
            //'data' => 'array',
            'data.*.username'    => 'required|email',
            'data.*.name_first'  => 'required',
            'data.*.name'        => 'required',
            'data.*.name_suffix' => '',
            'data.*.gender'      => '',
        ]);

        if ($extra_rule === []) {
            $mergedRules = $rules->merge([
                'data.*.external_id' => 'required',
            ]);
        } else {
            $mergedRules = $rules->merge($extra_rule);
        }
        logger($mergedRules->toArray());

        return $mergedRules->toArray();
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
        $validator->after(function ($validator) {
            if ($this->schoolClass == null) {
                $validator->errors()->add('class', 'Er dient een klas opgegeven te worden');
            }

            $data = $this->addDuplicateExternalIdErrors($validator);
            $data = $this->addDuplicateUsernameErrors($validator);

            if (isset($data['filter']) && isset($data['filter']['school_location_id']) && Uuid::isValid($data['filter']['school_location_id'])) {
                $item = SchoolLocation::whereUuid($data['filter']['school_location_id'])->first();
                if (!$item) {
                    $validator->errors()->add('school_location_id', 'De school locatie kon niet gevonden worden.');
                } else {
                    $data['filter']['school_location_id'] = $item->getKey();
                }
            }
            $this->merge(['data' => $data]);
        });
    }

    private function addDuplicateExternalIdErrors($validator){
        $data = collect(request()->input('data'));
        $uniqueFields = ['external_id'];
        $groupedByDuplicates = $data->groupBy(function ($row, $key) {
            if (array_key_exists('external_id', $row)) {
                return $row['external_id'];
            }
        })->map(function ($item) {
            return collect($item)->count();
        })->filter(function ($item, $key) {
            return $item > 1;
        });

        if ($groupedByDuplicates->count() < $data->count()) {
            collect($this->data)->each(function ($item, $key) use ($groupedByDuplicates, $validator) {
                if (array_key_exists('external_id', $item) && array_key_exists($item['external_id'], $groupedByDuplicates->toArray())) {
                    $validator->errors()->add(
                        sprintf('data.%d.external_id', $key),
                        'Deze import bevat dubbele studentennummers'
                    );
                }
            });
        }

        return $data->toArray();
    }

    private function addDuplicateUsernameErrors($validator){
        $data = collect(request()->input('data'));
        $groupedByDuplicates = $data->groupBy(function ($row, $key) {
            if (array_key_exists('username', $row)) {
                return $row['username'];
            }
        })->map(function ($item) {
            return collect($item)->count();
        })->filter(function ($item, $key) {
            return $item > 1;
        });

        if ($groupedByDuplicates->count() < $data->count()) {
            collect($this->data)->each(function ($item, $key) use ($groupedByDuplicates, $validator) {
                if (array_key_exists('username', $item) && array_key_exists($item['username'], $groupedByDuplicates->toArray())) {
                    $validator->errors()->add(
                        sprintf('data.%d.username', $key),
                        'Deze import bevat dubbele emailadressen'
                    );
                }
            });
        }

        return $data->toArray();
    }

}
