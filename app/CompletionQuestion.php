<?php namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Ramsey\Uuid\Uuid;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Traits\Questions\WithQuestionDuplicating;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Traits\UuidTrait;

class CompletionQuestion extends Question implements QuestionInterface
{
    use WithQuestionDuplicating;
    use UuidTrait;

    protected $casts = [
        'uuid'                             => EfficientUuid::class,
        'auto_check_answer'                => 'boolean',
        'auto_check_answer_case_sensitive' => 'boolean',
        'deleted_at'                       => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'completion_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['rating_method', 'subtype', 'auto_check_answer', 'auto_check_answer_case_sensitive'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $questionData = false;

    public function question()
    {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function completionQuestionAnswerLinks()
    {
        return $this->hasMany('tcCore\CompletionQuestionAnswerLink', 'completion_question_id');
    }

    public function completionQuestionAnswers()
    {
        return $this->belongsToMany(
            'tcCore\CompletionQuestionAnswer',
            'completion_question_answer_links',
            'completion_question_id',
            'completion_question_answer_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                $this->getDeletedAtColumn()
            ]
        )->wherePivot($this->getDeletedAtColumn(), null)->orderBy('completion_question_answer_links.order');
    }

    public function loadRelated()
    {
        $this->load('completionQuestionAnswers');
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function (CompletionQuestion $question) {
            $question->auto_check_answer = !!$question->auto_check_answer;
            $question->auto_check_answer_case_sensitive = !!$question->auto_check_answer_case_sensitive;
            return $question;
        });
    }

    public function duplicate(array $attributes, $ignore = null)
    {
        $question = $this->specificDuplication($attributes, $ignore);

        foreach ($this->completionQuestionAnswerLinks as $completionQuestionAnswerLink) {
            if ($ignore instanceof CompletionQuestionAnswer && $ignore->getKey() == $completionQuestionAnswerLink->getAttribute('completion_question_answer_id')) {
                continue;
            }

            if ($ignore instanceof CompletionQuestionAnswerLink
                && $ignore->getAttribute('completion_question_answer_id') == $completionQuestionAnswerLink->getAttribute('completion_question_answer_id')
                && $ignore->getAttribute('completion_question_id') == $completionQuestionAnswerLink->getAttribute('completion_question_id')) {
                continue;
            }

            if ($completionQuestionAnswerLink->duplicate($question, []) === false) {
                return false;
            }
        }

        return $question;
    }

    public function canCheckAnswer()
    {
        if ($this->isClosedQuestion()) { // cito based
            return true;
        } else if ($this->subtype == 'completion') { // don't auto check gatentekst
            return false;
        }

        $completionQuestionAnswers = $this->completionQuestionAnswers->groupBy('tag');
        unset($this->completionQuestionAnswers);

        if (!$completionQuestionAnswers) {
            return false;
        }

        foreach ($completionQuestionAnswers as $tag => $choices) {
            if (count($choices) < 1) {
                return false;
            }

            $hasCorrect = false;
            foreach ($choices as $choice) {
                if ($choice->getAttribute('correct') == 1) {
                    $hasCorrect = true;
                    break;
                }
            }

            if (!$hasCorrect) {
                return false;
            }
        }
        return true;
    }

    protected function isClosedQuestion()
    {
        return $this->isCitoQuestion() || $this->auto_check_answer;
    }

