<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class QuestionAttachment extends CompositePrimaryKeyModel {

    use CompositePrimaryKeyModelSoftDeletes;

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

    public function question() {
        return $this->belongsTo('tcCore\Question');
    }

    public function attachment() {
        return $this->belongsTo('tcCore\Attachment');
    }

    public function duplicate($parent, $attributes, $ignore = null) {
        $questionAttachment = $this->replicate();
        $questionAttachment->fill($attributes);

        if($parent instanceof Question) {
            $questionAttachment->setAttribute('attachment_id', $this->getAttribute('attachment_id'));
            if ($parent->questionAttachments()->save($questionAttachment) === false) {
                return false;
            }
        } elseif($parent instanceof Attachment) {
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
        session()->put('attachment_'.$this->getSessionKey(), 1);
    }

    public function audioCanBePlayedAgain()
    {
        if (session()->get('attachment_'.$this->getSessionKey())) {
            return false;
        }
        return true;
    }

    public function audioHasCurrentTime()
    {
        $sessionValue = 'attachment_'.$this->getSessionKey().'_currentTime';
        if (session()->get($sessionValue)) {
            return session()->get($sessionValue);
        }
        return 0;
    }

    private function getSessionKey() {
        return collect($this->getKey())->join('-');
    }

}
