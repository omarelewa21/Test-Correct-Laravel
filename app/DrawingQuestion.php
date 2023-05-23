<?php namespace tcCore;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Scopes\ArchivedScope;
use tcCore\Traits\UuidTrait;

class DrawingQuestion extends Question implements QuestionInterface {

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
    protected $table = 'drawing_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'answer',
        'grid',
//        'answer_svg',
//        'question_svg',
        'grid_svg',
        'zoom_group',
//        'question_preview',
//        'question_correction_model'
        'svg_date_updated', // this field was introduced to force a duplicate when the svg is updated;
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * @var UploadedFile
     */
    protected $file;

    public static function boot()
    {
        parent::boot();

        static::saved(function(DrawingQuestion $drawingQuestion)
        {
            if ($drawingQuestion->file instanceof UploadedFile) {
                $original = $drawingQuestion->getOriginalBgPath();
                if (File::exists($original)) {
                    File::delete($original);
                }

                $drawingQuestion->file->move(storage_path('drawing_question_bgs'), $drawingQuestion->getKey().' - '.$drawingQuestion->getAttribute('bg_name'));
            }
        });

        static::deleted(function(DrawingQuestion $drawingQuestion)
        {
            if ($drawingQuestion->forceDeleting) {
                $original = $drawingQuestion->getOriginalBgPath();
                if (File::exists($original)) {
                    File::delete($original);
                }
            }
        });
    }

    public function getOriginalBgPath() {
        return ((substr(storage_path('drawing_question_bgs'), -1) === DIRECTORY_SEPARATOR) ? storage_path('drawing_question_bgs') : storage_path('drawing_question_bgs') . DIRECTORY_SEPARATOR) . $this->getOriginal($this->getKeyName()) . ' - ' . $this->getOriginal('bg_name');
    }

    public function getCurrentBgPath() {
        return ((substr(storage_path('drawing_question_bgs'), -1) === DIRECTORY_SEPARATOR) ? storage_path('drawing_question_bgs') : storage_path('drawing_question_bgs') . DIRECTORY_SEPARATOR) . $this->getKey() . ' - ' . $this->getAttribute('bg_name');
    }

    public function loadRelated()
    {
        // Open questions do not have related stuff, so this does nothing!
    }


    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {

        parent::fill($attributes);
        if (is_array($attributes) && array_key_exists('bg', $attributes) && $attributes['bg'] instanceof UploadedFile) {
            $this->fillFileBg($attributes['bg']);
        }

        return $this;
    }

    public function fillFileBg(UploadedFile $file)
    {
        if ($file->isValid()) {
            $this->file = $file;
            $this->setAttribute('bg_name', $file->getClientOriginalName());
            $this->setAttribute('bg_size', $file->getSize());
            $this->setAttribute('bg_extension', $file->getClientOriginalExtension());
            $this->setAttribute('bg_mime_type', $file->getMimeType());
        }
    }

    public function isDirtyFile() {
        if(is_null($this->file)){
            return false;
        }
        if(!file_exists($this->file->getPath())&&!file_exists($this->getOriginalBgPath())){
            return false;
        }
        if(file_exists($this->file->getPath())&&!file_exists($this->getOriginalBgPath())){
            return false;
        }
        if(!file_exists($this->file->getPath())&&file_exists($this->getOriginalBgPath())){
            return false;
        }
        if ($this->file instanceof UploadedFile) {
            return $this->fileDiff($this->file->getPath(), $this->getOriginalBgPath());
        } else {
            return false;
        }
    }

    protected function fileDiff($a, $b) {
        // Check if filesize is different
        if(filesize($a) !== filesize($b))
            return false;

        // Check if content is different
        $ah = fopen($a, 'rb');
        $bh = fopen($b, 'rb');

        $result = true;
        while(!feof($ah))
        {
            if(fread($ah, 8192) != fread($bh, 8192))
            {
                $result = false;
                break;
            }
        }

        fclose($ah);
        fclose($bh);

        return $result;
    }

    public function duplicate(array $attributes, $ignore = null) {
        $question = $this->replicate();

        $question->parentInstance = $this->parentInstance->duplicate($attributes, $ignore);
        if ($question->parentInstance === false) {
            return false;
        }

        $question->fill($attributes);

        $newQuestionUuid = Uuid::uuid4();
        $question->setAttribute('uuid', $newQuestionUuid);

        if ($question->save() === false) {
            return false;
        }

        (new SvgHelper($this->uuid))->rename($newQuestionUuid);

        if (File::exists($this->getCurrentBgPath())) {
            File::copy($this->getCurrentBgPath(), $question->getCurrentBgPath());
        }

        return $question;
    }

    public function canCheckAnswer() {
        return false;
    }

    public function checkAnswer($answer) {
        return false;
    }

    public function needsToBeUpdated($request)
    {
        if($this->isDirtyFile()){
            return true;
        }
        return parent::needsToBeUpdated($request);
    }

    public function getBackgroundImage()
    {
        $backgroundImage = null;
        if($this->bg_name != null ) {
            $backgroundImage = base64_encode(file_get_contents($this->getCurrentBgPath()));

            if (!$backgroundImage) {
                return null;
            }

            if (!Str::contains($backgroundImage, ';base64,')) {
                $backgroundImage = 'data:' . $this->bg_mime_type . ';base64,' . $backgroundImage;
            }
        }

        return $backgroundImage;
    }

    public function question() {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public static function getEmbeddedFontForSVG()
    {
        return '@font-face{font-family:"Nunito";src:url(data:font/woff;base64,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) format("woff");font-weight:normal;font-style:normal;}';
    }

    public function isOldDrawingQuestion()
    {
        return filled($this->question->answer) && blank($this->question->zoom_group);
    }

    public function canAnswerModelBeSeen(User $user): bool
    {
        if (!$user->isA('Student')) {
            return true;
        }

        /* Check if student has an active review session which includes this question. */
        return TestTake::withoutGlobalScope(ArchivedScope::class)
            ->join(
                'test_participants',
                'test_takes.id',
                '=',
                'test_participants.test_take_id'
            )
            ->join(
                'test_questions',
                'test_takes.test_id',
                '=',
                'test_questions.test_id'
            )
            ->where('test_participants.user_id', $user->getKey())
            ->where('test_questions.question_id', $this->getKey())
            ->where('test_takes.show_results', '>', now())
            ->exists();
    }
}
