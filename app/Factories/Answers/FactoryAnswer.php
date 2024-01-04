<?php

namespace tcCore\Factories\Answers;

use tcCore\Answer;
use tcCore\Question;

abstract class FactoryAnswer
{
    public Answer $answer;
    public Question $question;

    public static function generate(Answer $answer)
    {
        $factory = new static();
        $factory->answer = $answer;
        $factory->question = $answer->question;

        $factory->answer->json = $factory->setJsonAnswer();
        $factory->setDone();
        $factory->setTime();

        $factory->answer->save();
    }

    abstract protected function setJsonAnswer();

    protected function setTime()
    {
        $this->answer->time = rand(1, 100);
    }

    protected function setDone()
    {
        $this->answer->done = 1;
    }
}