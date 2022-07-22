<?php namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Support\Str;
use Livewire\TemporaryUploadedFile;
use Monolog\Handler\IFTTTHandler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use tcCore\Traits\UuidTrait;

class Attachment extends BaseModel
{

    use SoftDeletes, UuidTrait;

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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type', 'title', 'description', 'text', 'link', 'json', 'question_closed'];

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

        static::saved(function (Attachment $attachment) {
            if ($attachment->file instanceof TemporaryUploadedFile) {
                rename(
                    storage_path('attachments/'.$attachment->getAttribute('file_name')),
                    storage_path('attachments/'.$attachment->getKey().' - '.$attachment->getAttribute('file_name'))
                );
                $original = $attachment->getOriginalPath();
                if (File::exists($original)) {
                    File::delete($original);
                }
            } else {
                if ($attachment->file instanceof UploadedFile) {
                    $attachment->file->move(storage_path('attachments'),
                        $attachment->getKey().' - '.$attachment->getAttribute('file_name'));
                    $original = $attachment->getOriginalPath();
                    if (File::exists($original)) {
                        File::delete($original);
                    }
                }
            }
        });

        static::deleted(function (Attachment $attachment) {
            if ($attachment->forceDeleting) {
                $original = $attachment->getOriginalPath();
                if (File::exists($original)) {
                    File::delete($original);
                }
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function getOriginalPath()
    {
        return ((substr(storage_path('attachments'),
                    -1) === DIRECTORY_SEPARATOR) ? storage_path('attachments') : storage_path('attachments').DIRECTORY_SEPARATOR).$this->getOriginal($this->getKeyName()).' - '.$this->getOriginal('file_name');
    }

    public function getCurrentPath()
    {
        return ((substr(storage_path('attachments'),
                    -1) === DIRECTORY_SEPARATOR) ? storage_path('attachments') : storage_path('attachments').DIRECTORY_SEPARATOR).$this->getKey().' - '.$this->getAttribute('file_name');
    }

    public function questionAttachments()
    {
        return $this->hasMany('tcCore\QuestionAttachment', 'attachment_id');
    }

    public function questions()
    {
        return $this->belongsToMany('tcCore\Question', 'question_attachments', 'attachment_id',
            'question_id')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
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

        if (is_array($attributes) && array_key_exists('attachment',
                $attributes) && $attributes['attachment'] instanceof UploadedFile) {
            $this->fillFile($attributes['attachment']);
        }

        return $this;
    }

    public function fillFile(UploadedFile $file)
    {
        if ($file->isValid()) {
            $this->file = $file;
            $this->setAttribute('file_name', $file->getClientOriginalName());
            if ($file instanceof TemporaryUploadedFile) {
                $this->setAttribute('file_name', $file->hashName());
            }

            $this->setAttribute('file_size', $file->getSize());
            $this->setAttribute('file_extension', $file->getClientOriginalExtension());
            $this->setAttribute('file_mime_type', $file->getMimeType());
        }
    }

    public function isDirtyFile()
    {
        if ($this->file instanceof UploadedFile) {
            return $this->fileDiff($this->file->getPath(), $this->getOriginalPath());
        } else {
            return false;
        }
    }

    protected function fileDiff($a, $b)
    {
        // Check if filesize is different
        if (filesize($a) !== filesize($b)) {
            return false;
        }

        // Check if content is different
        $ah = fopen($a, 'rb');
        $bh = fopen($b, 'rb');

        $result = true;
        while (!feof($ah)) {
            if (fread($ah, 8192) != fread($bh, 8192)) {
                $result = false;
                break;
            }
        }

        fclose($ah);
        fclose($bh);

        return $result;
    }

    public function duplicate($parent, array $attributes)
    {
        $attachment = $this->replicate();
        $attachment->fill($attributes);

        $attachment->save();

        if ((!$this->isDirtyFile() || $attachment->file instanceof UploadedFile) && File::exists($this->getCurrentPath())) {
            File::copy($this->getCurrentPath(), $attachment->getCurrentPath());
        }

        return $attachment;
    }

    public function isUsed($ignoreRelationTo)
    {
        $uses = $this->questionAttachments()->withTrashed();

        if ($ignoreRelationTo instanceof Question) {
            $ignoreRelationTo->where('question_id', '!=', $ignoreRelationTo->getKey());
        }

        if ($ignoreRelationTo instanceof QuestionAttachment) {
            $ignoreRelationTo->where('question_id', '!=', $ignoreRelationTo->getAttribute('question_id'));
        }

        return $uses->count() > 0;
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        return $query;
    }

    public function getVideoLink()
    {
        $youtubeRegex = "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))(?<video_id>[^\?&\"'>]+)/";
        $vimeoRegex = "/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*(?<video_id>[0-9]{6,11})[?]?.*/";

        preg_match($youtubeRegex, $this->link, $matches);
        if (!empty($matches['video_id'])) {
            $parts = parse_url($this->link);
            $t = 0;
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
                switch (true) {
                    case isset($query['t']):
                        $t = $query['t'];
                        break;
                    case isset($query['start']):
                        $t = $query['start'];
                        break;
                }
            }
            return sprintf('https://www.youtube.com/embed/%s?rel=0&start=%d', $matches['video_id'], $t);
        }

        preg_match($vimeoRegex, $this->link, $matches);
        if (!empty($matches['video_id'])) {
            return 'https://player.vimeo.com/video/'.$matches['video_id'];
        }

        return false;
    }

    public function isAccessableFrom(Answer $answer)
    {
        if (!$answer->testParticipant->testTakeOpenForInteraction()) {
            return false;
        }

        return $this->isPartOfQuestionForThisAnswer($answer);
    }

    private function isPartOfQuestionForThisAnswer($answer)
    {
        $question = $answer->question;
        if ($this->questionAttachments->pluck('question_id')->contains($question->getKey())) {
            return true;
        }
        $testId = $answer->testParticipant->testTake->test->getKey();
        return $this->questionAttachments->pluck('question_id')->contains($question->getGroupQuestionIdByTest($testId));
    }

    public function audioIsPausable()
    {
        return $this->getJsonPropertyValueBool('pausable');
    }

    public function audioOnlyPlayOnce()
    {
        return $this->getJsonPropertyValueBool('play_once');
    }

    public function audioTimeoutTime()
    {
        $timeOutTime = $this->getJsonPropertyValue('timeout');
        return ($timeOutTime===0||empty($timeOutTime))?null:$timeOutTime;
    }

    public function hasAudioTimeout()
    {
        if($this->audioTimeoutTime()>0){
            return true;
        }
        return false;
    }

    public function getJsonPropertyValueBool($propertyName){
        return is_null($this->getJsonPropertyValue($propertyName))?false:$this->getJsonPropertyValue($propertyName);
    }

    public function getJsonPropertyValue($propertyName)
    {
        $json = null;
        if ($this->json) {
            $json = json_decode($this->json);
        }

        if ($json != null && property_exists($json, $propertyName)) {
            return $json->$propertyName;
        }

        return null;
    }

    public function audioIsPlayedOnce()
    {
        session()->put('attachment_'.$this->getKey(), 1);
    }

    public function audioCanBePlayedAgain()
    {
        if (session()->get('attachment_'.$this->getKey())) {
            return false;
        }
        return true;
    }

    public function audioHasCurrentTime()
    {
        $sessionValue = 'attachment_'.$this->getKey().'_currentTime';
        if (session()->get($sessionValue)) {
            return session()->get($sessionValue);
        }
        return 0;
    }

    public function getFileType()
    {
        if ($this->type == 'video') {
            return 'video';
        }
        $type = collect(explode('/', $this->file_mime_type))->first();

        if ($type == 'application') {
            return 'pdf';
        }

        return $type;
    }

    public static function getVideoHost($link)
    {
        $youtube = collect(['youtube.com', 'youtu.be']);
        $vimeo = collect(['vimeo.com']);
        $host = null;
        $link = is_array($link) ? $link[0] : $link;

        $youtube->each(function ($opt) use ($link, &$host) {
            if (Str::contains($link, $opt)) {
                $host = 'youtube';
            }
        });

        $vimeo->each(function ($opt) use ($link, &$host) {
            if (Str::contains($link, $opt)) {
                $host = 'vimeo';
            }
        });

        return $host;
    }
}
