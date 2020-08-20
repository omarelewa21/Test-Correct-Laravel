<?php


namespace tcCore\Http\Helpers\QtiImporter\v2dot2dot0;


use tcCore\Http\Controllers\TestQuestions\MultipleChoiceQuestionAnswersController;
use tcCore\Http\Requests\CreateMultipleChoiceQuestionAnswerRequest;

class QtiFactory
{

    private $qtiResource;

    private $lookupTable = false;

    public function __construct(QtiResource $qtiResource)
    {
        $this->qtiResource = $qtiResource;

        $this->setLookupTable();

    }

    public function qtiQuestionTypeToTestCorrectQuestionType($key)
    {
        $this->throwExceptionIfNotFound($key);

        return $this->lookupTable[$this->qtiResource->itemType][$key];
    }

    /**
     * @param array $map
     * @param $key
     * @throws \Exception
     */
    private function throwExceptionIfNotFound($key): void
    {
        if (!array_key_exists($this->qtiResource->itemType, $this->lookupTable)) {
            throw new \Exception(sprintf('Desired interaction type %s has no implementation in QTIFactory', $this->qtiResource->itemType));
        } elseif (!array_key_exists($key, $this->lookupTable[$this->qtiResource->itemType])) {
            throw new \Exception(sprintf('%s has no implmentation for this key: %s', __CLASS__, $key));
        }
    }


    private function setLookupTable(): void
    {
        $this->lookupTable = [
            'matchInteraction' => [
                'type' => 'MultipleChoiceQuestion',
                'subtype' => 'MultipleChoice',
                'class_answer_request' => new CreateMultipleChoiceQuestionAnswerRequest,
                'class_answer_controller' => new MultipleChoiceQuestionAnswersController,
            ],
            'inlineChoiceInteraction' => [
                'type' => 'CompletionQuestion',
                'subtype' => 'multi',
                'class_answer_request' => new CreateMultipleChoiceQuestionAnswerRequest,
                'class_answer_controller' => new MultipleChoiceQuestionAnswersController,
            ],
            'choiceInteraction' => [
                'type' => 'MultipleChoiceQuestion',
                'subtype' => 'MultipleChoice',
                'class_answer_request' => new CreateMultipleChoiceQuestionAnswerRequest,
                'class_answer_controller' => new MultipleChoiceQuestionAnswersController,
            ]
        ];
    }
}
