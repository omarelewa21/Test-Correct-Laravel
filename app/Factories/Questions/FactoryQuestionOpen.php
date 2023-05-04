<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

abstract class FactoryQuestionOpen extends FactoryQuestion
{
    use DoWhileLoggedInTrait;
    use PropertyGetableByName;

    /**
     * @param string|array $answer "answer" | ["answer" => "text"]
     * @return
     */
    public function addAnswer($answer)
    {
        if (!$answer) {
            throw new \Exception("Adding an answer string or array ['answer' => 'text'] to the method addAnswers() is required.");
        }

        if (is_array($answer) && ($answer['answer'] ?? false)) {
            $this->questionProperties = array_merge($this->questionProperties, $answer);
        }
        if (is_string($answer)) {
            $this->questionProperties['answer'] = $answer;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function questionType(): string
    {
        return "OpenQuestion";
    }

    public function answerDefinition(): string
    {
        return "<p>voorbeeld antwoord: 3.14</p> ";
    }

    public function questionDefinition(): string
    {
        return '<p>voorbeeld vraag:</p> <p>wat is de waarde van pi</p> ';
    }
}