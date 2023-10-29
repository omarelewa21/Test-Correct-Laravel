<?php namespace tcCore;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Scopes\AttainmentScope;
use tcCore\Traits\UuidTrait;

class Attainment extends BaseModel
{

    use SoftDeletes;
    use UuidTrait;

    const TYPE = 'ATTAINMENT';

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'attainments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['base_subject_id', 'education_level_id', 'attainment_id', 'code', 'subcode', 'subsubcode', 'description', 'status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AttainmentScope);
    }

    public static function bootWithoutGlobalScope()
    {
        parent::boot();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function baseSubject()
    {
        return $this->belongsTo('tcCore\BaseSubject');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function educationLevel()
    {
        return $this->belongsTo('tcCore\EducationLevel');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attainment()
    {
        return $this->belongsTo('tcCore\Attainment');
    }

    public function questionAttainments()
    {
        return $this->hasMany('tcCore\QuestionAttainment', 'attainment_id');
    }

    public function questions()
    {
        return $this->belongsToMany('tcCore\Question', 'question_attainments')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'status':
                    if (is_array($value)) {
                        $query->whereIn('status', $value);
                    } else {
                        $query->where('status', '=', $value);
                    }
                    break;
                case 'education_level_id':
                    if (is_array($value)) {
                        $query->whereIn('education_level_id', $value);
                    } else {
                        $query->where('education_level_id', '=', $value);
                    }
                    break;
                case 'attainment_id':
                    if (is_array($value)) {
                        $query->whereIn('attainment_id', $value);
                    } else {
                        $query->where('attainment_id', '=', $value);
                    }
                    break;
                case 'subject_id':
                    $query->whereIn('base_subject_id', function ($query) use ($value) {
                        $query->select('base_subject_id')
                            ->from(with(new Subject())->getTable());
                        if (is_array($value)) {
                            $query->whereIn('id', $value);
                        } else {
                            $query->where('id', '=', $value);
                        }
                        $query->where('deleted_at', null);
                    });
                    break;
            }
        }

        //Todo: More sorting
        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'code':
                case 'subcode':
                case 'description':
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
                case 'code':
                case 'subcode':
                case 'description':
                    $query->orderBy($key, $value);
                    break;
            }

        }

        return $query;
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function getNameAttribute()
    {
        if ($this->is_learning_goal == 1) {
            return __('student.leerdoel met nummer', ['number' => $this->getOrderNumber()]);
        }

        return __('student.eindterm met nummer', ['number' => $this->getOrderNumber()]);
    }


    /**
     * // Solution not working online in php (works directly on the sql client
    // Illuminate\Database\QueryException with message 'SQLSTATE[42000]: Syntax error or access violation: 1064 Routing query to backend failed.
    //        $orderNumber = DB::Select(
    //            DB::raw('
    //                SELECT vlg FROM
    //                (
    //                    SELECT *, @row_number := @row_number + 1  as vlg from  ' . $this->getTable() . ',
    //                    (select @row_number := 0) as x
    //                    WHERE base_subject_id = ' . $this->base_subject_id . '
    //                        '. $attaimentIdWhereClause .'
    //                        AND is_learning_goal = ' . $this->is_learning_goal . '
    //                        AND education_level_id = ' . $this->education_level_id . '
    //                    ORDER BY base_subject_id, education_level_id, is_learning_goal) as t
    //                    WHERE t.id = ' . $this->getKey()
    //                 )
    //        )[0]->vlg;
     */
    public function getOrderNumber() {
        $found = false;
        return Attainment::withoutGlobalScope(AttainmentScope::class)
            ->where([
                ['base_subject_id', $this->base_subject_id],
                ['is_learning_goal', $this->is_learning_goal],
                ['education_level_id', $this->education_level_id],
            ])->when(is_null($this->attainment_id),
                fn($query) => $query->whereNull('attainment_id'),
                fn($query) => $query->where('attainment_id', $this->attainment_id)
            )->orderByRaw(' is_learning_goal, education_level_id, code, subcode')
            ->get()
            ->filter(function ($value) use (&$found) {
                if ($found) return false;
                $found = ($this->id == $value->id);
                return true;
            })->count();
    }

    public static function getAnalysisDataForTestTake(TestTake $testTake): Collection
    {
        $participantIdsBuilder = $testTake->testParticipants()->select('id');
        $data = DB::table('p_values')
            ->leftJoin('questions', 'p_values.question_id', '=', 'questions.id')
            ->leftJoin('question_attainments', 'questions.id', '=', 'question_attainments.question_id')
            ->leftJoin('attainments', 'question_attainments.attainment_id', '=', 'attainments.id')
            ->selectRaw(
                'attainments.*,
                    sum(p_values.score) as total_score, 
                    sum(p_values.max_score) as total_max_score,count(question_attainments.question_id) as questions_per_attainment,
                    count(distinct p_values.test_participant_id) as count_testparticipants,
                    sum(p_values.score)/sum(p_values.max_score) as p_value'
            )
            ->whereIn('test_participant_id', $participantIdsBuilder)
            ->whereNull('p_values.deleted_at')
            ->whereNull('question_attainments.deleted_at')
            ->whereNull('questions.deleted_at')
            ->whereNull('attainments.deleted_at')
            ->whereNotNull('attainments.id')
            ->groupBy('attainments.id')
            ->orderBy('attainments.code', 'asc')
            ->orderBy('attainments.subcode')
            ->get();

        return Attainment::hydrate($data->toArray());
    }

    public function getStudentAnalysisDataForTestTake(TestTake $testTake): Collection
    {
        $participantIdsBuilder = $testTake->testParticipants()->select('id');
        $data = DB::table('p_values')
            ->leftJoin('questions', 'p_values.question_id', '=', 'questions.id')
            ->leftJoin('question_attainments', 'questions.id', '=', 'question_attainments.question_id')
            ->leftJoin('test_participants', 'p_values.test_participant_id', '=', 'test_participants.id')
            ->leftJoin('users', 'test_participants.user_id', '=', 'users.id')
            ->selectRaw(
                'users.name_first,users.name_suffix,users.name,users.uuid,
                    sum(p_values.score) as total_score, 
                    sum(p_values.max_score) as total_max_score,count(question_attainments.question_id) as questions_per_attainment,
                    count(distinct p_values.test_participant_id) as count_testparticipants,
                    sum(p_values.score)/sum(p_values.max_score) as p_value'
            )
            ->whereIn('test_participant_id', $participantIdsBuilder)
            ->where('question_attainments.attainment_id', $this->getKey())
            ->whereNull('p_values.deleted_at')
            ->whereNull('question_attainments.deleted_at')
            ->whereNull('questions.deleted_at')
            ->whereNull('users.deleted_at')
            ->whereNull('test_participants.deleted_at')
            ->whereNotNull('question_attainments.attainment_id')
            ->orderBy('p_value', 'desc')
            ->groupBy('test_participant_id')
            ->get();

        return User::hydrate($data->toArray());
    }
}
