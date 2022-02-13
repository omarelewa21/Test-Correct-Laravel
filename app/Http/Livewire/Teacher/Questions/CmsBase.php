<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

class CmsBase
{
    public static function transformHtmlChars($answer)
    {
        $answer = str_replace('<','&lt;',$answer);
        $answer = str_replace('>','&gt;',$answer);
        return $answer;
    }

    public static function transformHtmlCharsReverse($answer)
    {
        $answer = str_replace('&lt;','<',$answer);
        $answer = str_replace('&gt;','>',$answer);
        return $answer;
    }
}