    public function checkAnswerCompletion($answer)
    {
        $completionQuestionAnswers = $this->completionQuestionAnswers->groupBy('tag');
        foreach ($completionQuestionAnswers as $tag => $choices) {
            $answers = [];
            foreach ($choices as $choice) {
                if ($choice->getAttribute('correct') == 1) {
                    $answers[] = $choice->getAttribute('answer');
                }
            }
            $completionQuestionAnswers[$tag] = $answers;
        }

        $answers = json_decode($answer->getAttribute('json'), true);
        if (!$answers) {
            return 0;
        }

        $correct = 0;
        foreach ($completionQuestionAnswers as $tag => $tagAnswers) {
            // as completion questions have a saved tag 0 based we need to lower them
            $refTag = $tag - 1;
            if (!array_key_exists($refTag, $answers)) {
                continue;
            }

            if ($this->auto_check_answer && !$this->auto_check_answer_case_sensitive) {
                $answers[$refTag] = Str::lower($answers[$refTag]);
                $tagAnswersAr = $tagAnswers;
                $tagAnswers = [];
                foreach ($tagAnswersAr as $key => $val) {
                    $tagAnswers[$key] = Str::lower($val);
                }
            }
            $tagAnswers = collect($tagAnswers)->map(function ($tagAnswer) {
                return BaseHelper::transformHtmlCharsReverse(
                    trim($tagAnswer)
                );
            })->toArray();
            if (in_array(trim($answers[$refTag]), $tagAnswers)
                || in_array(trim(BaseHelper::transformHtmlCharsReverse($answers[$refTag])), $tagAnswers)
                || in_array(trim(htmlentities($answers[$refTag])), $tagAnswers)
            ) {
                $correct++;
            }
        }

        if ($this->allOrNothingQuestion()) {
            if ($correct == count($completionQuestionAnswers)) {
                return $this->score;
            } else {
                return 0;
            }
        }


        $score = $this->getAttribute('score') * ($correct / count($completionQuestionAnswers));
        if ($this->getAttribute('decimal_score') == true) {
            $score = floor($score * 2) / 2;
        } else {
            $score = floor($score);
        }

        return $score;

    }

    public function checkAnswerMulti($answer)
    {
        $completionQuestionAnswers = $this->completionQuestionAnswers->groupBy('tag');
        foreach ($completionQuestionAnswers as $tag => $choices) {
            $answers = [];
            foreach ($choices as $choice) {
                if ($choice->getAttribute('correct') == 1) {
                    $answers[] = $choice->getAttribute('answer');
                }
            }
            $completionQuestionAnswers[$tag] = $answers;
        }

        $answers = json_decode($answer->getAttribute('json'), true);
        if (!$answers) {
            return 0;
        }

        $correct = 0;
        foreach ($completionQuestionAnswers as $tag => $tagAnswers) {
            if (!array_key_exists($tag, $answers)) {
                continue;
            }

            if (in_array($answers[$tag], $tagAnswers)) {
                $correct++;
            }
        }

        if ($this->allOrNothingQuestion()) {
            if ($correct == count($completionQuestionAnswers)) {
                return $this->score;
            } else {
                return 0;
            }
        }


        $score = $this->getAttribute('score') * ($correct / count($completionQuestionAnswers));
        if ($this->getAttribute('decimal_score') == true) {
            $score = floor($score * 2) / 2;
        } else {
            $score = floor($score);
        }

        return $score;
    }

    public function checkAnswer($answer)
    {
        if ($this->subtype == 'multi') {
            return $this->checkAnswerMulti($answer);
        }
        return $this->checkAnswerCompletion($answer);
    }

    public function deleteAnswers()
    {
        $this->completionQuestionAnswerLinks->each(function ($cQAL) {
            if (!$cQAL->delete()) {
                throw new QuestionException('Failed to delete completion question answer link', 422);
            }

            if ($cQAL->completionQuestionAnswer->isUsed($cQAL)) {
                // all okay, this one should be kept
//                throw new QuestionException(sprintf('Failed to delete the question answer, completionQuestionAnswer with id %d is still used',$cQAL->completionQuestionAnswer->id),422);
            } else {
                if (!$cQAL->completionQuestionAnswer->delete()) {
                    throw new QuestionException('Failed to delete completion question answer', 422);
                }
            }
        });
        return true;
    }

