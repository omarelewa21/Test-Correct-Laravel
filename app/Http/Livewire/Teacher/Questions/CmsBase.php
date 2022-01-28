<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

class CmsBase
{
    protected function transformHtmlChars($answer)
    {
        $answer = str_replace('<','&lt;',$answer);
        $answer = str_replace('>','&gt;',$answer);
        return $answer;
    }

    protected function transformHtmlCharsReverse($answer)
    {
        $answer = str_replace('&lt;','<',$answer);
        $answer = str_replace('&gt;','>',$answer);
        return $answer;
    }
}