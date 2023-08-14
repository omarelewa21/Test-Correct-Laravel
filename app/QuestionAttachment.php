<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class QuestionAttachment extends CompositePrimaryKeyModel
{

    use CompositePrimaryKeyModelSoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = ['deleted_at' => 'datetime',];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'question_attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['question_id', 'attachment_id', 'options'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['question_id', 'attachment_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function question()
    {
        return $this->belongsTo('tcCore\Question');
    }

    public function attachment()
    {
        return $this->belongsTo('tcCore\Attachment');
    }

    public function duplicate($parent, $attributes, $ignore = null)
    {
        $questionAttachment = $this->replicate();
        $questionAttachment->fill($attributes);

        if ($parent instanceof Question) {
            $questionAttachment->setAttribute('attachment_id', $this->getAttribute('attachment_id'));
            if ($parent->questionAttachments()->save($questionAttachment) === false) {
                return false;
            }
        } elseif ($parent instanceof Attachment) {
            $questionAttachment->setAttribute('question_id', $this->getAttribute('question_id'));
            if ($parent->questionAttachments()->save($questionAttachment) === false) {
                return false;
            }
        } else {
            return false;
        }

        return $questionAttachment;
    }

    public function audioIsPausable()
    {
        return $this->getJsonPropertyValueBool('pausable');
    }

    public function audioIsOnlyPlayableOnce()
    {
        return $this->getJsonPropertyValueBool('play_once');
    }

    public function audioTimeoutTime()
    {
        $timeOutTime = $this->getJsonPropertyValue('timeout');
        return ($timeOutTime === 0 || empty($timeOutTime)) ? null : $timeOutTime;
    }

    public function hasAudioTimeout()
    {
        return ($this->audioTimeoutTime() > 0);
    }

    public function getJsonPropertyValueBool($propertyName)
    {
        return is_null($this->getJsonPropertyValue($propertyName)) ? false : $this->getJsonPropertyValue($propertyName);
    }

    public function getJsonPropertyValue($propertyName)
    {
        $json = null;
        if ($this->options) {
            $json = json_decode($this->options);
        }

        if ($json != null && property_exists($json, $propertyName)) {
            return $json->$propertyName;
        }

        return null;
    }

    public function audioIsPlayedOnce()
    {
        session()->put('attachment_' . $this->getSessionKey(), 1);
    }

    public function audioCanBePlayedAgain()
    {
        if (session()->get('attachment_' . $this->getSessionKey())) {
            return false;
        }
        return true;
    }

    public function audioIsNotPausableOnlyPlayableOnce()
    {
        return (!$this->audioIsPausable() && $this->audioIsOnlyPlayableOnce());
    }

    public function audioHasCurrentTime()
    {
        $sessionValue = 'attachment_' . $this->getSessionKey() . '_currentTime';
        if (session()->get($sessionValue)) {
            return session()->get($sessionValue);
        }
        return 0;
    }

    public function getAttachmentTitleShortKey(): string
    {
        if (!$this->audioCanBePlayedAgain()) {
            return 'test_take.sound_clip_played';
        }
        if ($this->audioIsOnlyPlayableOnce()) {
            return 'test_take.only_playable_once';
        }
        if (!$this->audioIsPausable()) {
            return 'test_take.cannot_pause_sound_clip';
        }
        if ($this->audioIsNotPausableOnlyPlayableOnce()) {
            return 'test_take.not_pausable_only_playable_once';
        }

        return 'test_take.sound_clip';
    }

    private function getSessionKey()
    {
        return collect($this->getKey())->join('-');
    }

}
