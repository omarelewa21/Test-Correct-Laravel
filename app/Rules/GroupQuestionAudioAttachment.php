<?php

namespace tcCore\Rules;

use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\Mime\MimeTypes;
use tcCore\TestQuestion;

class GroupQuestionAudioAttachment implements Rule
{
    private $type;
    private $title;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type,$title)
    {
        $this->type = $type;
        $this->title = $title;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if($this->type!='file'){
            return true;
        }
        $path_parts = pathinfo($this->title);
        $mimeType = MimeTypes::getDefault()->getMimeTypes($path_parts['extension'])[0];
        if(!stristr($mimeType,'audio')){
            return true;
        }
        if(is_null(request()->route('test_question'))){
            return true;
        }
        $testQuestion = request()->route('test_question');
        if($testQuestion->question->type!='GroupQuestion'){
            return true;
        }
        $jsonObj = json_decode($value);
        if((int) $jsonObj->timeout>0 && (bool) $jsonObj->play_once){
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('attachment-modal.Groupquestion attachment error 1');
    }
}
