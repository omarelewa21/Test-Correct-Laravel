<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;

class UpdateAnswerRequest extends Request {

	/**
	 * @var Answer
	 */
	private $answer;

	/**
	 *
	 * @param Route $route
	 */
	function __construct(Route $route)
	{
		$this->answer = $route->parameter('answer');
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
		$this->sanitize();

		$this->handleClosedAttribute();

		return [
			'test_participant_id' => '',
			'question_id' => '',
			'json' => '',
            'closed' => '',
            'closed_group' => '',
		];
	}

	private function handleClosedAttribute()
    {
        $input = $this->all();

        if ($input['close_action'] == 'close_group') {
            $input['closed_group'] = true;
        }

        if($input['close_action'] == 'close_question') {
            $input['closed'] = true;
        }

        return $this->replace($input);
    }

	/**
	 * Get the sanitized input for the request.
	 *
	 * @return array
	 */
	public function sanitize()
	{
		$input = $this->all();

        //unpack json answer and sanitize the input
        $answerJson = json_decode($input['json'], true);

		//sanitize input to prevent XSS
		if (!is_array($answerJson)) {
			return;
		}

        foreach ($answerJson as $key => $value) {
            $answerJson[$key] = clean($value);
        }

        $input['json'] = json_encode($answerJson, JSON_FORCE_OBJECT);





		return $this->replace($input);
	}

}
