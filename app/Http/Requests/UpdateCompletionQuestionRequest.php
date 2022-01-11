<?php namespace tcCore\Http\Requests;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;
use tcCore\CompletionQuestion;
use tcCore\Http\Helpers\QuestionHelper;

class UpdateCompletionQuestionRequest extends UpdateQuestionRequest
{

    /**
     * @var CompletionQuestion
     */
    private $completionQuestion;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->completionQuestion = $this->route->parameter('testQuestion')->question;
        if ($this->completionQuestion instanceof CompletionQuestion) {
            $this->question = $this->completionQuestion->getQuestionInstance();
        }

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
            'type'          => 'sometimes|required|in:CompletionQuestion',
            'subtype'       => '',
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
    public function getWithValidator($validator)
    {
        $validator->after(function ($validator) {
            $question = request()->input('question');

            if(request()->route()->hasParameter('test_question')) {
                $completionQuestion = request()->route()->parameter('test_question')->question;
            } else if (request()->route()->hasParameter('group_question_question_id')){
                $completionQuestion = request()->route()->parameter('group_question_question_id')->question;
            }

            if ($completionQuestion->subtype == 'completion' && strstr($question, '|')) {
                $validator->errors()->add('question', 'U kunt geen |-teken gebruiken in de tekst of antwoord mogelijkheden');
            }

            if (!strstr($question, '[') && !strstr($question, ']')) {
                if($completionQuestion->subtype === 'completion'){
                    $validator->errors()->add('question', 'U dient &eacute;&eacute;n woord tussen vierkante haakjes te plaatsen.');
                } else {
                    $validator->errors()->add('question', 'U dient minimaal &eacute;&eacute;n woord tussen vierkante haakjes te plaatsen.');
                }
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

            if($completionQuestion->subtype == 'multi'){
				$qHelper = new QuestionHelper();
				$questionData = $qHelper->getQuestionStringAndAnswerDetailsForSavingCompletionQuestion($question, true);
				if($questionData["error"]){
					$validator->errors()->add('question', $questionData["error"]);
				}
			}
        });
    }
}
