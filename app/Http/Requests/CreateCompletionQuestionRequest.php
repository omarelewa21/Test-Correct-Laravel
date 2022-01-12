<?php

namespace tcCore\Http\Requests;

use tcCore\Http\Helpers\QuestionHelper;

/**
 * Should not be called a request as it is only a helper
 */
class CreateCompletionQuestionRequest extends CreateQuestionRequest {

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
		$baseRules = parent::baseRules();

		return array_merge($baseRules, [
			'type' => 'required|in:CompletionQuestion',
			'subtype' => '',
			'rating_method' => ''
		]);
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
	 * @param  \Illuminate\Validation\Validator $validator
	 * @return void
	 */
	public function getWithValidator($validator){
		$validator->after(function ($validator) {
			$question = request()->input('question');
			if(!strstr($question, '[') && !strstr($question, ']')) {
                if(request()->input('subtype') === 'completion'){
                    $validator->errors()->add('question', 'U dient &eacute;&eacute;n woord tussen vierkante haakjes te plaatsen.');
                } else {
                    $validator->errors()->add('question', 'U dient minimaal &eacute;&eacute;n woord tussen vierkante haakjes te plaatsen.');
                }
			}

			if(request()->input('subtype') == 'completion' && strstr($question,'|')){
				$validator->errors()->add('substype','U kunt geen |-teken gebruiken in de tekst of antwoord mogelijkheden');
			}

			$check         = false;
            $errorMessage  = "U heeft het verkeerde formaat van de vraag ingevoerd, zorg ervoor dat elk haakje '[' gesloten is en er geen onderschepping tussen haakjes is.";
            for ($charIndex = 0; $charIndex < strlen($question); $charIndex++){
                if($question[$charIndex] == '[' && !$check){        // set check to true if [ char found
                    $check = true;
                }elseif($question[$charIndex] == ']' && $check){     // if ] char found return check to false
                    $check = false;
                }elseif($question[$charIndex] == ']' && !$check){    // if ] char found and there was no [ before resutls in an error
                    $check = false;
                    $validator->errors()->add('question', $errorMessage);
                    break;
                }elseif($check && $question[$charIndex] == '['){     // if [ char found with check set to true results in an error
                    $check = false;
                    $validator->errors()->add('question', $errorMessage);
                    break;
                }
            }
            if($check){                                             // if check is true results in an error
                $validator->errors()->add('question', $errorMessage);
            }

			if(request()->input('subtype') == 'multi'){
				$qHelper = new QuestionHelper();
				$questionData = $qHelper->getQuestionStringAndAnswerDetailsForSavingCompletionQuestion($question, true);
				if($questionData["error"]){
					$validator->errors()->add('question', $questionData["error"]);
				}
			}
		});
	}

}
