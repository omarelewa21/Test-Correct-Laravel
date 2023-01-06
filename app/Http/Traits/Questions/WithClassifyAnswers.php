<?php

namespace tcCore\Http\Traits\Questions;

trait WithClassifyAnswers
{
    public $groupItemOrder = [];
    public $itemAnswerValues = [];
    public function mountWithClassifyAnswers()
    {
        $this->fillItemAnswerValuesArray();
        $this->setGroupItemOrderByAnswerStruct();
    }
    /**
     * @return void
     */
    protected function setGroupItemOrderByAnswerStruct(): void
    {
        if (array_key_exists('order', $this->answerStruct)) {
            $this->groupItemOrder = $this->answerStruct['order'];
        }
    }

    /**
     * @return void
     */
    protected function fillItemAnswerValuesArray(): void
    {
        $matchingQuestionAnswers = $this->question->matchingQuestionAnswers;
        $matchingQuestionAnswers->each(function ($item) {
            $this->itemAnswerValues[$item->id] = $item->answer;
            if ($item->isGroup()) {
                $this->groupItemOrder[$item->id] = [];
            }
        });
    }

    public function updateOrder($values): void
    {
        $this->buildGroupItemOrderArray(collect($values));
        parent::updateOrder($values);
    }

    private function buildGroupItemOrderArray($values): void
    {
        $updatedValues = $values;
        $updatedValues->shift();
        $updatedValues->each(function ($data) {
            $this->groupItemOrder[$data['value']] = $data['items'];
        });
    }

    protected function getJsonToStore(array $answerObject): string
    {
        $answerObject['order'] = $this->groupItemOrder;
        return json_encode($answerObject);
    }
}