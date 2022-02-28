<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use tcCore\TestQuestion;

class UpdateTestQuestionRequest extends UpdateQuestionRequest {

    /**
     * @var TestQuestion
     */
    protected $testQuestion;

    /**
     *
     * @param Route $route
     */
    function __construct(Route $route = null)
    {
        if ($route instanceof Route) {
            $this->testQuestion = $route->parameter('test_question');
            $this->question = $this->testQuestion->question;
            $this->route = $route;
        }
    }

    public static function makeWithParams($test_question_id) {
        $instance = new self();
        $instance->testQuestion = TestQuestion::whereUUID($test_question_id)->first();
        $instance->question = $instance->testQuestion->question;
        return $instance;

    }

    public function messages(){
        return [
            'title.required' => 'A title is required',
            'body.required'  => 'A message is required',
        ];
    
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules['question_id'] = 'sometimes|question_id';
        $rules['test_id'] = 'sometimes|exists:tests,id,deleted_at,NULL';
        $rules['order'] = 'sometimes|required|integer|min:0';
        $rules['maintain_position'] = 'sometimes|required|in:0,1';
        $rules['discuss'] = 'sometimes|required|in:0,1';
//        $rules['closeable'] = 'required|in:0,1';

        return $rules;
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

    public function prepareForValidation()
    {
            $data = ($this->all());

            $this->handleEmptyArrayVar($data,'tags');
            $this->handleEmptyArrayVar($data,'attainments');
            $this->handleEmptyStringVar($data,'rtti');
            $this->handleEmptyStringVar($data,'bloom');
            $this->handleEmptyStringVar($data,'miller');
            $this->merge($data);
    }

    private function handleEmptyArrayVar(&$data,$var){
        if (isset($data[$var])) {
            if (empty($data[$var])) {
                $data[$var] = [];
            }
        }
    }

    private function handleEmptyStringVar(&$data,$var){
        if (isset($data[$var])) {
            if (empty($data[$var])) {
                $data[$var] = null;
            }
        }
    }

}
