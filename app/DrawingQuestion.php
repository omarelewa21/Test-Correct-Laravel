<?php namespace tcCore;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use tcCore\Http\Requests\UpdateTestQuestionRequest;
use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
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
    protected $fillable = ['answer', 'grid', 'answer_svg', 'question_svg', 'grid_svg'];

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

        $question->setAttribute('uuid', Uuid::uuid4());

        if ($question->save() === false) {
            return false;
        }

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
}