    /**
     * @param $mainQuestion either TestQuestion or GroupQuestionQuestion
     * @param $answers
     * @return boolean
     * @throws \Exception
     */
    public function addAnswers($mainQuestion, $answers)
    {
        $question = $this;
////        if ($question->isDirty() || $mainQuestion->isDirty() || $mainQuestion->isDirtyAttainments() || $mainQuestion->isDirtyTags() || ($question instanceof DrawingQuestion && $question->isDirtyFile())) {
////
////            if ($this->isUsed($mainQuestion)) {
//                $question = $this->duplicate([]);
//                if ($question === false) {
//                    throw new QuestionException('Failed to duplicate question', 422);
//                }
//                $mainQuestion->setAttribute('question_id', $question->getKey());
//
//                if (!$mainQuestion->save()) {
//                    throw new QuestionException('Failed to update test question', 422);
//                }
////            }
////        }

        if (!QuestionAuthor::addAuthorToQuestion($question)) {
            throw new QuestionException('Failed to attach author to question', 422);
        }

        $returnAnswers = [];
        $loop = 1;
        foreach ($answers as $answerDetails) {
            $completionQuestionAnswer = new CompletionQuestionAnswer();

            $answerDetails['answer'] = clean($answerDetails['answer']);//str_replace(['&eacute;','&euro;','&euml;','&nbsp;','&oacute;'],['é','€','ë',' ','ó'],$answerDetails['answer']);

            $completionQuestionAnswer->fill($answerDetails);
            if (!$completionQuestionAnswer->save()) {
                throw new QuestionException('Failed to create completion question answer', 422);
            }

            $completionQuestionAnswerLink = new CompletionQuestionAnswerLink();
            $completionQuestionAnswerLink->setAttribute('completion_question_id', $question->getKey());
            $completionQuestionAnswerLink->setAttribute('completion_question_answer_id', $completionQuestionAnswer->getKey());
            $completionQuestionAnswerLink->setAttribute('order', $loop++);

            if (!$completionQuestionAnswerLink->save()) {
                throw new QuestionException('Failed to create completion question answer link', 422);
            }
        }
        return true;
    }

    public function getCaptionAttribute()
    {
        if ($this->subtype === 'multi') {
            return __('test_take.completion_question_multi');
        }

        return parent::getCaptionAttribute();
    }

//    /**
//     * transform if needed for test, meaning that if there are no
//     * answers available and it is a completion question, it means
//     * that there's still some transformation needed towards
//     * [aa] => [1]
//     */
//    public function transformIfNeededForTest()
//    {
//        if($this->subtype == 'completion' && $this->completionQuestionAnswerLinks->count() === 0) {
//            $count = (object)['nr' => 0];
//            $this->getQuestionInstance()->question = preg_replace_callback(
//                '/\[(.*?)\]/i',
//                function ($matches) use ($count) {
//                    $count->nr++;
//                    return '[' . $count->nr . ']';
//                },
//                $this->getQuestionInstance()->question
//            );
//        }
//    }

    public function getTotalDataForTestQuestionUpdate($request)
    {
        $questionData = $this->getQuestionData($request);
        return array_merge($request->all(), $questionData);
    }

    public function getCompletionAnswerDirty($request)
    {
        $questionData = $this->getQuestionData($request);
        if (!array_key_exists('answers', $questionData)) {
            return false;
        }
        $currentAnswers = $this->completionQuestionAnswers()->OrderBy('id', 'asc')->get()->map(function ($item) {
            return $item->answer;
        })->toArray();
        $futureAnswers = collect($questionData['answers'])->values()->map(function ($item) {
            return $item['answer'];
        })->toArray();
        return (($currentAnswers !== $futureAnswers));
    }

    public function getQuestionData($request)
    {
        $qHelper = new QuestionHelper();
        if (!$this->questionData) {
            $questionHtml = $request->input('question');
            $this->questionData = $qHelper->getQuestionStringAndAnswerDetailsForSavingCompletionQuestion(
                question:$questionHtml,
                markAllAnswersAsCorrect: $this->isSubType('completion')
            );
            if (empty($this->questionData['question'])) {
                $this->questionData = false;
                return [];
            }
        }
        return $this->questionData;
    }

    public function needsToBeUpdated($request)
    {
        if ($this->getCompletionAnswerDirty($request)) {
            return true;
        }
        return parent::needsToBeUpdated($request);
    }

