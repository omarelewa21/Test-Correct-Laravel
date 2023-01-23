<?php namespace tcCore;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Helpers\ContentSourceHelper;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Requests\UpdateTestQuestionRequest;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Lib\Question\Factory;
use tcCore\Traits\UuidTrait;

class TestQuestion extends BaseModel {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_id', 'question_id', 'order', 'maintain_position', 'discuss'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $callbacks = true;

    protected $recount = true;

    public static function boot()
    {
        parent::boot();

        static::saving(function(TestQuestion $testQuestion) {
            $testQuestion->load('test');
            return $testQuestion->test->allowChange();
        });

        static::saved(function(TestQuestion $testQuestion) {
            if ($testQuestion->doCallbacks() && ($testQuestion->getOriginal('order') != $testQuestion->getAttribute('order') || $testQuestion->getOriginal('test_id') != $testQuestion->getAttribute('test_id'))) {
                $testQuestion->test->reorder($testQuestion);
            }

            if ($testQuestion->doCallbacks() && $testQuestion->getOriginal('question_id') != $testQuestion->getAttribute('question_id') && $testQuestion->question instanceof GroupQuestion) {
                $testQuestion->test->performMetadata();
            }

            $testQuestion->test->processChange();

        });

        $metadataCallback = function(TestQuestion $testQuestion) {
            if ($testQuestion->doCallbacks()) {
                $testQuestion->test->performMetadata();
            }
        };

        static::created($metadataCallback);

        static::restored($metadataCallback);

        static::deleted($metadataCallback);
    }

    /**
     * @param $questionAttributes
     * @return TestQuestion
     * @throws QuestionException
     */
    public static function store($questionAttributes)
    {
        $question = Factory::makeQuestion($questionAttributes['type']);
        if (!$question) {
            throw new QuestionException('Failed to create question with factory', 500);
        }

        $testQuestion = new TestQuestion();
        $testQuestion->fill($questionAttributes);

        $test = $testQuestion->test;

        $qHelper = new QuestionHelper();
        $questionData = [];
        if ($questionAttributes['type'] == 'CompletionQuestion') {
            $questionData = $qHelper->getQuestionStringAndAnswerDetailsForSavingCompletionQuestion($questionAttributes['question']);
        }

        $totalData = array_merge($questionAttributes, $questionData);
        $question->fill($totalData);
        $questionInstance = $question->getQuestionInstance();
        if ($questionInstance->getAttribute('subject_id') === null) {
            $questionInstance->setAttribute('subject_id', $test->subject->getKey());
        }

        if ($questionInstance->getAttribute('education_level_id') === null) {
            $questionInstance->setAttribute('education_level_id', $test->educationLevel->getKey());
        }

        if ($questionInstance->getAttribute('education_level_year') === null) {
            $questionInstance->setAttribute('education_level_year', $test->getAttribute('education_level_year'));
        }

        if ($questionInstance->getAttribute('draft') === null) {
            $questionInstance->setAttribute('draft', $test->getAttribute('draft'));
        }
        
        if ($question->save()) {
            $testQuestion->setAttribute('question_id', $question->getKey());

            if ($testQuestion->save()) {

                if (Question::usesDeleteAndAddAnswersMethods($questionAttributes['type'])) {
//                        // delete old answers
//                        $question->deleteAnswers($question);

                    // add new answers
                    $testQuestion->question->addAnswers($testQuestion, $totalData['answers']);
                }
                $testQuestion->addCloneAttachmentsIfAppropriate($questionAttributes);
            } else {
                throw new QuestionException('Failed to create test question');
            }

        } else {
            throw new QuestionException('Failed to create question');
        }

        if (!QuestionAuthor::addAuthorToQuestion($question)) {
            throw new QuestionException('Failed to attach author to question');
        }

        return $testQuestion;
    }

    public function setCallbacks($callbacks) {
        $this->callbacks = ($callbacks === true);
    }

    public function doCallbacks() {
        return $this->callbacks;
    }

    public function test() {
        return $this->belongsTo('tcCore\Test', 'test_id');
    }

    public function question() {
        return $this->belongsTo('tcCore\Question', 'question_id');
    }

    public function addCloneAttachmentsIfAppropriate($questionAttributes)
    {
        if(array_key_exists('clone_attachments',$questionAttributes) && !empty($questionAttributes['clone_attachments']) && is_array($questionAttributes['clone_attachments'])){
            foreach($questionAttributes['clone_attachments'] as $cloneId){
                $attachment = Attachment::whereUuid($cloneId)->first();
                if(!$attachment){
                    throw new QuestionException('Could not find the corresponding attachment');
                }
                $questionAttachment = new QuestionAttachment();
                $questionAttachment->setAttribute('question_id', $this->question->getKey());
                $questionAttachment->setAttribute('attachment_id', $attachment->getKey());
                if(!$questionAttachment->save()){
                    throw new QuestionException('Failed to clone the attachments');
                }
            }
        }
    }

    public function duplicate($parent, array $attributes = [], $reorder = true) {
        $testQuestion = $this->replicate();
        $testQuestion->fill($attributes);

        $testQuestion->setAttribute('uuid', Uuid::uuid4());

        $this->duplicateQuestionsIfPublishedContent($testQuestion);

        if ($reorder === false) {
            $testQuestion->setCallbacks(false);
        }

        if ($parent->testQuestions()->save($testQuestion) === false) {
            return false;
        }

        if ($reorder === false) {
            $testQuestion->setCallbacks(true);
        }

        return $testQuestion;
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'test_id':
                    if (is_array($value)) {
                        $query->whereIn('test_id', $value);
                    } else {
                        $query->where('test_id', '=', $value);
                    }
                    break;
                case 'question_id':
                    if (is_array($value)) {
                        $query->whereIn('question_id', $value);
                    } else {
                        $query->where('question_id', '=', $value);
                    }
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
                case 'id':
                case 'order':
                    $key = $value;
                    $value = 'asc';
                    break;
                case 'asc':
                case 'desc':
                    break;
                default:
                    $value = 'asc';
            }
            switch(strtolower($key)) {
                case 'id':
                case 'order':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }

    public function duplicateQuestionsIfPublishedContent($testQuestion): void
    {
        if (in_array($testQuestion->question->scope, ContentSourceHelper::PUBLISHABLE_SCOPES)) {
            $request = new Request();
            $request->merge(['scope' => null]);

            (new TestQuestionsController)->updateFromWithin($testQuestion, $request);
        }
    }

}
