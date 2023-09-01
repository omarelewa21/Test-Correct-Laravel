<?php namespace tcCore;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;
use tcCore\Services\ContentSource\FormidableService;
use tcCore\Services\ContentSource\NationalItemBankService;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\Services\ContentSourceFactory;

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

    public static function addPublishAuthorToTest(Test $test)
    {
        self::addExamAuthorToTest($test);
        // this line is here to avoid: [Warning: method_exists() expects parameter 1 to be object|string, null given]
        if ($contentService = ContentSourceFactory::makeWithTestBasedOnScope($test)) {
            if (method_exists($contentService, 'addAuthorToTest')) {
                $contentService::addAuthorToTest($test);
            }
        }
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

    public static function addExamAuthorToTest(Test $test): bool
    {
        if ($test->scope != 'exam') {
            return false;
        }
        if (!optional(Auth::user())->isInExamSchool()) {
            return false;
        }
        $test->testAuthors->each(function ($testAuthor) {
            $testAuthor->delete();
        });
        $examAuthorUser = AuthorsController::getCentraalExamenAuthor();
        return self::addOrRestoreAuthor($test, $examAuthorUser->getKey());
    }

    public static function addOrRestoreAuthor($test, $userId): bool
    {
        $testAuthor = static::withTrashed()->where('user_id', $userId)->where('test_id', $test->getKey())->first();
        if ($testAuthor === null) {
            $testAuthor = new TestAuthor(['user_id' => $userId, 'test_id' => $test->getKey()]);
            return $testAuthor->save();

        }
        return $testAuthor->restore();
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
