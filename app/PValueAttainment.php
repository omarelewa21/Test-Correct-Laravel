<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class PValueAttainment extends CompositePrimaryKeyModel {

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
    protected $table = 'p_value_attainments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['p_value_id', 'attainment_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['p_value_id', 'attainment_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function pValue() {
        return $this->belongsTo('tcCore\PValue');
    }

    public function attainment() {
        return $this->belongsTo('tcCore\Attainment');
    }
}