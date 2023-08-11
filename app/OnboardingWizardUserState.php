<?php namespace tcCore;

use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\UpdatePValueUsers;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingWizardUserState extends BaseModel
{

    public $incrementing = false;
    protected $keyType = 'string';

    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'onboarding_wizard_user_states';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'show', 'onboarding_wizard_id', 'active_step'];

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

    public function wizard()
    {
        return $this->belongsTo('tcCore\OnboardingWizard', 'onboarding_wizard_id');
    }

    public function user()
    {
        return $this->belongsTo('tcCore\user', 'user_id');
    }
}
