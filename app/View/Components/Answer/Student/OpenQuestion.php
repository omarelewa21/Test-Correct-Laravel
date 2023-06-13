<?php

namespace tcCore\View\Components\Answer\Student;

use Illuminate\Support\Str;
use tcCore\Question;
use tcCore\Answer;
use function PHPUnit\Framework\stringContains;

class OpenQuestion extends QuestionComponent
{
    public string $answerValue;

    public function __construct(
        public Question $question,
        public Answer   $answer,
        public string $editorId,
        public bool $webSpellChecker = false,
        public string $commentMarkerStyles = '',
    )
    {
        parent::__construct($question, $answer);
        $this->allowWsc = auth()->user()->schoolLocation->allow_wsc;
    }

    protected function setAnswerStruct($question, $answer): void
    {
        $this->answerValue = json_decode($this->answer->json)->value ?? '';

        $this->answerValue = Str::replace(
            chr(194).chr(160),
            " ".chr(194).chr(160),
            $this->answerValue
        );

//        $haystack2 = Str::substr($this->answerValue, 287, 8);
//        $tempie = [];
//        foreach(str_split($haystack2, 1) as $value) {
//            $tempie[] = ord($value);
//        }
//        dd($tempie, str_split($haystack2, 1)[3], str_split($haystack2, 1)[4]);
//
//        $needle = "<p> </p>";
//$haystack = (string) "<p>".chr(160)."</p>";
//        $temp = Str::contains($needle, $haystack);
//        var_dump(chr(255));
//        dd($this->answerValue, $haystack2, $temp, chr(255), chr(160), "<p>".chr(160)."</p>");
//
//        dd($this->answerValue);
    }
}