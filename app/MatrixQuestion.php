<?php namespace tcCore;

use tcCore\Exceptions\QuestionException;
use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;

class MatrixQuestion extends Question implements QuestionInterface {

    use UuidTrait;

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'matrix_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['shuffle', 'subtype'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function question() {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function matrixQuestionSubQuestions()
    {
        return $this->hasMany('tcCore\MatrixQuestionSubQuestion')->orderBy('order');
    }

    public function matrixQuestionAnswers()
    {
        return $this->hasMany('tcCore\MatrixQuestionAnswer')->orderBy('order');
    }

    public function matrixQuestionAnswerSubQuestions()
    {
        return $this->hasManyThrough(MatrixQuestionAnswerSubQuestion::class,MatrixQuestionSubQuestion::class);
    }

    public function loadRelated()
    {
        $this->loadRelatedAnswers();
        $this->loadRelatedSubQuestions();
        $this->loadRelatedAnswerSubQuestions();
    }

    public function loadRelatedAnswers()
    {
        $this->load('matrixQuestionAnswers');
    }

    public function loadRelatedSubQuestions()
    {
        $this->load('matrixQuestionSubQuestions');
    }

    public function loadRelatedAnswerSubQuestions()
    {
        $this->load('matrixQuestionAnswerSubQuestions');
    }

    public function duplicate(array $attributes, $ignore = null) {
        $question = $this->replicate();

        $question->parentInstance = $this->parentInstance->duplicate($attributes, $ignore);
        if ($question->parentInstance === false) {
            return false;
        }

        $question->fill($attributes);

        if ($question->save() === false) {
            return false;
        }

        $answerReferences = [];

        foreach($this->matrixQuestionAnswers as $matrixQuestionAnswer){
            if ($ignore instanceof MatrixQuestionAnswer && $ignore->getKey() == $matrixQuestionAnswer->getKey()) {
                continue;
            }

            $newMatrixQuestionAnswer = $matrixQuestionAnswer->duplicate($question, []);
            if($newMatrixQuestionAnswer === false) {
                return false;
            } else {
                $answerReferences[$matrixQuestionAnswer->getKey()] = $newMatrixQuestionAnswer;
            }
        }

        foreach($this->matrixQuestionSubQuestions as $matrixQuestionSubQuestion){
            if ($ignore instanceof MatrixQuestionSubQuestion && $ignore->getKey() == $matrixQuestionSubQuestion->getKey()) {
                continue;
            }

            if($matrixQuestionSubQuestion->duplicate($question, [], $answerReferences) === false) {
                return false;
            }
        }

        return $question;
    }

    public function canCreateSystemRatingForAnswer($answer): bool
    {

//        if($this->subtype === 'SingleChoice'){
//            return true;
//        }

        return true;
    }

    /**
     * we expect the answer to look like
     * [
     *      subQuestionId => [answerId,answerId,answerId],
     *      subQuestionId => [answerId],
     * ]
     * in case of only one answer it might be (but shouldn't) subQuestionId => answerId
     * @param $answer
     * @return int
     */
    public function checkAnswer($answer) {
        $answers = json_decode($answer->getAttribute('json'),true);

        $score = 0;

        if(!$answers){
            return $score;
        }

        $this->matrixQuestionSubQuestions->each(function (MatrixQuestionSubQuestion $sQ) use (&$score, $answers){
            $sQAnswers = $sQ->matrixQuestionAnswers()->pluck('id');
            if(isset($answers[$sQ->getKey()])){ // is there an answer for this
                if(is_array($answers[$sQ->getKey()])){ // is the answer an array
                    $diff = $sQAnswers->diff(collect($answers[$sQ->getKey()])); // let's diff the two collections
                    if(count($diff->all()) === 0){ // if no differences then it's okay
                        $score += $sQ->score;
                    }
                } else if($sQAnswers->count() === 1 && $sQAnswers->toArray()[0] == $answers[$sQ->getKey()]){ // if the answer is a string and there's only one correct answer, check if the same
                    $score += $sQ->score;
                }
            }
        });

        if($this->allOrNothingQuestion()){
            if($score == $this->matrixQuestionAnswerSubQuestions->count()){
                return $this->score;
            } else {
                return 0;
            }
        }

        return $score;
    }

    public function isClosedQuestion(): bool
    {
        return true;
    }

    /**
     * delete both answers and subquestions
     * @return bool|void
     */
    public function deleteAnswers(){
        $this->matrixQuestionAnswers->each(function($a){
            if (!$a->delete()) {
                throw new QuestionException('Failed to delete matrix question answer', 422);
            }
        });

        $this->matrixQuestionSubQuestions->each(function($q){
            if (!$q->delete()) {
                throw new QuestionException('Failed to delete matrix question sub questions', 422);
            }
        });

        return true;
    }

    /**
     * structure should be
     * order 0 based
     * $answers = [
     *  'answers' => [
     *      [
     *          'answer' => ...,
     *          'order' => ...,
     *      ],
     *      [
     *          'answer' => ...,
     *          'order' => ...,
     *      ]
     *  ],
     *  'subQuestions' => [
     *      [
     *          'sub_question' => ...
     *          'score' => ...
     *          'order' => ...,
     *          'answers' => [{ordernr},{ordernr}]
     *      ],
     *      [
     *          'sub_question' => ...
     *          'score' => ...
     *          'order' => ...
     *          'answers' => [{ordernr},{ordernr}]
     *      ],
     *  ],
     * ]
     * @param $mainQuestion either TestQuestion or GroupQuestionQuestion
     * @param $answers array of both answers and subquestions
     * @return boolean
     * @throws \Exception
     */
    public function addAnswers($mainQuestion,$answers){
        $question = $this;
        $answerReferences = [];
        foreach($answers['answers'] as $answerDetails){
            $answer = new MatrixQuestionAnswer();

            $answer->fill($answerDetails);
            $answer->matrix_question_id = $question->getKey();
            if (!$answer->save()) {
                throw new QuestionException('Failed to create matrix question answer',422);
            }
            $answerReferences[$answerDetails['order']] = $answer;
        }

        foreach($answers['subQuestions'] as $details){
            $subQuestion = new MatrixQuestionSubQuestion();

            $subQuestion->fill($details);
            $subQuestion->matrix_question_id = $question->getKey();
            if (!$subQuestion->save()) {
                throw new QuestionException('Failed to create matrix question sub question',422);
            }

            foreach($details['answers'] as $answer) {
                if(!isset($answerReferences[$answer])){
                    throw new QuestionException('Failed to create matrix question answer sub question relation', 422);
                }
                $answerSubQuestion = new MatrixQuestionAnswerSubQuestion();
                $answerSubQuestion->matrix_question_answer_id = $answerReferences[$answer]->getkey();
                if (!$subQuestion->matrixQuestionAnswerSubQuestions()->save($answerSubQuestion)) {
                    throw new QuestionException('Failed to create matrix question answer sub question relation', 422);
                }
            }
        }

        return true;
    }

    public function isFullyAnswered(Answer $answer): bool
    {
        $givenAnswersCount = collect(json_decode($answer->json, true))->filter()->count();
        return $givenAnswersCount === $this->subQuestions->count();
    }
}
