<?php namespace tcCore\Lib\Question;

use Illuminate\Support\Facades\Log;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\GroupQuestion;
use tcCore\Lib\Answer\AnswerChecker;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Traits\CanClearStaticProperties;

class QuestionGatherer
{
    use CanClearStaticProperties;

    static protected $questions = [];
    static protected $questionsDotted = [];
    static protected $questionGroupCache = [];

    public static function getQuestionsOfTest($testId, $dottedIds)
    {
        if ((!$dottedIds && !array_key_exists($testId, static::$questions)) || ($dottedIds && !array_key_exists($testId, static::$questionsDotted))) {
            static::$questions[$testId] = [];
            static::$questionsDotted[$testId] = [];
            $test = Test::with([
                                   'testQuestions' => function ($query) {
                                       $query->orderBy('order', 'asc');
                                   },
                                   'testQuestions.question'])->find($testId);

            foreach ($test->testQuestions as $testQuestion) {
                $question = $testQuestion->question;

                if ($question instanceof GroupQuestion) {
                    static::getQuestionsOfGroupQuestion($question, [], static::$questions[$testId], static::$questionsDotted[$testId]);
                } elseif ($question instanceof QuestionInterface) {
                    if (!array_key_exists($question->getKey(), static::$questions[$testId])) {
                        static::$questions[$testId][$question->getKey()] = $question;
                    }

                    if (!array_key_exists($question->getKey(), static::$questionsDotted[$testId])) {
                        $question = clone $question;
                        $question->setAttribute('order', $testQuestion->getAttribute('order'));
                        $question->setAttribute('discuss', $testQuestion->getAttribute('discuss'));
                        static::$questionsDotted[$testId][$question->getKey()] = $question;
                    }
                }
            }
        }

        if ($dottedIds) {
            return static::$questionsDotted[$testId];
        } else {
            return static::$questions[$testId];
        }
    }

    public static function getCarouselQuestionsOfTest($testId)
    {
        $carouselQuestions = [];
        $test = Test::with([
                               'testQuestions' => function ($query) {
                                   $query->orderBy('order', 'asc');
                               },
                               'testQuestions.question'])->find($testId);

        foreach ($test->testQuestions as $testQuestion) {
            $question = $testQuestion->question;

            if ($question instanceof GroupQuestion) {
                if ($question->groupquestion_type != 'carousel') {
                    continue;
                }
                $score = (new QuestionHelper())->getTotalScoreForCarouselQuestion($question);
                $questionId = $question->getKey();
                $question = clone $question;
                $question->setAttribute('score', $score);
                $carouselQuestions[$questionId] = $question;
            }
        }
        return $carouselQuestions;
    }

    public static function getQuestionOfTest($testId, $questionId, $dottedIds)
    {
        $questions = static::getQuestionsOfTest($testId, $dottedIds);
        if (array_key_exists($questionId, $questions)) {
            return $questions[$questionId];
        } else {
            return null;
        }
    }

    protected static function getQuestionsOfGroupQuestion(GroupQuestion $question, $parents, &$array, &$dottedArray)
    {
        if (in_array($question->getKey(), $parents)) {
            return;
        }
        $groupQuestionUuid = $question->uuid;
        $parents[] = $question->getKey();
        $dottedPrefix = implode('.', $parents) . '.';

        if (!array_key_exists($question->getKey(), static::$questionGroupCache)) {
            static::$questionGroupCache[$question->getKey()] = $question->groupQuestionQuestions()->orderBy('order', 'asc')->with('question')->get();
        }
        $groupQuestionQuestions = static::$questionGroupCache[$question->getKey()];

        foreach ($groupQuestionQuestions as $groupQuestionQuestion) {
            $question = $groupQuestionQuestion->question;
            if ($question instanceof GroupQuestion) {
                static::getQuestionsOfGroupQuestion($question, [], $array, $dottedArray);
            } elseif ($question instanceof QuestionInterface) {
                if (!array_key_exists($question->getKey(), $array)) {
                    $array[$question->getKey()] = $question;
                }

                $question = clone $question;
                $question->setAttribute('order', $groupQuestionQuestion->getAttribute('order'));
                $question->setAttribute('discuss', $groupQuestionQuestion->getAttribute('discuss'));
                $question->setAttribute('groupQuestionUuid', $groupQuestionUuid);
                $dottedArray[$dottedPrefix . $question->getKey()] = $question;
            }
        }
    }

    public static function invalidateGroupQuestionCache(GroupQuestion $question)
    {
        unset(static::$questionGroupCache[$question->getKey()]);
    }

    public static function invalidateTestCache(Test $test)
    {
        $testId = $test->getKey();
        unset(static::$questions[$testId], static::$questionsDotted[$testId]);
    }

    public static function getNextQuestionId($testId, $dottedQuestionId, $skipClosed = false, $skipDoNotDiscuss = false)
    {
        $questions = array_keys(static::getQuestionsOfTest($testId, true));

        if ($dottedQuestionId === '') {
            $dottedQuestionId = null;
        }
        foreach ($questions as $questionId) {
            // If dottedQuestionId is null, return the questionId. The handles #1: getting the first question, #2: getting the next question because dottedQuestionId will be set to null
            if ($dottedQuestionId === null) {
                if (self::questionIsPartOfCarousel($questionId, $testId)) {
                    continue;
                }
                if ($skipDoNotDiscuss) {
                    $question = static::$questionsDotted[$testId][$questionId];
                    if ($question->discuss === 0) {
                        continue;
                    }
                }
                if ($skipClosed) {
                    $question = static::$questionsDotted[$testId][$questionId];
                    if (!$question->canCheckAnswer()) {
                        return $questionId;
                    }
                    continue;
                }

                return $questionId;
            }

            // If the current question has the dottedQuestionId, the next question is the 'first'.
            if ($questionId == $dottedQuestionId) {
                $dottedQuestionId = null;
            }
        }

        return false;
    }

    private static function questionIsPartOfCarousel($questionId, $testId)
    {
        if (!stristr($questionId, '.')) {
            return false;
        }
        $strippedQuestionId = explode('.', $questionId)[0];
        $question = GroupQuestion::find($strippedQuestionId);
        if (is_null($question)) {
            return false;
        }
        if ($question->groupquestion_type == 'carousel') {
            return true;
        }
        return false;
    }
}