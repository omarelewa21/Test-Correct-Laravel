<?php namespace tcCore;

use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\UpdatePValueUsers;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;

class OnboardingWizardStep extends BaseModel
{

    public $incrementing = false;
    protected $keyType = 'string';

    use SoftDeletes;
    use GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'onboarding_wizard_steps';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'onboarding_wizard_id',
        'parent_id',
        'title',
        'action',
        'action_content',
        'displayorder',
        'confetti_time_out',
        'confetti_max_count',
        'knowledge_base_action',
    ];

    protected $appends = ['done'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

    }

    public function sub()
    {
        return $this->hasMany('tcCore\OnboardingWizardStep', 'parent_id')->orderBy('displayorder', 'asc');;
    }

    public function main()
    {
        return $this->belongsTo('tcCore\OnboardingWizardStep', 'parent_id');
    }

    public function setDoneAttribute($value)
    {
        $this->attributes['done'] = (bool)$value;
    }

    public function getDoneAttribute()
    {
        return (bool)$this->attributes['done'];
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
