<?php namespace tcCore;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class TestAuthor extends CompositePrimaryKeyModel
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
    protected $table = 'test_authors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_id', 'user_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['test_id', 'user_id'];

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

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public static function addAuthorToTest(Test $test, $userId)
    {
        return self::addOrRestoreAuthor($test, $userId);
    }

    public static function addExamAuthorToTest(Test $test)
    {

        if (!optional(Auth::user())->isInExamSchool()) {
            return false;
        }
        if ($test->scope != 'exam') {
            return false;
        }
        $test->testAuthors->each(function ($testAuthor) {
            $testAuthor->delete();
        });
        $examAuthorUser = AuthorsController::getCentraalExamenAuthor();
        return self::addOrRestoreAuthor($test, $examAuthorUser->getKey());
    }

    public static function addNationalItemBankAuthorToTest(Test $test)
    {

        if (!optional(Auth::user())->isInNationalItemBankSchool()) {
            return false;
        }
        if ($test->scope != 'ldt') {
            return false;
        }
        $test->testAuthors->each(function ($testAuthor) {
            $testAuthor->delete();
        });
        $nationalItemBankAuthorUser = AuthorsController::getNationalItemBankAuthor();
        return self::addOrRestoreAuthor($test, $nationalItemBankAuthorUser->getKey());
    }

    private static function addOrRestoreAuthor($test, $userId)
    {
        $testAuthor = static::withTrashed()->where('user_id', $userId)->where('test_id', $test->getKey())->first();
        if ($testAuthor === null) {
            $testAuthor = new TestAuthor(['user_id' => $userId, 'test_id' => $test->getKey()]);
            if (!$testAuthor->save()) {
                return false;
            }
        } else {
            if (!$testAuthor->restore()) {
                return false;
            }
        }
        return true;
    }

    public function scopeSchoolLocationAuthorUsers($query, $user)
    {
        if ($user->isValidExamCoordinator()) {
            return User::select(['id', 'name_first', 'name_suffix', 'name', 'school_location_id'])
                ->whereIn(
                    'id',
                    Test::select('author_id')->whereOwnerId($user->school_location_id)
                );
        }

        return User::withTrashed()->whereIn('id', // find all users part of this selection
            Teacher::withTrashed()->whereIn('subject_id', // find the teachers with these subjects
                Subject::withTrashed()->whereIn('section_id', // get all subjects belonging to the section memberships
                    $user->sections()->select('sections.id')->where('demo', 0) // get section memberships of this teacher where section is not part of the demo environment
                )->select('subjects.id')
            )->select('teachers.user_id')
        )->select('id', 'name_first', 'name_suffix', 'name', 'school_location_id')->groupBy('users.id');
    }

    public function scopeSchoolLocationAndSharedSectionsAuthorUsers($query, $user)
    {
        return User::withTrashed()->whereIn('id', // find all users part of this selection
            Teacher::withTrashed()->whereIn('subject_id', // find the teachers with these subjects
                $user->subjectsIncludingShared()->where('demo', 0)->pluck('id')
            )->select('teachers.user_id')
        )->select('id', 'name_first', 'name_suffix', 'name', 'school_location_id')->groupBy('users.id');
    }
}
