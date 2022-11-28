<?php namespace tcCore\Lib\GroupQuestionQuestion;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use tcCore\GroupQuestionQuestion;
use tcCore\TestQuestion;

class GroupQuestionQuestionManager {
    protected $testQuestion;
    protected $groupQuestionQuestions;

    public static function getInstance($group_question_question_path) {
        $group_question_question_path_parts = explode('.', $group_question_question_path);
        $testQuestionId = array_shift($group_question_question_path_parts);
        $groupQuestionQuestionIds = $group_question_question_path_parts;

        $testQuestion = TestQuestion::findOrFail($testQuestionId);
        $groupQuestionQuestions = GroupQuestionQuestion::findOrFail($groupQuestionQuestionIds);

        $groupQuestionQuestionsOrdered = [];
        $nextGroupQuestionQuestionId = $testQuestion->getAttribute('question_id');
        $groupQuestionQuestionsCount = count($groupQuestionQuestions);

        while($nextGroupQuestionQuestionId !== false && count($groupQuestionQuestionsOrdered) < $groupQuestionQuestionsCount) {
            $found = false;
            foreach($groupQuestionQuestions as $groupQuestionQuestion) {
                if ($groupQuestionQuestion->getAttribute('group_question_id') == $nextGroupQuestionQuestionId) {
                    $groupQuestionQuestionsOrdered[] = $groupQuestionQuestion;
                    $nextGroupQuestionQuestionId = $groupQuestionQuestion->getAttribute('question_id');
                    $found = true;
                    break;
                }
            }

            if ($found === false) {
                throw new ModelNotFoundException('group question question not found');
            }
        }

        return new static($testQuestion, $groupQuestionQuestionsOrdered);
    }

    public static function getInstanceWithUuid($group_question_question_path) {
        $group_question_question_path_parts = explode('.', $group_question_question_path);
        $testQuestionId = array_shift($group_question_question_path_parts);
        $groupQuestionQuestionIds = $group_question_question_path_parts;
        $testQuestion = TestQuestion::whereUuid($testQuestionId)->firstOrFail();
        $groupQuestionQuestionsOrdered = [];
        if (!empty($groupQuestionQuestionIds)) {
            $groupQuestionQuestions = [];

            foreach ($groupQuestionQuestionIds as $key => $value) {
                $groupQuestionQuestions[] = GroupQuestionQuestion::whereUuid($value)->firstOrFail();
            }
            $nextGroupQuestionQuestionId = $testQuestion->getAttribute('question_id');
            $groupQuestionQuestionsCount = count($groupQuestionQuestions);

            while($nextGroupQuestionQuestionId !== false && count($groupQuestionQuestionsOrdered) < $groupQuestionQuestionsCount) {
                $found = false;
                foreach($groupQuestionQuestions as $groupQuestionQuestion) {
                    if ($groupQuestionQuestion->getAttribute('group_question_id') == $nextGroupQuestionQuestionId) {
                        $groupQuestionQuestionsOrdered[] = $groupQuestionQuestion;
                        $nextGroupQuestionQuestionId = $groupQuestionQuestion->getAttribute('question_id');
                        $found = true;
                        break;
                    }
                }

                if ($found === false) {
                    throw new ModelNotFoundException('group question question not found');
                }
            }
        }

        return new static($testQuestion, $groupQuestionQuestionsOrdered);
    }

    /**
     * @param tcCore\TestQuestion $testQuestion
     * @param array of tcCore\GroupQuestionQuestion $groupQuestionQuestions
     */
    public function __construct($testQuestion, $groupQuestionQuestions)
    {
        $this->testQuestion = $testQuestion;
        $this->groupQuestionQuestions = $groupQuestionQuestions;
    }

    public function getQuestionLink() {
        if (!$this->groupQuestionQuestions) {
            return $this->testQuestion;
        } else {
            return $this->arrayLast($this->groupQuestionQuestions);
        }
    }

    public function isUsed() {

        $toIgnore = $this->testQuestion->question;
        if ($toIgnore->isUsed($this->testQuestion)) {
            return true;
        }

        foreach($this->groupQuestionQuestions as $groupQuestionQuestion) {
            $question = $groupQuestionQuestion->question;
            if ($question->isUsed($toIgnore)) {
                return true;
            }
            $toIgnore = $groupQuestionQuestion;
        }
        return false;
    }

    public function prepareForChange($relationToIgnore=null) {
        $question = $this->testQuestion->question;
        $i = 1;
        $prevGroupQuestionQuestion = null;
        $prevKey = null;
        $testQuestion = null;
        foreach($this->groupQuestionQuestions as $key => $groupQuestionQuestion) {
            $prevGroupQuestionQuestion = $groupQuestionQuestion;
            $prevKey = $key;
            $newQuestion = $question->duplicate($groupQuestionQuestion->question);
            if ($i === 1) {
                $testQuestion = $this->testQuestion;
                $testQuestion->setAttribute('question_id', $newQuestion->getKey());
            } else {
                $newGroupQuestionQuestion = $prevGroupQuestionQuestion->replicate();
                $newGroupQuestionQuestion->setAttribute('group_question_id', $question->getKey());
                $newGroupQuestionQuestion->setAttribute('question_id', $newQuestion->getKey());
                $newGroupQuestionQuestion->setCallbacks(false);
                $newGroupQuestionQuestion->save();
                $newGroupQuestionQuestion->setCallbacks(true);
                $this->groupQuestionQuestions[$prevKey] = $newGroupQuestionQuestion;
            }

            $question = $newQuestion;
            $i++;

        }

        $newQuestion = $question->duplicate([], $relationToIgnore, false);
        if ($i === 1) {
            $testQuestion = $this->testQuestion;
            $testQuestion->setAttribute('question_id', $newQuestion->getKey());
        } else {
            $newGroupQuestionQuestion = $prevGroupQuestionQuestion->replicate();
            $newGroupQuestionQuestion->setAttribute('group_question_id', $question->getKey());
            $newGroupQuestionQuestion->setAttribute('question_id', $newQuestion->getKey());
            $newGroupQuestionQuestion->save();
            $this->groupQuestionQuestions[$prevKey] = $newGroupQuestionQuestion;
        }

        if ($i >= 1 && $testQuestion !== null) {
            $testQuestion->save();
        }
        $testQuestion->refresh();
        return $testQuestion;

    }

    public function getGroupQuestionQuestionPath() {
        $groupQuestionQuestionPath = $this->testQuestion->uuid;
        foreach($this->groupQuestionQuestions as $groupQuestionQuestion) {
            $groupQuestionQuestionPath .= '.'.$groupQuestionQuestion->uuid;
        }
        return $groupQuestionQuestionPath;
    }

    protected function arrayLast($array) {
        if (count($array) < 1)
            return null;

        $keys = array_keys($array);
        return $array[$keys[count($keys) - 1]];
    }

    public function isChild($child) {
        return $child->getAttribute('group_question_id') == $this->getQuestionLink()->getAttribute('question_id');
    }
}
