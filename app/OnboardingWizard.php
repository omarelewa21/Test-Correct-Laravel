<?php namespace tcCore;

use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\UpdatePValueUsers;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class OnboardingWizard extends BaseModel {

    const VIDEO = 'video';
    const TOUR = 'tour';
    const BUTTON_DONE = 'button_done';
    public $incrementing = false;
    protected $keyType = 'string';

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'onboarding_wizards';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','title','role_id','active'];

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

    public function steps(){
        return $this->hasMany('tcCore\OnboardingWizardStep','onboarding_wizard_id');
    }

    public function mainSteps()
    {
        return $this->hasMany(OnboardingWizardStep::class)->whereNull('parent_id')->orderBy('displayorder','asc');
    }


}
