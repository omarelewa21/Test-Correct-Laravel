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
            if (!strstr($question, '[') && !strstr($question, ']')) {
                $validator->errors()->add('question', 'U dient minimaal &eacute;&eacute;n woord tussen vierkante haakjes te plaatsen.');
            }

            if(request()->route()->hasParameter('test_question')) {
                $completionQuestion = request()->route()->parameter('test_question')->question;
            } else if (request()->route()->hasParameter('group_question_question_id')){
                $completionQuestion = request()->route()->parameter('group_question_question_id')->question;
            }

            if ($completionQuestion->subtype == 'completion' && strstr($question, '|')) {
                $validator->errors()->add('question', 'U kunt geen |-teken gebruiken in de tekst of antwoord mogelijkheden');
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
