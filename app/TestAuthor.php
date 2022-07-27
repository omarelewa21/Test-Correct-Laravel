<?php namespace tcCore;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Jobs\CountTeacherQuestions;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class TestAuthor extends CompositePrimaryKeyModel {

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

    public function test() {
        return $this->belongsTo(Test::class);
    }

    public function user() {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public static function addAuthorToTest(Test $test, $userId) {
        return self::addOrRestoreAuthor($test,$userId);
    }

    public static function addExamAuthorToTest(Test $test) {

        if(!optional(Auth::user())->isInExamSchool()){
            return false;
        }
        if($test->scope!='exam'){
            return false;
        }
        $test->testAuthors->each(function ($testAuthor){
            $testAuthor->delete();
        });
        $examAuthorUser = AuthorsController::getCentraalExamenAuthor();
        return self::addOrRestoreAuthor($test,$examAuthorUser->getKey());
    }

    public static function addNationalItemBankAuthorToTest(Test $test) {

        if(!optional(Auth::user())->isInNationalItemBankSchool()){
            return false;
        }
        if($test->scope!='ldt'){
            return false;
        }
        $test->testAuthors->each(function ($testAuthor){
            $testAuthor->delete();
        });
        $nationalItemBankAuthorUser = AuthorsController::getNationalItemBankAuthor();
        return self::addOrRestoreAuthor($test,$nationalItemBankAuthorUser->getKey());
    }

    private static function addOrRestoreAuthor($test,$userId)
    {
        $testAuthor = static::withTrashed()->where('user_id', $userId)->where('test_id', $test->getKey())->first();
        if ($testAuthor === null) {
            $testAuthor = new TestAuthor(['user_id' => $userId, 'test_id' => $test->getKey()]);
            if (!$testAuthor->save()) {
                return false;
            }
        } else {
            if(!$testAuthor->restore()) {
                return false;
            }
        }
        return true;
    }
}
