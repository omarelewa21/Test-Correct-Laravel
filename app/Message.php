<?php namespace tcCore;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\SendMessageMail;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class Message extends BaseModel {

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
    protected $table = 'messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['subject', 'message', 'read'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The receivers of the message
     * @var array
     */
    protected $to;

    /**
     * The receivers(in CC) of the message
     * @var array
     */
    protected $cc;

    /**
     * The receivers(in BCC) of the message
     * @var array
     */
    protected $bcc;

    /**
     * Mark this message read/unread
     * @var array
     */
    protected $read;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo('tcCore\User')->withTrashed();
    }

    public function messageReceivers() {
        return $this->hasMany('tcCore\MessageReceiver', 'message_id');
    }

    public static function boot()
    {
        parent::boot();

        static::saved(function(Message $message) {
            if ($message->to !== null) {
                $message->saveTo();
            }

            if ($message->cc !== null) {
                $message->saveCc();
            }

            if ($message->bcc !== null) {
                $message->saveBcc();
            }

            if ($message->read !== null) {
                $message->saveRead();
            }
        });

        static::created(function(Message $message) {
            Queue::push(new SendMessageMail($message->getKey()));
        });
    }

    private function saveTo() {
        $receiversTo = $this->messageReceivers()->withTrashed()->where('type', '=', 'TO')->get();

        $this->syncTcRelation($receiversTo, $this->to, 'user_id', function($message, $userId) {
            MessageReceiver::create(['user_id' => $userId, 'message_id' => $message->getKey(), 'type' => 'TO']);
        });

        $this->to = null;
    }

    private function saveCc() {
        $receiversCc = $this->messageReceivers()->withTrashed()->where('type', '=', 'CC')->get();

        $this->syncTcRelation($receiversCc, $this->cc, 'user_id', function($message, $userId) {
            MessageReceiver::create(['user_id' => $userId, 'message_id' => $message->getKey(), 'type' => 'CC']);
        });

        $this->cc = null;
    }

    private function saveBcc() {
        $receiversBcc = $this->messageReceivers()->withTrashed()->where('type', '=', 'BCC')->get();

        $this->syncTcRelation($receiversBcc, $this->bcc, 'user_id', function($message, $userId) {
            MessageReceiver::create(['user_id' => $userId, 'message_id' => $message->getKey(), 'type' => 'BCC']);
        });

        $this->bcc = null;
    }

    private function saveRead() {
        $this->messageReceivers()->withTrashed()->where('user_id', '=', Auth::user()->getKey())->update(['read' => $this->read]);
        $this->read = null;
    }

    public function markRead() {
        $this->read = 1;
        $this->saveRead();
    }

    public function fill(array $attributes) {
        parent::fill($attributes);

        if(array_key_exists('to', $attributes)) {
            $this->to = $attributes['to'];
        }

        if(array_key_exists('cc', $attributes)) {
            $this->cc = $attributes['cc'];
        }

        if(array_key_exists('bcc', $attributes)) {
            $this->bcc = $attributes['bcc'];
        }

        if(array_key_exists('read', $attributes)) {
            $this->read = $attributes['read'];
        }
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        if (!array_key_exists('sender_id', $filters) && !array_key_exists('receiver_id', $filters) && !array_key_exists('unread_receiver_id', $filters)) {
            $query->where(function ($query) {
                $query->where('user_id', '=', Auth::user()->getKey())
                    ->orWhereIn('id', function ($query) {
                        $query->select('message_id')->from(with(new MessageReceiver())->getTable())->where('user_id', '=', Auth::user()->getKey());
                    });
            });
        }

        foreach($filters as $key => $value) {
            switch($key) {
                case 'sender_id':
                    if (is_array($value)) {
                        $query->whereIn('user_id', $value);
                    } else {
                        $query->where('user_id', '=', $value);
                    }
                    break;
                case 'receiver_id':
                    $query->whereIn('id', function ($query) use ($value) {
                        $query->select('message_id')->from(with(new MessageReceiver())->getTable());
                        if (is_array($value)) {
                            $query->whereIn('user_id', $value);
                        } else {
                            $query->where('user_id', '=', $value);
                        }
                    });
                    break;
                case 'unread_receiver_id':
                    $query->whereIn('id', function ($query) use ($value) {
                        $query->select('message_id')
                            ->from(with(new MessageReceiver())->getTable())
                            ->where('read', '=', false);
                        if (is_array($value)) {
                            $query->whereIn('user_id', $value);
                        } else {
                            $query->where('user_id', '=', $value);
                        }
                    });
                    break;
                case 'subject':
                    $query->where('subject', 'LIKE', '%'.$value.'%');
                    break;
                case 'message':
                    $query->where('message', 'LIKE', '%'.$value.'%');
                    break;
            }
        }

        //Todo: More sorting
        foreach($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'created_at':
                case 'updated_at':
                case 'subject':
                case 'message':
                    $key = $value;
                    $value = 'asc';
                    break;
                case 'asc':
                case 'desc':
                    break;
                default:
                    $value = 'asc';
            }

            switch (strtolower($key)) {
                case 'id':
                case 'created_at':
                case 'updated_at':
                case 'subject':
                case 'message':
                    $query->orderBy($key, $value);
                    break;
            }

        }

        return $query;
    }

    public function toArray() {
        // Filter message receivers
        if (array_key_exists('messageReceivers', $this->relations) && $this->getAttribute('user_id') !== Auth::user()->getKey()) {
            $messageReceivers = $this->relations['messageReceivers']->all();

            $filteredMessageReceivers = new Collection();
            foreach($messageReceivers as $messageReceiver) {
                if ($messageReceiver->getAttribute('type') !== 'BCC' || $messageReceiver->getAttribute('user_id') == Auth::user()->getKey()) {
                    $filteredMessageReceivers->push($messageReceiver);
                }
            }

            $this->relations['messageReceivers'] = $filteredMessageReceivers;
        }

        $result = parent::toArray();

        // Restore
        if (array_key_exists('messageReceivers', $this->relations) && $this->getAttribute('user_id') !== Auth::user()->getKey()) {
            $this->relations['messageReceivers'] = $messageReceivers;
        }

        return $result;
    }


}
