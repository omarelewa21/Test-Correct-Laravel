<?php namespace tcCore;

use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\UpdatePValueUsers;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingWizardUserStep extends BaseModel {

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
    protected $table = 'onboarding_wizard_user_steps';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','onboarding_wizard_step_id','user_id'];

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

    public function user(){
        return $this->belongsTo('tcCore\user','user_id');
    }

    public function step(){
        return $this->belongsTo('tcCore\OnboardingWizardStep','onboarding_wizard_step_id');
    }

}
