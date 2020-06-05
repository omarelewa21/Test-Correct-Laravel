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

}
