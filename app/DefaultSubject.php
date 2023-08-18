<?php namespace tcCore;

use Closure;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class DefaultSubject extends BaseModel
{

    use UuidTrait;
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'default_subjects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'abbreviation', 'base_subject_id', 'default_section_id', 'education_levels','demo'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [
        'demo' => 'boolean',
        'uuid' => EfficientUuid::class,
    ];

    public function baseSubject()
    {
        return $this->belongsTo(BaseSubject::class);
    }

    public function defaultSection()
    {
        return $this->belongsTo(DefaultSection::class);
    }

}
