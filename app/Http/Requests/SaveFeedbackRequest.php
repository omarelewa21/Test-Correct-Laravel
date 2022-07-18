<?php

namespace tcCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use tcCore\Answer;

class SaveFeedbackRequest extends FormRequest
{

     /**
     * @var GroupQuestion
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
        $feedback = $this->answer->feedback()->where('user_id', auth()->id())->first();
        if(!is_null($feedback)){
            return $feedback->user->id === auth()->id();
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
        return [
            'message' => 'required',
        ];
    }
}
