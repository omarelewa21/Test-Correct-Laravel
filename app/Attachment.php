<?php namespace tcCore;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

class Attachment extends BaseModel {

    use SoftDeletes;

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
    protected $fillable = ['type', 'title', 'description', 'text', 'link', 'json'];

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

        static::saved(function(Attachment $attachment)
        {
            if ($attachment->file instanceof UploadedFile) {
                $attachment->file->move(storage_path('attachments'), $attachment->getKey().' - '.$attachment->getAttribute('file_name'));

                $original = $attachment->getOriginalPath();
                if (File::exists($original)) {
                    File::delete($original);
                }
            }
        });

        static::deleted(function(Attachment $attachment)
        {
            if ($attachment->forceDeleting) {
                $original = $attachment->getOriginalPath();
                if (File::exists($original)) {
                    File::delete($original);
                }
            }
        });
    }

    public function getOriginalPath() {
        return ((substr(storage_path('attachments'), -1) === DIRECTORY_SEPARATOR) ? storage_path('attachments') : storage_path('attachments') . DIRECTORY_SEPARATOR) . $this->getOriginal($this->getKeyName()) . ' - ' . $this->getOriginal('file_name');
    }

    public function getCurrentPath() {
        return ((substr(storage_path('attachments'), -1) === DIRECTORY_SEPARATOR) ? storage_path('attachments') : storage_path('attachments') . DIRECTORY_SEPARATOR) . $this->getKey() . ' - ' . $this->getAttribute('file_name');
    }

    public function questionAttachments() {
        return $this->hasMany('tcCore\QuestionAttachment', 'attachment_id');
    }

    public function questions() {
        return $this->belongsToMany('tcCore\Question', 'question_attachments', 'attachment_id', 'question_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
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

        if (is_array($attributes) && array_key_exists('attachment', $attributes) && $attributes['attachment'] instanceof UploadedFile) {
            $this->fillFile($attributes['attachment']);
        }

        return $this;
    }

    public function fillFile(UploadedFile $file)
    {
        if ($file->isValid()) {
            $this->file = $file;
            $this->setAttribute('file_name', $file->getClientOriginalName());
            $this->setAttribute('file_size', $file->getSize());
            $this->setAttribute('file_extension', $file->getClientOriginalExtension());
            $this->setAttribute('file_mime_type', $file->getMimeType());
        }
    }

    public function isDirtyFile() {
        if ($this->file instanceof UploadedFile) {
            return $this->fileDiff($this->file->getPath(), $this->getOriginalPath());
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

    public function duplicate($parent, array $attributes) {
        $attachment = $this->replicate();
        $attachment->fill($attributes);

        $attachment->save();

        if ((!$this->isDirtyFile() || $attachment->file instanceof UploadedFile) && File::exists($this->getCurrentPath())) {
            File::copy($this->getCurrentPath(), $attachment->getCurrentPath());
        }

        return $attachment;
    }

    public function isUsed($ignoreRelationTo) {
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
}
