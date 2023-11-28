<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use tcCore\Answer;
use tcCore\AnswerParentQuestion;
use tcCore\Http\Helpers\AnswerParentQuestionsHelper;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Log;
use tcCore\SchoolLocation;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;
use function Termwind\terminal;
Trait BaseCommandTrait
{

    public function toOutput($string)
    {
        $this->output->write($string);
    }

    public function toInfo($string)
    {
        $this->info($string);
    }

    public function toError($string)
    {
        $this->error($string);
    }

    public function toComment($string)
    {
        $this->comment($string);
    }

    protected function writeInfoText($text, $endWithLineBreak = false)
    {
        $this->output->write('<info>'.$text.'<info>',$endWithLineBreak);
        $this->currentLineLength = strlen($text);
    }

    protected function writeDoneInfo($text = 'done', $color = null)
    {
        $endOnPosition = max(terminal()->width()-31, 0);

        $originalText = $text;
        if($color) {
            $text = '<fg='.$color.'>'.$text.'</>';
        }
        $lastLength = $this->currentLineLength;
        if($endOnPosition){
            $extraDots = $endOnPosition - strlen($originalText) - $lastLength;
            for($i=0;$i < $extraDots; $i++){
                $text = '.'.$text;
            }
        }

        $this->writeInfoText($text,true);
    }

}