    public static function validateWithValidator(Validator $validator, $questionString, $subType, $fieldPreFix = '')
    {
        if (!strstr($questionString, '[') && !strstr($questionString, ']')) {
            if (request()->input('subtype') === 'completion') {
                $validator->errors()->add($fieldPreFix . 'question', 'U dient één woord tussen vierkante haakjes te plaatsen.');
            } else {
                $validator->errors()->add($fieldPreFix . 'question', 'U dient minimaal één woord tussen vierkante haakjes te plaatsen.');
            }
        }

//        if ($subType == 'completion' && strstr($questionString, '|')) {
//            $validator->errors()->add($fieldPreFix . 'question', 'U kunt geen |-teken gebruiken in de tekst of antwoord mogelijkheden');
//        }

        $check = false;
        $errorMessage = "U heeft het verkeerde formaat van de vraag ingevoerd, zorg ervoor dat elk haakje '[' gesloten is en er geen overlap tussen haakjes is.";
        for ($charIndex = 0; $charIndex < strlen($questionString); $charIndex++) {
            if ($questionString[$charIndex] == '[' && !$check) {        // set check to true if [ char found
                $check = true;
            } elseif ($questionString[$charIndex] == ']' && $check) {     // if ] char found return check to false
                $check = false;
            } elseif ($questionString[$charIndex] == ']' && !$check) {    // if ] char found and there was no [ before resutls in an error
                $check = false;
                $validator->errors()->add($fieldPreFix . 'question', $errorMessage);
                break;
            } elseif ($check && $questionString[$charIndex] == '[') {     // if [ char found with check set to true results in an error
                $check = false;
                $validator->errors()->add($fieldPreFix . 'question', $errorMessage);
                break;
            }
        }
        if ($check) {                                             // if check is true results in an error
            $validator->errors()->add($fieldPreFix . 'question', $errorMessage);
        }

        $qHelper = new QuestionHelper();

        $questionData = $qHelper->getQuestionStringAndAnswerDetailsForSavingCompletionQuestion(
            question: $questionString,
            isNewQuestion: true,
            markAllAnswersAsCorrect:  $subType === 'completion'
        );

        foreach ($questionData['answers'] as $answer) {
            if (trim($answer['answer']) == '') {
                if (request()->input('subtype') === 'completion') {
                    $validator->errors()->add($fieldPreFix . 'question', 'U dient één woord tussen vierkante haakjes te plaatsen.');
                } else {
                    $validator->errors()->add($fieldPreFix . 'question', 'U dient minimaal één woord tussen vierkante haakjes te plaatsen.');
                }
                break;
            }

            if (trim(clean(html_entity_decode($answer['answer']))) == '') {
                $validator->errors()->add($fieldPreFix . 'question', 'U heeft tekens gebruikt die hier niet mogelijk zijn');
                break;
            }
        }

        if ($subType == 'multi') {
            if ($questionData["error"]) {
                $validator->errors()->add($fieldPreFix . 'question', $questionData["error"]);
            }
        }
    }

    public static function decodeCompletionTags($question)
    {
        if (!$question->completionQuestionAnswers) {
            return $question->getQuestionHtml();
        }

        $tags = [];
        $question->completionQuestionAnswers->each(function ($tag) use (&$tags) {
            $tags[$tag['tag']][] = $tag['answer'];
        });

        $searchPattern = '/\[([0-9]+)\]/i';
        $replacementFunction = function ($matches) use ($question, $tags) {
            $tag_id = $matches[1]; // the completion_question_answers list is 1 based
            if (isset($tags[$tag_id])) {
                return sprintf('[%s]', implode('|', $tags[$tag_id]));
            }
        };

        return preg_replace_callback($searchPattern, $replacementFunction, $question->getQuestionHtml());
    }

    public function isFullyAnswered(Answer $answer): bool
    {
        $givenAnswersCount = collect(json_decode($answer->json, true))->filter()->count();
        return $givenAnswersCount === $this->completionQuestionAnswers()->distinct('tag')->count();
    }

    public function getCorrectAnswerStructure()
    {
        return CompletionQuestionAnswerLink::join(
            'completion_question_answers',
            'completion_question_answer_links.completion_question_answer_id',
            '=',
            'completion_question_answers.id'
        )
            ->orderBy('completion_question_answer_links.order')
            ->select('completion_question_answers.*')
            ->where('completion_question_id', $this->getKey())
            ->whereNull('completion_question_answers.deleted_at')
            ->get();
    }
}
