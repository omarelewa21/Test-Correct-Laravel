<?php namespace tcCore;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Closure;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Http\Enums\SystemLanguage;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\ImportHelper;
use tcCore\Http\Helpers\GlobalStateHelper;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Http\Livewire\Account\UserData;
use tcCore\Jobs\CountSchoolActiveTeachers;
use tcCore\Jobs\CountSchoolLocationActiveTeachers;
use tcCore\Jobs\CountSchoolLocationQuestions;
use tcCore\Jobs\CountSchoolLocationStudents;
use tcCore\Jobs\CountSchoolLocationTests;
use tcCore\Jobs\CountSchoolLocationTestsTaken;
use tcCore\Jobs\CountSchoolQuestions;
use tcCore\Jobs\CountSchoolTests;
use tcCore\Jobs\CountSchoolTestsTaken;
use tcCore\Jobs\SendOnboardingWelcomeMail;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\StatisticsRepository;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Traits\ExamCoordinator;
use tcCore\Traits\HasFeatureSettings;
use tcCore\Traits\UuidTrait;
use Facades\tcCore\Http\Controllers\PreviewLaravelController;

class User extends BaseModel implements AuthenticatableContract, CanResetPasswordContract, AccessCheckable
{

    use Authenticatable,
        SoftDeletes,
        Authorizable,
        CanResetPassword,
        ExamCoordinator,
        UuidTrait,
        HasFeatureSettings;

    const MIN_PASSWORD_LENGTH = 8;

    protected $casts = [
        'uuid'               => EfficientUuid::class,
        'intense'            => 'boolean',
        'is_examcoordinator' => 'boolean',
        'password_expiration_date' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    protected $appends = ['has_text2speech', 'active_text2speech', 'external_id'];

    protected $uniqueJobs = [];

    const STUDENT_IMPORT_EMAIL_PATTERN = 's_%d@test-correct.nl';
    const TEACHER_IMPORT_EMAIL_PATTERN = 't_%d@test-correct.nl';
    const GUEST_ACCOUNT_EMAIL_PATTERN = 'guest_%d@test-correct.nl';

    const STUDENT_IMPORT_PASSWORD_PATTERN = 'S%dTC#2014';
    const TEACHER_IMPORT_PASSWORD_PATTERN = 'T%dTC#2014';
    const USER_SETTINGS_SESSION_KEY = 'UserSettings';

    const FEATURE_SETTING_ENUM = UserFeatureSettingEnum::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sales_organization_id', 'school_id', 'school_location_id', 'username', 'name_first', 'name_suffix', 'name',
        'password', 'external_id', 'gender', 'time_dispensation', 'text2speech', 'abbreviation', 'note', 'demo',
        'invited_by', 'account_verified', 'test_take_code_id', 'guest', 'send_welcome_email', 'is_examcoordinator', 'is_examcoordinator_for', 'password_expiration_date','has_package'
    ];


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'session_hash', 'api_key', 'login_logs'];

    /**
     * @var string in case of external id which needs to be updated
     */
    protected $updateExternalId;

    /**
     * @var string in case of school location id which needs to be updated
     */
    protected $updateSchoolLocationId;

    /**
     * @var array Array with school class IDs of which this user is student, for saving
     */
    protected $studentSchoolClasses;

    /**
     * @var array Array with school class IDs of which this user is manager, for saving
     */
    protected $managerSchoolClasses;

    /**
     * @var array Array with school class IDs of which this user is mentor, for saving
     */
    protected $mentorSchoolClasses;

    /**
     * @var array Array with role IDs, for saving
     */
    protected $userRoles;

    /**
     * @var array Array with parent user IDs, for saving
     */
    protected $studentParents;

    /**
     * @var array Array with student user IDs, for saving
     */
    protected $studentParentsOf;


    /**
     * @var
     */
    protected $profileImage;

    public static function createTeacher($data)
    {
        if (!is_array($data)) {
            throw new \Exception('Should provide an array with valid data');
        }

        if (!array_key_exists('user_roles', $data) || !is_array($data['user_roles'])) {
            $data['user_roles'] = [];
        }

        $data['user_roles'] = array_unique(
            array_merge(
                $data['user_roles'], [1]
            )
        );

        $user = (new Factory(new self))->generate($data);
        $user->save();
        return $user;
    }

    public static function getPasswordLengthRule()
    {
        return 'min:' . User::MIN_PASSWORD_LENGTH;
    }

    public function fill(array $attributes)
    {
        // note when called from seeder this fill method fails because it gets called with several
        // attributes that are not on the database but are transformable to other fields
        // or relations. They will end up in the insert query when guarding is off.
        self::reguard();

        $this->fillFeatureSettings($attributes);

        parent::fill($attributes);

        if (array_key_exists('external_id', $attributes)) {
            $this->updateExternalId = $attributes['external_id'];
        }
        if (array_key_exists('school_location_id', $attributes)) {
            $this->updateSchoolLocationId = $attributes['school_location_id'];
        }

        if (array_key_exists('student_school_classes', $attributes)) {
            $this->studentSchoolClasses = $attributes['student_school_classes'];
        } elseif (array_key_exists('add_student_school_class',
                $attributes) || array_key_exists('delete_student_school_class', $attributes)) {
            $this->studentSchoolClasses = $this->students()->pluck('class_id')->all();
            if (array_key_exists('add_student_school_class', $attributes)) {
                array_push($this->studentSchoolClasses, $attributes['add_student_school_class']);
            }

            if (array_key_exists('delete_student_school_class', $attributes)) {
                if (($key = array_search($attributes['delete_student_school_class'],
                        $this->studentSchoolClasses)) !== false) {
                    unset($this->studentSchoolClasses[$key]);
                }
            }
        }

        if (array_key_exists('manager_school_classes', $attributes)) {
            $this->managerSchoolClasses = $attributes['manager_school_classes'];
        } elseif (array_key_exists('add_manager_school_class',
                $attributes) || array_key_exists('delete_manager_school_class', $attributes)) {
            $this->managerSchoolClasses = $this->managers()->pluck('school_class_id')->all();
            if (array_key_exists('add_manager_school_class', $attributes)) {
                array_push($this->managerSchoolClasses, $attributes['add_manager_school_class']);
            }

            if (array_key_exists('delete_manager_school_class', $attributes)) {
                if (($key = array_search($attributes['delete_manager_school_class'],
                        $this->managerSchoolClasses)) !== false) {
                    unset($this->managerSchoolClasses[$key]);
                }
            }
        }

        if (array_key_exists('mentor_school_classes', $attributes)) {
            $this->mentorSchoolClasses = $attributes['mentor_school_classes'];
        } elseif (array_key_exists('add_mentor_school_class',
                $attributes) || array_key_exists('delete_mentor_school_class', $attributes)) {
            $this->mentorSchoolClasses = $this->mentors()->pluck('school_class_id')->all();
            if (array_key_exists('add_mentor_school_class', $attributes)) {
                array_push($this->mentorSchoolClasses, $attributes['add_mentor_school_class']);
            }

            if (array_key_exists('delete_mentor_school_class', $attributes)) {
                if (($key = array_search($attributes['delete_mentor_school_class'],
                        $this->mentorSchoolClasses)) !== false) {
                    unset($this->mentorSchoolClasses[$key]);
                }
            }
        }

        if (array_key_exists('student_parents', $attributes)) {
            $this->studentParents = $attributes['student_parents'];
        } elseif (array_key_exists('add_student_parent', $attributes) || array_key_exists('delete_student_parent',
                $attributes)) {
            $this->studentParents = $this->studentParents()->pluck('student_parent_id')->all();
            if (array_key_exists('add_student_parent', $attributes)) {
                array_push($this->studentParents, $attributes['add_student_parent']);
            }

            if (array_key_exists('delete_student_parent', $attributes)) {
                if (($key = array_search($attributes['delete_student_parent'], $this->studentParents)) !== false) {
                    unset($this->studentParents[$key]);
                }
            }
        }

        if (array_key_exists('student_parents_of', $attributes)) {
            $this->studentParentsOf = $attributes['student_parents_of'];
        } elseif (array_key_exists('add_student_parent_of', $attributes) || array_key_exists('delete_student_parent_of',
                $attributes)) {
            $this->studentParentsOf = $this->studentParentsOf()->pluck('user_id')->all();
            if (array_key_exists('add_student_parent_of', $attributes)) {
                array_push($this->studentParentsOf, $attributes['add_student_parent_of']);
            }

            if (array_key_exists('delete_student_parent_of', $attributes)) {
                if (($key = array_search($attributes['delete_student_parent_of'], $this->studentParentsOf)) !== false) {
                    unset($this->studentParentsOf[$key]);
                }
            }
        }

        if (array_key_exists('user_roles', $attributes)) {
            $this->userRoles = $attributes['user_roles'];
        } elseif (array_key_exists('add_user_role', $attributes) || array_key_exists('delete_user_role', $attributes)) {
            $this->userRoles = $this->userRoles()->pluck('role_id')->all();
            if (array_key_exists('add_user_role', $attributes)) {
                array_push($this->userRoles, $attributes['add_user_role']);
            }

            if (array_key_exists('delete_user_role', $attributes)) {
                if (($key = array_search($attributes['delete_user_role'], $this->userRoles)) !== false) {
                    unset($this->userRoles[$key]);
                }
            }
        }

        if (is_array($attributes) && array_key_exists('profile_image',
                $attributes) && $attributes['profile_image'] instanceof UploadedFile) {
            $this->fillFileProfileImage($attributes['profile_image']);
        }
    }

    public function text2SpeechDetails()
    {
        return $this->hasOne(Text2Speech::class);
    }

    public function text2SpeechLog()
    {
        return $this->hasMany(Text2SpeechLog::class);
    }

    public function hasText2Speech()
    {
        return (bool)$this->text2speech;
    }

    public function hasActiveText2Speech()
    {
        if (!$this->hasText2Speech()) {
            return false;
        }
        return (bool)$this->text2SpeechDetails->active;
    }

    public function getHasText2speechAttribute()
    {
        return $this->hasText2Speech();
    }

    public function getActiveText2speechAttribute()
    {
        return $this->hasActiveText2Speech();
    }

    public function getUserTableExternalIdAttribute()
    {
        if (!array_key_exists('external_id', $this->attributes)) {
            return null;
        }
        return $this->attributes['external_id'];
    }

    public function getExternalIdAttribute()
    {
        if ($this->isA('Teacher')) {
            $value = DB::table('school_location_user')
                ->where('school_location_id', $this->school_location_id)
                ->where('user_id', $this->getKey())
                ->value('external_id');
            if ($value) {
                return $value;
            }
        }

        return array_key_exists('external_id', $this->attributes) ? $this->attributes['external_id'] : '';
    }

    public function eckidFromRelation()
    {
        return $this->hasOne(EckidUser::class);
    }

    public function getEckidAttribute()
    {
//        $passphrase = config('custom.encrypt.eck_id_passphrase');
//        $iv = config('custom.encrypt.eck_id_iv');
//        $method = 'aes-256-cbc';
        $eckid = '';
        if (!is_null($this->eckidFromRelation)) {
            $eckid = Crypt::decryptString($this->eckidFromRelation->eckid);
        }
        return $eckid;
//        return openssl_decrypt(base64_decode($eckid), $method, $passphrase, OPENSSL_RAW_DATA, $iv);
    }

    public function setEckidAttribute($eckid)
    {
        if (!$eckid) {
            $this->removeEckId();
            return;
        }

        $eckIdUser = $this->eckidFromRelation ?: new EckIdUser;
        $eckIdUser->eckid = Crypt::encryptString($eckid);
        $eckIdUser->eckid_hash = md5($eckid);
        $this->eckidFromRelation()->save($eckIdUser);
    }

    public function updateExternalIdWithSchoolLocation($externalId, $schoolLocationId)
    {
        $handled = false;

        foreach ($this->allowedSchoolLocations as $schoolLocation) {
            if ($schoolLocation->id != $schoolLocationId) {
                continue;
            }
            if ($schoolLocation->pivot->external_id == $externalId) {
                $handled = true;
                break;
            }
            $this->allowedSchoolLocations()->updateExistingPivot($schoolLocation->id, ['external_id' => $externalId]);
            $handled = true;
            break;
        }
        if (!$handled) {
            $this->allowedSchoolLocations()->attach([$schoolLocationId => ['external_id' => $externalId]]);
        }
    }

    public function scopeFindByEckidAndSchoolLocationIdForTeacher($query, $eckid, $school_location_id)
    {
        $list = DB::table('eckid_user')->where('eckid_hash', md5($eckid))->get();

        $record = $list->first(function ($record) use ($eckid, $school_location_id) {
            if (Crypt::decryptString($record->eckid) === $eckid) {
                // user should be part of this school_location
                $user = User::find($record->user_id);
                if (!$user) {
                    return false;
                }
                return $user->allowedSchoolLocations->contains($school_location_id);
            }
            return false;
        });

        // return empty if user_id was not found;
        $user_id = 0;

        if ($record) {
            $user_id = $record->user_id;
        }

        return $query->select('users.*')->where('id', $user_id);
    }

    public function scopeFindByEckidAndSchoolLocationIdForUser($query, $eckid, $school_location_id)
    {
        $list = DB::table('eckid_user')->where('eckid_hash', md5($eckid))->get();

        $record = $list->first(function ($record) use ($eckid, $school_location_id) {
            if (Crypt::decryptString($record->eckid) === $eckid) {
                // user should be part of this school_location
                $user = User::find($record->user_id);
                if (null === $user) {
                    $message = (sprintf('THIS SHOULD NOT HAPPEN (did found eckid but no user): Can not find user for id %d', $record->user_id));
                    Bugsnag::notifyException(new \Exception($message));
                    return false;
                }
                return $user->school_location_id === $school_location_id;
            }
            return false;
        });

        // return empty if user_id was not found;
        $user_id = 0;

        if ($record) {
            $user_id = $record->user_id;
        }

        return $query->select('users.*')->where('id', $user_id);
    }

    public function scopeFilterByEckid($query, $eckid)
    {
        $list = DB::table('eckid_user')->where('eckid_hash', md5($eckid))->get();

        $records = $list->filter(function ($record) use ($eckid) {
            return Crypt::decryptString($record->eckid) === $eckid;
        });

        // return empty if user_id was not found;
        $user_ids = [];

        if ($records->count()) {
            $user_ids = $records->map(function ($u) {
                return $u->user_id;
            });
        }

        return $query->select('users.*')->whereIn('id', $user_ids);
    }

    public function scopeFindByEckid($query, $eckid)
    {
        $list = DB::table('eckid_user')->where('eckid_hash', md5($eckid))->get();

        $record = $list->first(function ($record) use ($eckid) {
            return Crypt::decryptString($record->eckid) === $eckid;
        });

        // return empty if user_id was not found;
        $user_id = 0;

        if ($record) {
            $user_id = $record->user_id;
        }

        return $query->select('users.*')->where('id', $user_id);
    }

    public function getIsTempTeacher()
    {
        if (!$this->schoolLocation) {
            return false;
        }
        return ($this->isA('Teacher') && $this->schoolLocation->getKey() == SchoolHelper::getTempTeachersSchoolLocation()->getKey());
    }


    public function getloginLogCount()
    {
        return $this->loginLogs()->count();
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }

    public function supportTakeOverLogs()
    {
        return $this->hasMany(SupportTakeOverLog::class, 'support_user_id');
    }

    public function appVersionInfos()
    {
        return $this->hasMany(AppVersionInfo::class);
    }

    public static function boot()
    {
        parent::boot();

        // addd for the onboarding experience 20200506
        static::created(function (User $user) {
            if ($user->userRoles !== null) {
                $user->saveUserRoles();
            }
            if ($user->roles()->first()->getKey() === Role::TEACHER && $user->demo == false) {
                $schoolYear = SchoolYearRepository::getCurrentSchoolYear();
                if (null === $schoolYear) {
                    $user->forceDelete();
                    throw new \Exception('U kunt een docent pas aanmaken als dat u een actuele periode heeft aangemaakt. Dit doet u door als schoolbeheerder in het menu Database -> Schooljaren een schooljaar aan te maken met een periode die in de huidige periode valt.');
                    return false;
                }

                DemoTeacherRegistration::registerIfApplicable($user);

                $helper = new DemoHelper();
                $helper->createDemoForTeacherIfNeeded($user);
            }

            // $user->isA('teacher') valt hier naar false om de een of andere reden?
            // Dit zorgt ervoor dat er dus geen school_location_user records werden aangemaakt;
            // Uitzoeken -- RR 12-10-2022
//            if ($user->isA('teacher') && !is_null($user->school_location_id)) {
            if ($user->roles()->first()->getKey() === Role::TEACHER) {
                $user->handleSchoolLocationsForNewTeacher();
            }

        });

        static::saving(function (User $user) {
            if ($user->isDirty(['school_id', 'school_location_id'])) {
                $user->studentSchoolClasses ??= [];
                $user->managerSchoolClasses ??= [];
                $user->mentorSchoolClasses ??= [];
            }

            $user->setForcePasswordChangeIfRequired();

            if ($user->isDirty(['is_examcoordinator', 'is_examcoordinator_for'])) {
                $user->setAttribute('session_hash', '');
            }
        });

        static::saved(function (User $user) {
            if ($user->isA('Teacher')) {
                $user->handleExamCoordinatorChange();
            }
        });

        static::updating(function (User $user) {
            if ($user->getOriginal('demo') == true && !isset($user->demoRestrictionOverrule)) {
                return false;
            }
            if (isset($user->demoRestrictionOverrule)) {
                unset($user->demoRestrictionOverrule);
            }
        });

        static::updated(function (User $user) {
            if ($user->isA('teacher')) {
                if (null === $user->updateSchoolLocationId) {
                    if (null !== Auth::user() && Auth::user()->isA('school manager') && null !== Auth::user()->school_location_id) {
                        $user->updateSchoolLocationId = Auth::user()->school_location_id;
                    }
                }
                if (null !== $user->updateExternalId && null !== $user->updateSchoolLocationId) {
                    // if not existing, then there should have been another way this school location should be added
                    if ($user->allowedSchoolLocations->contains($user->updateSchoolLocationId)) {
                        $user->allowedSchoolLocations()->updateExistingPivot($user->updateSchoolLocationId, [
                            'external_id' => $user->updateExternalId,
                        ]);
                    }
                    $user->updateExternalId = null;
                    $user->updateSchoolLocationId = null;
                }
            }
//            if ($user->isA('teacher')){
//                if ($user->user_table_external_id == $user->getOriginal('external_id')) {
//                    return true;
//                }
//                foreach ($user->allowedSchoolLocations as $schoolLocation) {
//                    if($schoolLocation->id == Auth::user()->school_location_id){
//                        $user->allowedSchoolLocations()->updateExistingPivot($schoolLocation->id, [
//                            'external_id' => $user->user_table_external_id,
//                        ]);
//                        break; // no need to continu as there's max 1 schoollocationid for this user
//                    }
//                }
//            }
        });

        static::deleting(function (User $user) {
            if (School::where('user_id', $user->id)->count() > 0) {
                throw new \Exception(__('Kan gebruiker niet verwijderen omdat deze gekoppeld is aan een scholengemeenschap'));
            }
            if (SchoolLocation::where('user_id', $user->id)->count() > 0) {
                throw new \Exception(__('Kan gebruiker niet verwijderen omdat deze gekoppeld is aan een schoollocatie'));
            }
            if (UmbrellaOrganization::where('user_id', $user->id)->count() > 0) {
                throw new \Exception(__('Kan gebruiker niet verwijderen omdat deze gekoppeld is aan een koepel'));
            }
            if ($user->getOriginal('demo') == true) {
                return false;
            }
            if ($user->allowedSchoolLocations->count() === 1) {
                $user->removeSchoolLocation($user->schoolLocation);
            }
            if (static::isLoggedInUserAnActiveSchoolLocationMemberOfTheUserToBeRemovedFromThisLocation($user)) {
                $user->removeSchoolLocation(Auth::user()->schoolLocation);
                $user->removeSchoolLocationTeachers(Auth::user()->schoolLocation);
                throw new \Exception(__('Deze gebruiker is ook aanwezig in een andere locatie. Alleen het account voor deze locatie is verwijderd!'));
            }
        });

        // Progress additional answers
        static::saved(function (User $user) {
            $oldText2Speech = (bool)$user->getOriginal('text2speech');
            if (!$oldText2Speech && (bool)request()->input('text2speech')) {
                // we've got a new user with time dispensation
                if (Text2Speech::where('user_id', $user->getKey())
                    ->where('acceptedby', Auth::user()->getKey())->exists()) {

                    $text2Speech = Text2Speech::where('user_id', $user->getKey())
                        ->where('acceptedby', Auth::user()->getKey())->first();

                    $text2Speech->update([
                        'user_id'    => $user->getKey(),
                        'active'     => true,
                        'acceptedby' => Auth::user()->getKey(),
                        'price'      => config('custom.text2speech.price')
                    ]);
                } else {
                    Text2Speech::create([
                        'user_id'    => $user->getKey(),
                        'active'     => true,
                        'acceptedby' => Auth::user()->getKey(),
                        'price'      => config('custom.text2speech.price')
                    ]);
                }

                Text2SpeechLog::create([
                    'user_id' => $user->getKey(),
                    'action'  => 'ACCEPTED',
                    'who'     => Auth::user()->getKey()
                ]);
            } else {
                if ($oldText2Speech && request()->has('active_text2speech')) {
                    // we've got a student with time dispensation and there might be a change in the active status
                    // we only change these settings if there is a active_time_dispensation value, otherwise it would be changed on password update as well for instance
                    $newActiveText2Speech = (bool)request()->input('active_text2speech');
                    $oldActiveText2Speech = (bool)$user->hasActiveText2Speech();
                    if ($newActiveText2Speech !== $oldActiveText2Speech) {
                        $user->text2SpeechDetails->active = $newActiveText2Speech;
                        $user->text2SpeechDetails->save();

                        $user->text2speech = $newActiveText2Speech;
                        $user->save();

                        Text2SpeechLog::create([
                            'user_id' => $user->getKey(),
                            'action'  => ($newActiveText2Speech) ? 'ENABLED' : 'DISABLED',
                            'who'     => Auth::user()->getKey()
                        ]);
                    }
                }
            }


            if ($user->studentSchoolClasses !== null) {
                $user->saveStudentSchoolClasses();
            }

            if ($user->managerSchoolClasses !== null) {
                $user->saveManagerSchoolClasses();
            }

            if ($user->mentorSchoolClasses !== null) {
                $user->saveMentorSchoolClasses();
            }

            if ($user->studentParents !== null) {
                $user->saveStudentParents();
            }

            if ($user->studentParentsOf !== null) {
                $user->saveStudentParentsOf();
            }

            if ($user->profileImage instanceof UploadedFile) {
                $original = $user->getOriginalProfileImagePath();
                if (File::exists($original)) {
                    File::delete($original);
                }

                $user->profileImage->move(storage_path('user_profile_images'),
                    $user->getKey() . ' - ' . $user->getAttribute('profile_image_name'));
            }

            // Reload roles of this user!
            $user->load('roles');
            $roles = Roles::getUserRoles($user);

            if ($user->getAttribute('text2speech') !== $user->getOriginal('text2speech')) {
                if (in_array('Student', $roles)) {
                    $user->addJobUnique(new CountSchoolLocationStudents($user->schoolLocation));
                }
            }

            //Trigger jobs
//			if ($user->getAttribute('school_id') !== $user->getOriginal('school_id') || $user->getAttribute('school_location_id') !== $user->getOriginal('school_location_id')) {
            if ($user->getAttribute('school_id') !== $user->getOriginal('school_id') ||
                $user->getAttribute('school_location_id') !== $user->getOriginal('school_location_id')) {
                // Reload roles of this user!
                $user->load('roles');
                $roles = Roles::getUserRoles($user);


                if (in_array('Student', $roles)) {

                    StatisticsRepository::runBasedOnUser($user);

                    //Delete from future test takes
                    TestParticipant::where('user_id', $user->getKey())->whereIn('test_take_id', function ($query) {
                        $testTake = new TestTake();
                        $date = new Carbon();
                        $date->setTime(0, 0);

                        $query->select('id')->from($testTake->getTable())
                            ->where('test_take_status_id', TestTakeStatus::where('name', '=', 'Planned')->value('id'))
                            ->where('time_start', '>', $date->format('Y-m-d H:i:s'));
                    })->delete();
                }

            } else {
                $school = $user->school;
                $schoolLocation = $user->schoolLocation;

                if ($user->getAttribute('count_last_test_taken') !== $user->getOriginal('count_last_test_taken')) {
                    if ($school !== null) {
                        $user->addJobUnique(new CountSchoolActiveTeachers($school));
                    } elseif ($schoolLocation !== null) {
                        $user->addJobUnique(new CountSchoolLocationActiveTeachers($schoolLocation));
                    }
                }

                if ($user->getAttribute('count_questions') !== $user->getOriginal('count_questions')) {
                    if ($school !== null) {
                        $user->addJobUnique(new CountSchoolQuestions($school));
                    } elseif ($schoolLocation !== null) {
                        $user->addJobUnique(new CountSchoolLocationQuestions($schoolLocation));
                    }
                }

                if ($user->getAttribute('count_tests') !== $user->getOriginal('count_tests')) {
                    if ($school !== null) {
                        $user->addJobUnique(new CountSchoolTests($school));
                    } elseif ($schoolLocation !== null) {
                        $user->addJobUnique(new CountSchoolLocationTests($schoolLocation));
                    }
                }

                if ($user->getAttribute('count_tests_taken') !== $user->getOriginal('count_tests_taken')) {
                    if ($school !== null) {
                        $user->addJobUnique(new CountSchoolTestsTaken($school));
                    } elseif ($schoolLocation !== null) {
                        $user->addJobUnique(new CountSchoolLocationTestsTaken($schoolLocation));
                    }
                }
            }
        });

        static::deleted(function (User $user) {
            if ($user->isA('teacher')) {
                $demoClass = null;
//		        $demoClass = (new DemoHelper())->setSchoolLocation($user->schoolLocation())->getDemoClass();
//		        if($demoClass !== null) {
                $user->teacher->each(function (Teacher $t) use ($demoClass) {
//                        if($t->class_id == $demoClass->getKey()){
                    $t->delete();
//                        }
                });
//              }
            }

            $user->removeEckId();

            if ($user->forceDeleting) {
                $original = $user->getOriginalProfileImagePath();
                if (File::exists($original)) {
                    File::delete($original);
                }
            }

            StatisticsRepository::runBasedOnUser($user);
        });
    }

    /**
     * is user linked to multiple school locations and is the performing user a member of the school location the to be trashed user is linked to.
     */
    private static function isLoggedInUserAnActiveSchoolLocationMemberOfTheUserToBeRemovedFromThisLocation(User $user)
    {
        if ($user->allowedSchoolLocations()->count() <= 1) {
            return false;
        }
        if (null === Auth::user()->schoolLocation) {
            return false;
        }
        if (!$user->allowedSchoolLocations->contains(Auth::user()->schoolLocation->getKey())) {
            return false;
        }
        return true;
//        return (bool) $user->allowedSchoolLocations()->count() > 1
//            && null !== Auth::user()->schoolLocation
//            && $user->allowedSchoolLocations->contains(Auth::user()->schoolLocation->getKey());
    }

    public function getOriginalProfileImagePath()
    {
        return ((substr(storage_path('user_profile_images'),
                    -1) === DIRECTORY_SEPARATOR) ? storage_path('user_profile_images') : storage_path('user_profile_images') . DIRECTORY_SEPARATOR) . $this->getOriginal($this->getKeyName()) . ' - ' . $this->getOriginal('profile_image_name');
    }

    public function getCurrentProfileImagePath()
    {
        return ((substr(storage_path('user_profile_images'),
                    -1) === DIRECTORY_SEPARATOR) ? storage_path('user_profile_images') : storage_path('user_profile_images') . DIRECTORY_SEPARATOR) . $this->getKey() . ' - ' . $this->getAttribute('profile_image_name');
    }

    public function fillFileProfileImage(UploadedFile $file)
    {
        if ($file->isValid()) {
            $this->profileImage = $file;
            $this->setAttribute('profile_image_name', $file->getClientOriginalName());
            $this->setAttribute('profile_image_size', $file->getSize());
            $this->setAttribute('profile_image_extension', $file->getClientOriginalExtension());
            $this->setAttribute('profile_image_mime_type', $file->getMimeType());
        }
    }

    public function roles()
    {
        return $this->belongsToMany('tcCore\Role', 'user_roles')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
    }


    protected function saveUserRoles()
    {
        $userRoles = $this->userRoles()->withTrashed()->get();
        $this->syncTcRelation($userRoles, $this->userRoles, 'role_id', function ($user, $userRole) {
            UserRole::create(['role_id' => $userRole, 'user_id' => $user->getKey()]);
        });

        $this->userRoles = null;
    }

    public function userRoles()
    {
        return $this->hasMany('tcCore\UserRole');
    }

    public function salesOrganization()
    {
        return $this->belongsTo('tcCore\SalesOrganization');
    }

    public function school()
    {
        return $this->belongsTo('tcCore\School');
    }

    public function schoolLocation()
    {
        return $this->belongsTo('tcCore\SchoolLocation');
    }

    public function mentors()
    {
        return $this->hasMany('tcCore\Mentor');
    }

    public function temporaryLogin()
    {
        return $this->hasOne('tcCore\TemporaryLogin');
    }

    public function mentorSchoolClasses()
    {
        return $this->belongsToMany('tcCore\SchoolClass', 'mentors', 'user_id', 'school_class_id')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
    }

    protected function saveMentorSchoolClasses()
    {
        $schoolClasses = $this->mentors()->withTrashed()->get();
        $this->syncTcRelation($schoolClasses, $this->mentorSchoolClasses, 'school_class_id',
            function ($user, $schoolClass) {
                Mentor::create(['school_class_id' => $schoolClass, 'user_id' => $user->getKey()]);
            });

        $this->mentorSchoolClasses = null;
    }


    public function managers()
    {
        return $this->hasMany('tcCore\Manager');
    }

    public function managerSchoolClasses()
    {
        return $this->belongsToMany('tcCore\SchoolClass', 'managers', 'user_id', 'school_class_id')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
    }

    protected function saveManagerSchoolClasses()
    {
        $schoolClasses = $this->managers()->withTrashed()->get();
        $this->syncTcRelation($schoolClasses, $this->managerSchoolClasses, 'school_class_id',
            function ($user, $schoolClass) {
                Manager::create(['school_class_id' => $schoolClass, 'user_id' => $user->getKey()]);
            });

        $this->managerSchoolClasses = null;
    }


    public function students()
    {
        return $this->hasMany('tcCore\Student');
    }

    public function studentSchoolClasses()
    {
        return $this->belongsToMany('tcCore\SchoolClass', 'students', 'user_id', 'class_id')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
    }

    protected function saveStudentSchoolClasses()
    {
        $schoolClasses = $this->students()->withTrashed()->get();
        $this->syncTcRelation($schoolClasses, $this->studentSchoolClasses, 'class_id', function ($user, $schoolClass) {
            Student::create(['class_id' => $schoolClass, 'user_id' => $user->getKey()]);
        });

        $this->studentSchoolClasses = null;
    }

    public function teacherSchoolClasses()
    {
        $userId = $this->getKey();
        return SchoolClass::whereIn('id', function ($query) use ($userId) {
            $query->select('class_id')
                ->from(with(new Teacher())->getTable())
                ->where('user_id', $userId)
                ->where('deleted_at', null);
        });
    }

    public function getTeacherSchoolClassIds()
    {
        $current = SchoolYearRepository::getCurrentSchoolYear();
        if ($current == null) {
            return false;
        }

        return Teacher::where('teachers.user_id', $this->getKey())
            ->where('teachers.deleted_at', null)
            ->leftJoin('school_classes', 'teachers.class_id', 'school_classes.id')
            ->where('school_classes.demo', 0)
            ->where('school_classes.created_by', 'lvs')
            ->where('school_classes.school_year_id', $current->getKey())
            ->pluck('class_id');
    }

    public function studentParents()
    {
        return $this->hasMany('tcCore\StudentParent');
    }

    public function studentParentUsers()
    {
        return $this->belongsToMany('tcCore\Users', 'student_parents', 'user_id', 'parent_id')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
    }

    protected function saveStudentParents()
    {
        $studentParents = $this->studentParents()->withTrashed()->get();
        $this->syncTcRelation($studentParents, $this->studentParents, 'parent_id', function ($user, $parent) {
            StudentParent::create(['parent_id' => $parent, 'user_id' => $user->getKey()]);
        });

        $this->studentParents = null;
    }

    public function studentParentsOf()
    {
        return $this->hasMany('tcCore\StudentParent', 'parent_id');
    }

    public function studentParentOfUsers()
    {
        return $this->belongsToMany('tcCore\Users', 'student_parents', 'parent_id', 'user_id')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
    }

    protected function saveStudentParentsOf()
    {
        $studentParentsOf = $this->StudentParentsOf()->withTrashed()->get();
        $this->syncTcRelation($studentParentsOf, $this->studentParentsOf, 'user_id', function ($parent, $user) {
            StudentParent::create(['user_id' => $user, 'parent_id' => $parent->getKey()]);
        });

        $this->studentParentsOf = null;
    }

    public function scopeSubjectsInCurrentLocation($query)
    {
        $schoolLocationSectionIds = $this->schoolLocation->schoolLocationSections()->select('section_id');
        return $this->subjects()->whereIn('section_id', $schoolLocationSectionIds);
    }

    public function subjects($query = null)
    {
        $userId = $this->getKey();

        if ($query === null) {
            $query = Subject::select();
        } else {
            $query->from(with(new Subject())->getTable())
                ->where('deleted_at', null);
        }

        $query->whereIn('id', function ($query) use ($userId) {
            $query->select('subject_id')
                ->from(with(new Teacher())->getTable())
                ->where('user_id', $userId)
                ->where('deleted_at', null);
        });

        return $query;
    }

    public function subjectsIncludingShared($query = null)
    {
        $sharedSectionIds = $this->schoolLocation->sharedSections()->pluck('id')->unique();
        $baseSubjectIds = $this->subjects()->pluck('base_subject_id')->unique();

        $subjectIdsFromShared = collect([]);

        if (count($sharedSectionIds) > 0) {
            $subjectIdsFromShared = Subject::whereIn('section_id', $sharedSectionIds)->whereIn('base_subject_id',
                $baseSubjectIds)->pluck('id')->unique();
        }

        $subjectIds = $subjectIdsFromShared->merge($this->subjects()->pluck('id')->unique());

        if ($query === null) {
            $query = Subject::whereIn('id', $subjectIds);
        } else {
            $query->from(with(new Subject())->getTable())
                ->where('deleted_at', null)
                ->whereIn('id', $subjectIds);
        }

        return $query;
    }

    public function otherSchoolLocationsSharedSectionsWithMe()
    {
        $schoolLocationSharedSections = SchoolLocationSharedSection::where('school_location_id', $this->schoolLocation->getKey());
        if ($schoolLocationSharedSections->count() === 0) {
            return false;
        }
        return true;
    }

    public function subjectsOnlyShared($query = null)
    {
        $sharedSectionIds = $this->schoolLocation->sharedSections()->pluck('id')->unique();
        $baseSubjectIds = $this->subjects()->pluck('base_subject_id')->unique();

        $subjectIdsFromShared = collect([]);

        if (count($sharedSectionIds) > 0) {
            $subjectIdsFromShared = Subject::whereIn('section_id', $sharedSectionIds)
                ->whereIn('base_subject_id', $baseSubjectIds)
                ->select('id');
        }

        $subjectIds = $subjectIdsFromShared;

        if ($query === null) {
            $query = Subject::whereIn('id', $subjectIds);
        } else {
            $query->from(with(new Subject())->getTable())
                ->where('deleted_at', null)
                ->whereIn('id', $subjectIds);
        }

        return $query;

    }

    public function sections($query = null)
    {
        $user = $this;

        if ($query === null) {
            $query = Section::select();
        } else {
            $query->from(with(new Section())->getTable())
                ->where('deleted_at', null);
        }

        $query->whereIn('id', function ($query) use ($user) {
            $user->subjects($query)->select('section_id');
        });

        return $query;
    }

    public function sectionsOnlyShared($query = null)
    {
        $sharedSectionIds = $this->schoolLocation->sharedSections()->pluck('id')->unique();
        $baseSubjectIds = $this->subjects()->pluck('base_subject_id')->unique();

        $sectionIdsFromShared = collect([]);

        if (count($sharedSectionIds) > 0) {
            $sectionIdsFromShared = Subject::whereIn('section_id', $sharedSectionIds)->whereIn('base_subject_id',
                $baseSubjectIds)->select('section_id');
        }

        if ($query === null) {
            $query = Section::whereIn('id', $sectionIdsFromShared);
        } else {
            $query->from(with(new Section())->getTable())
                ->where('deleted_at', null)
                ->whereIn('id', $sectionIdsFromShared);
        }
        return $query;
    }

    public function teacher()
    {
        return $this->hasMany('tcCore\Teacher');
    }

    public function ownTeachers()
    {
        return $this->hasMany('tcCore\Teacher')->currentSchoolLocation();
    }

    public function tests()
    {
        return $this->hasMany('tcCore\Test', 'author_id');
    }

    public function testTakes()
    {
        return $this->hasMany('tcCore\TestTake', 'user_id');
    }

    public function invigilator()
    {
        return $this->hasMany('tcCore\Invigilator');
    }

    public function invigilatorTestTakes()
    {
        return $this->belongsToMany('tcCore\TestTake', 'invigilators')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ]);
    }

    public function testParticipants()
    {
        return $this->hasMany('tcCore\TestParticipant');
    }

    public function testRatings()
    {
        return $this->hasMany('tcCore\TestRating');
    }

    public function answerRating()
    {
        return $this->hasMany('tcCore\AnswerRating');
    }

    public function questionAuthors()
    {
        return $this->hasMany('tcCore\QuestionAuthor', 'user_id');
    }

    public function authors()
    {
        return $this->belongsToMany('tcCore\Question', 'question_authors', 'user_id', 'question_id')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
    }

    // Account manager's umbrella organizations
    public function umbrellaOrganizations()
    {
        return $this->hasMany('tcCore\UmbrellaOrganization');
    }

    // Account manager's schools
    public function schools()
    {
        return $this->hasMany('tcCore\School');
    }

    public function ratings()
    {
        return $this->hasMany('tcCore\Rating');
    }

    public function averageRatings()
    {
        return $this->hasMany('tcCore\AverageRating');
    }

    public function onboardingWizardUserSteps()
    {
        return $this->hasMany(OnboardingWizardUserStep::class);
    }

    public function onboardingWizardUserState()
    {
        return $this->hasOne(OnboardingWizardUserState::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function generalTermsLog()
    {
        return $this->hasOne(GeneralTermsLog::class, 'user_id');
    }

    public function trialPeriods()
    {
        return $this->hasMany(TrialPeriod::class, 'user_id');
    }

    public function trialPeriodsWithSchoolLocationCheck()
    {
        return $this->hasOne(TrialPeriod::class, 'user_id')->where('school_location_id', $this->school_location_id);
    }

    public function systemSettings()
    {
        $this->hasMany(UserSystemSetting::class);
    }

    public function userFeatureSettings()
    {
        return $this->hasMany(UserFeatureSetting::class);
    }

    public function getOnboardingWizardSteps()
    {
        $state = $this->onboardingWizardUserState;
        $wizard = null;
        if ($state) {
            $wizard = $state->wizard;
        }

        if ($wizard == null) {
            $roleIds = $this->roles()->select('id');
            $wizard = OnboardingWizard::whereIn('role_id', $roleIds)
                ->where('active', true)
                ->orderBy('role_id')
                ->first();
            OnboardingWizardUserState::create([
                'id'                   => Str::uuid(),
                'user_id'              => $this->getKey(),
                'onboarding_wizard_id' => $wizard->getKey()
            ]);
        }

        if ($wizard === null) {
            return collect([]); // there is no wizard
        }

        $doneIds = $this->onboardingWizardUserSteps()->pluck('onboarding_wizard_step_id');
        $steps = $wizard->mainSteps->each(function ($step) use ($doneIds) {
            $done = false;
            if ($doneIds->contains($step->getkey())) {
                $done = true;
            }
            $step->done = $done;
            $step->sub->map(function ($sub) use ($doneIds) {
                $done = false;
                if ($doneIds->contains($sub->getKey())) {
                    $done = true;
                }
                $sub->done = $done;
                return $sub;
            });
            return $step;
        });
        return $steps;
    }


    /**
     * Returns the private API key for the user, or false on failure.
     *
     * @return bool|mixed
     */
    public function apiKey()
    {
        return !empty($this->api_key) ? $this->api_key : false;
    }

    public function isToetsenbakker()
    {
        if (!$this->isA('Teacher')) {
            return false;
        }

        $isToetsenbakker = UserSystemSetting::getSettingFromSession($this, 'isToetsenbakker');
        if (is_bool($isToetsenbakker)) {
            return $isToetsenbakker;
        }

        $isToetsenbakker = FileManagement::testUploads()->handledBy($this)->exists()
            || SchoolLocationUser::whereUserId($this->getKey())
                ->whereIn('school_location_id', SchoolLocation::select('id')->where('customer_code', config('custom.TB_customer_code')))
                ->exists();

        UserSystemSetting::setSetting($this, 'isToetsenbakker', $isToetsenbakker);

        return $isToetsenbakker;
    }

    public function isCurrentlyInToetsenbakkerij() : bool
    {
        return SchoolLocation::where('customer_code', config('custom.TB_customer_code'))->where('id',$this->school_location_id)->exists();
    }

    public function isTestCorrectUser()
    {
        if (stristr($this->username, '@test-correct.nl')) {
            return true;
        }
        return false;
    }

    public function hasCitoToetsen()
    {
        return (bool)$this->subjects()->where('name', 'like', 'cito%')->count() > 0;
    }

    public function isInExamSchool(): bool
    {
        if (optional($this->schoolLocation)->customer_code == config('custom.examschool_customercode')) {
            return true;
        }
        return false;
    }
    public static function getDeletedNewUser()
    {
        $user = new static();
        $user->name = 'student';
        $user->name_first = 'verwijderde';
        return $user;
    }

    public function getNameFullAttribute()
    {
        return Str::squish(sprintf('%s %s %s', $this->name_first, $this->name_suffix, $this->name));
    }

    public function hasSharedSections()
    {
        return (bool)(null !== $this->schoolLocation && $this->schoolLocation->sharedSections()->count());
    }

    public function isPartOfSharedSection()
    {
        if (!$this->otherSchoolLocationsSharedSectionsWithMe()) {
            return false;
        }
        if ($this->subjectsOnlyShared()->count() === 0) {
            return false;
        }
        return true;
    }

    public function scopeStudentFiltered($query, $filters = [], $sorting = [])
    {
        $query->join('user_roles', function ($join) {
            $join->on('user_roles.user_id', '=', 'users.id')->whereNull('user_roles.deleted_at');
        })
            ->join('roles', function ($join) {
                $join->on('roles.id', '=', 'user_roles.role_id')->whereNull('roles.deleted_at');
            })
            ->where('roles.name', 'Student');

        $user = Auth::user()->getAttributes();

        if (array_key_exists('school_id',
                $user) && $user['school_id'] !== null && array_key_exists('school_location_id',
                $user) && $user['school_location_id'] !== null) {
            $query->where(function ($query) use ($user) {
                $query->where('users.school_id', $user['school_id'])
                    ->orWhere('users.school_location_id', $user['school_location_id']);
            });
        } elseif (array_key_exists('school_id', $user) && $user['school_id'] !== null) {
            $query->where('users.school_id', $user['school_id']);
        } elseif (array_key_exists('school_location_id', $user) && $user['school_location_id'] !== null) {
            $query->where('users.school_location_id', $user['school_location_id']);
        }

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'name':
                    $query->where('name', 'LIKE', '%' . $value . '%');
                    break;
                case 'school_class_id':
                    $query->whereIn('users.id', function ($query) use ($value) {
                        $query->select('students.user_id')
                            ->from(with(new SchoolClass())->getTable())
                            ->join(with(new Student())->getTable(), 'students.class_id', '=', 'school_classes.id')
                            ->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
                            ->where('school_classes.deleted_at', null)
                            ->where('students.deleted_at', null);
                    });
                    break;
                case 'test_take_id_participated':
                case 'test_take_id_made':
                case 'test_take_id_not_made':
                    $testTakeIds = $value;

                    $testTakeStatusses = null;
                    $maxRating = null;
                    $minRating = null;

                    if ($key === 'test_id_made') {
                        $testTakeStatusses = TestTakeStatus::whereIn('name', [
                            'Taking test', 'Handed in', 'Taken away', 'Taken', 'Discussing', 'Discussed', 'Rated'
                        ])->pluck('id');
                        if (array_key_exists('max_rating', $filters)) {
                            $maxRating = $filters['max_rating'];
                        }
                        if (array_key_exists('min_rating', $filters)) {
                            $minRating = $filters['min_rating'];
                        }
                    } elseif ($key === 'test_take_id_participated') {
                        $testTakeStatusses = TestTakeStatus::whereIn('name',
                            ['Planned', 'Test not taken'])->pluck('id');
                    }

                    $subQuery = function ($query) use ($testTakeIds, $testTakeStatusses, $maxRating, $minRating) {
                        $query->select('test_participants.user_id')
                            ->from(with(new SchoolClass())->getTable());
                        if (is_array($testTakeIds)) {
                            $query->whereIn('test_take_id', $testTakeIds);
                        } else {
                            $query->where('test_take_id', $testTakeIds);
                        }
                        if ($testTakeStatusses !== null) {
                            $query->whereIn('test_take_status_id', $testTakeStatusses);
                        }

                        if ($maxRating !== null) {
                            $query->where('rating', '<=', $maxRating);
                        }

                        if ($minRating !== null) {
                            $query->where('rating', '>=', $minRating);
                        }
                    };

                    $query->whereIn('users.id', $subQuery);
                    break;
                case 'test_id_participated':
                case 'test_id_made':
                case 'test_id_not_made':
                    // Todo: same filters as above but with test_ids
                    break;
            }
        }

        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'name':
                case 'abbreviation':
                case 'subject_id':
                case 'education_level_id':
                case 'education_level_year':
                case 'period_id':
                case 'test_kind_id':
                case 'status':
                case 'author_id':
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
                case 'name':
                case 'abbreviation':
                case 'subject_id':
                case 'education_level_id':
                case 'education_level_year':
                case 'period_id':
                case 'test_kind_id':
                case 'status':
                case 'author_id':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {

        $roles = Roles::getUserRoles();
        // you are an Account manager
        if (!in_array('Administrator', $roles) && !in_array('Support', $roles)) {
            $query->where(function ($query) use ($roles) {
                if (!in_array('Administrator', $roles) && in_array('Account manager', $roles)) {
//                    logger(__LINE__);
                    //		if($this->hasRole(['Administrator','Account manager'])){
                    $userId = Auth::user()->getKey();

                    $sHelper = new SchoolHelper();
                    $schoolIds = $sHelper->getRelatedSchoolIds(Auth::user());
                    $schoolLocationIds = $sHelper->getRelatedSchoolLocationIds(Auth::user());

                    $parentIds = StudentParent::whereIn('user_id',
                        function ($query) use ($schoolIds, $schoolLocationIds) {
                            $query->select($this->getKeyName())
                                ->from($this->getTable())
                                ->where(function ($query) use ($schoolIds, $schoolLocationIds) {
                                    $query->whereIn('school_id', $schoolIds);
                                    $query->orWhereIn('school_location_id', $schoolLocationIds);
                                })
                                ->whereNull('deleted_at');
                        })->pluck('parent_id')->all();

                    $query->where(function ($query) use ($schoolIds, $schoolLocationIds, $parentIds) {
                        $query->whereIn('school_id', $schoolIds);
                        $query->orWhereIn('school_location_id', $schoolLocationIds);
                        $query->orWhereIn('id', $parentIds);
                    });
                    // you are a school manager, teacher, invigilator, school management or mentor
                } elseif (!in_array('Administrator', $roles) && (in_array('School manager',
                            $roles) || in_array('Teacher',
                            $roles) || in_array('Invigilator', $roles) || in_array('School management',
                            $roles) || in_array('Mentor', $roles))) {
//        } elseif (!$this->hasRole('Administrator') &&
//            ($this->hasRole(['School manager','Teacher','Invigilator', 'School management','Mentor']))) {
                    $user = Auth::user();
                    $schoolId = $user->getAttribute('school_id');
                    $schoolLocationId = $user->getAttribute('school_location_id');

                    if ($schoolId !== null) {
                        $schoolLocationIds = SchoolLocation::where(function ($query) use (
                            $schoolId,
                            $schoolLocationId
                        ) {
                            $query->where('school_id', $schoolId)->orWhere('id', $schoolLocationId);
                        })->pluck('id')->all();
                    } elseif ($schoolLocationId !== null) {
                        $schoolLocationIds = [$schoolLocationId];
                    } else {
                        $schoolLocationIds = [];
                    }

                    $parentIds = StudentParent::whereIn('user_id',
                        function ($query) use ($schoolId, $schoolLocationIds) {
                            $query->select($this->getKeyName())
                                ->from($this->getTable())
                                ->where(function ($query) use ($schoolId, $schoolLocationIds) {
                                    $query->where('school_id', $schoolId);
                                    $query->orWhereIn('school_location_id', $schoolLocationIds);
                                })
                                ->whereNull('deleted_at');
                        })->pluck('parent_id')->all();

                    $query->where(function ($query) use ($schoolId, $schoolLocationIds, $parentIds) {
                        if ($schoolId !== null) {
                            $query->where('school_id', $schoolId);
                            $query->orWhereIn('school_location_id', $schoolLocationIds);
                        } elseif ($schoolLocationIds !== null) {
                            $query->whereIn('school_location_id', $schoolLocationIds);
                        }

                        $query->orWhereIn('id', $parentIds);
                    });
                    // you are probably a student or so
                } else {
//        } elseif (!$this->hasRole('Administrator')) {
                    $query->where('id', Auth::user()->getKey());
                }
                $query->orWhereIn('users.id', $this->fromAnotherLocation(Auth::user()));

            });
        }

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'teacher_students': // only show students in the classes of the teacher
                    $user = $user ?? Auth::user();
                    $value = $user->teacher->pluck('class_id')->toArray();
                    $query->whereIn('users.id', function ($query) use ($value) {
                        $query->select('students.user_id')
                            ->from(with(new SchoolClass())->getTable())
                            ->join(with(new Student())->getTable(), 'students.class_id', '=', 'school_classes.id')
                            ->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
                            ->where('school_classes.deleted_at', null)
                            ->where('students.deleted_at', null);
                    });
                    break;
                case 'sales_organization_id':
                    if (is_array($value)) {
                        $query->whereIn('sales_organization_id', $value);
                    } else {
                        $query->where('sales_organization_id', '=', $value);
                    }
                    break;
                case 'school_id':
                    if (is_array($value)) {
                        $query->whereIn('school_id', $value);
                    } else {
                        $query->where('school_id', '=', $value);
                    }
                    break;
                case 'school_location_id':
                    if (is_array($value)) {
                        $query->whereIn('school_location_id', $value);
                    } else {
                        $query->where('school_location_id', '=', $value);
                    }
                    break;
                case 'school_class_id':
                    $query->whereIn('users.id', function ($query) use ($value) {
                        $query->select('students.user_id')
                            ->from(with(new SchoolClass())->getTable())
                            ->join(with(new Student())->getTable(), 'students.class_id', '=', 'school_classes.id')
                            ->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
                            ->where('school_classes.deleted_at', null)
                            ->where('students.deleted_at', null);
                    });
                    break;
                case 'external_id':
                    if (is_array($value)) {
                        $query->whereIn('external_id', $value);
                    } else {
                        $query->where('external_id', '=', $value);
                    }
                    break;
                case 'username':
                    $query->where('username', 'LIKE', '%' . $value . '%');
                    break;
                case 'name_full':
                    $query->where(DB::raw('CONCAT_WS(\' \', name_first, name_suffix, name)'), 'LIKE', '%' . $value . '%');
                    break;
                case 'name':
                    $query->where('name', 'LIKE', '%' . $value . '%');
                    break;
                case 'name_first':
                    $query->where('name_first', 'LIKE', '%' . $value . '%');
                    break;
                case 'has_package':
                    if ($value==1) {      $query->where('has_package', '=', 1);
                    } elseif($value==2) {
                        $query->where('has_package', '=', 0);
                    }
                    break;
                case 'trial_status':
                    $usersStatus = TrialPeriod::get();
                    $usersWithMoreThan14Days = []; // Array to store user IDs with more than 14 days remaining
                    $usersWithMoreThan0Days = []; // Array to store user IDs with more than 0 days remaining
                    foreach ($usersStatus as $userStatus) {
                        $daysRemaining = $userStatus->created_at->diffInDays($userStatus->trial_until);
        
                        if ($daysRemaining >= 1 && $daysRemaining <= 15) {
                            // If days remaining is greater than 14, store the user ID in the array
                            $usersWithMoreThan14Days[] = $userStatus->user_id;
                        } else {
                            $usersWithMoreThan0Days[] = $userStatus->user_id;
                        }
                    }
                    if ($value==1) {
                        $query->whereNotIn('id', $usersWithMoreThan14Days)->whereNotIn('id', $usersWithMoreThan0Days);
                    } elseif($value==2) {
                        $query->whereIn('id', $usersWithMoreThan0Days);
                    }
                    elseif($value==3) {
                        $query->whereIn('id', $usersWithMoreThan14Days);
                    }
                    break;
                case 'beta_status':
                    $usersSystemSetting = UserSystemSetting::get();
                    $usersWithBetaStatus = []; // Array to store user IDs with beta status
                    $usersWithBetaStatusNewTestTakeDetailPage = []; // Array to store user IDs with beta status
                    foreach ($usersSystemSetting as $userSystemSetting) {
                        if ($userSystemSetting->value == 1) {
                            $usersWithBetaStatus[] = $userSystemSetting->user_id;
                            if ($userSystemSetting->title == 'allow_new_test_take_detail_page') {
                                $usersWithBetaStatusNewTestTakeDetailPage[] = $userSystemSetting->user_id;
                            }
                        }
                    }
                    if($value==1) {
                        $query->whereIn('id', $usersWithBetaStatus);
                    } elseif($value==2) {
                        $query->whereIn('id', $usersWithBetaStatusNewTestTakeDetailPage);
                    } elseif($value==3) {
                        $query->whereNotIn('id', $usersWithBetaStatus);
                    }
                    break;
                case 'send_welcome_email':
                    $query->where('send_welcome_email', '=', $value);
                    break;
                case 'gender':
                    if (is_array($value)) {
                        $query->whereIn('gender', $value);
                    } else {
                        $query->where('gender', '=', $value);
                    }
                    break;
                case 'student_school_class_id':
                    $query->whereIn('users.id', function ($query) use ($value) {
                        $query->select('students.user_id')
                            ->from(with(new SchoolClass())->getTable())
                            ->join(with(new Student())->getTable(), 'students.class_id', '=', 'school_classes.id')
                            ->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
                            ->where('school_classes.deleted_at', null)
                            ->where('students.deleted_at', null);
                    });
                    break;
                case 'mentor_school_class_id':
                    $query->whereIn('users.id', function ($query) use ($value) {
                        $query->select('mentors.user_id')
                            ->from(with(new SchoolClass())->getTable())
                            ->join(with(new Mentor())->getTable(), 'mentors.school_class_id', '=', 'school_classes.id')
                            ->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
                            ->where('school_classes.deleted_at', null)
                            ->where('mentors.deleted_at', null);
                    });
                    break;
                case 'manager_school_class_id':
                    $query->whereIn('users.id', function ($query) use ($value) {
                        $query->select('managers.user_id')
                            ->from(with(new SchoolClass())->getTable())
                            ->join(with(new Manager())->getTable(), 'managers.school_class_id', '=',
                                'school_classes.id')
                            ->whereIn('school_classes.id', (is_array($value)) ? $value : [$value])
                            ->where('school_classes.deleted_at', null)
                            ->where('managers.deleted_at', null);
                    });
                    break;
                case 'student_parent_id':
                    $query->whereIn('users.id', function ($query) use ($value) {
                        $query->select('users.id')
                            ->from(with(new User())->getTable())
                            ->join(with(new StudentParent())->getTable(), 'student_parents.user_id', '=', 'users.id')
                            ->whereIn('student_parents.parent_id', (is_array($value)) ? $value : [$value])
                            ->where('users.deleted_at', null)
                            ->where('student_parents.deleted_at', null);
                    });
                    break;
                case 'student_parent_of_id':
                    $query->whereIn('users.id', function ($query) use ($value) {
                        $query->select('users.id')
                            ->from(with(new User())->getTable())
                            ->join(with(new StudentParent())->getTable(), 'student_parents.parent_id', '=', 'users.id')
                            ->whereIn('student_parents.user_id', (is_array($value)) ? $value : [$value])
                            ->where('users.deleted_at', null)
                            ->where('student_parents.deleted_at', null);
                    });
                    break;
                case 'role':
                    $query->whereIn('id', function ($query) use ($value) {
                        $query->select('user_id')
                            ->from(with(new UserRole())->getTable());
                        if (is_array($value)) {
                            $query->whereIn('role_id', $value);
                        } else {
                            $query->where('role_id', '=', $value);
                        }
                    });
                    break;
                case 'trial':
                    $query->whereIn(
                        'id',
                        SchoolLocationUser::select('school_location_user.user_id')
                            ->join('school_locations', 'school_locations.id', '=', 'school_location_user.school_location_id')
                            ->where('school_locations.license_type', SchoolLocation::LICENSE_TYPE_TRIAL)
                    );
                    break;
                case 'without_guests':
                    $query->when($value, function ($query) {
                        $query->withoutGuests();
                    });
                    break;
                default:
                    break;
            }
        }

        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'name':
                case 'name_first':
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
                case 'name':
                case 'name_first':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }

    public function generateSessionHash()
    {
        // didn't work out to be safe
        // return str_random(100);
        // new
        return sprintf('%s%d', Str::random(85), $this->id);
    }

    public function isA($roleName)
    {
        return $this->hasRole($roleName, $this);
    }

    private $_hasRoleUser = null;
    private $_hasRoleRoles = null;

    /**
     * @param $roleName
     * @param null $user if no user is given, the auth::user is taken
     * @return bool
     */
    public function hasRole($roleName, $user = null)
    {
        if ($this->_hasRoleRoles === null || $this->_roleUser != $user) {
            $this->_hasRoleRoles = array_map('strtolower', Roles::getUserRoles($user));
            $this->_hasRoleUser = $user;
        }
        if (!is_array($roleName)) {
            return (in_array(strtolower($roleName), $this->_hasRoleRoles));
        } else {
            foreach ($roleName as $name) {
                if (in_array(strtolower($name), $this->_hasRoleRoles)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function canAccess()
    {
        $roles = Roles::getUserRoles($this);
        if ($this->hasRole('Administrator', $this)) {
            return true;
        }

        if ($this->hasRole('Account manager', $this)) {
            $userId = Auth::user()->getKey();

            $schoolIds = School::where(function ($query) use ($userId) {
                $query->whereIn('umbrella_organization_id', function ($query) use ($userId) {
                    $query->select('id')
                        ->from(with(new UmbrellaOrganization())->getTable())
                        ->where('user_id', $userId)
                        ->whereNull('deleted_at');
                })->orWhere('user_id', $userId);
            })->pluck('id')->all();

            $schoolLocationIds = SchoolLocation::where(function ($query) use ($schoolIds, $userId) {
                $query->whereIn('school_id', $schoolIds)
                    ->orWhere('user_id', $userId);
            })->pluck('id')->all();

            if (!in_array($this->getAttribute('school_id'),
                    $schoolIds) && !in_array($this->getAttribute('school_location_id'), $schoolLocationIds)) {
                $parentCount = StudentParent::whereIn('user_id',
                    function ($query) use ($schoolIds, $schoolLocationIds) {
                        $query->select($this->getKeyName())
                            ->from($this->getTable())
                            ->where(function ($query) use ($schoolIds, $schoolLocationIds) {
                                $query->whereIn('school_id', $schoolIds);
                                $query->orWhereIn('school_location_id', $schoolLocationIds);
                            })
                            ->whereNull('deleted_at');
                    })->where('parent_id', $this->getKey())->count();

                return ($parentCount >= 1);
            } else {
                return true;
            }
        }

        if ($this->hasRole(['School manager', 'Teacher', 'Invigilator', 'School management', 'Mentor'], $this)) {
            $user = Auth::user();
            $schoolId = $user->getAttribute('school_id');
            $schoolLocationId = $user->getAttribute('school_location_id');

            if ($schoolId !== null) {
                $schoolLocationIds = SchoolLocation::where(function ($query) use ($schoolId, $schoolLocationId) {
                    $query->where('school_id', $schoolId)->orWhere('id', $schoolLocationId);
                })->pluck('id')->all();
            } elseif ($schoolLocationId !== null) {
                $schoolLocationIds = [$schoolLocationId];
            } else {
                $schoolLocationIds = [];
            }

            if ($this->getAttribute('school_id') != $user->getAttribute('school_id') && !in_array($this->getAttribute('school_location_id'),
                    $schoolLocationIds)) {
                $parentCount = StudentParent::whereIn('user_id', function ($query) use ($schoolId, $schoolLocationIds) {
                    $query->select($this->getKeyName())
                        ->from($this->getTable())
                        ->where(function ($query) use ($schoolId, $schoolLocationIds) {
                            $query->where('school_id', $schoolId);
                            $query->orWhereIn('school_location_id', $schoolLocationIds);
                        })
                        ->whereNull('deleted_at');
                })->where('parent_id', $this->getKey())->count();

                return ($parentCount >= 1);
            } else {
                return true;
            }
        }

        // As a student you're not allowed to update your details except your password, but that is not limited through this middlewarecall
        // so only allow show request.
        if ($this->getKey() === Auth::user()->getKey()) {
            if ($this->hasRole('Student', $this)) {
                //dd(request()->route);
                if (request()->route()->getName() == 'users.show' || request()->route()->getName() == 'user.update') {
                    return true;
                }
                return false;
            }
            return true; // others than students may update their own details
        }
        return false;
    }

    public function canAccessBoundResource($request, Closure $next)
    {
        return $this->canAccess();
    }

    public function canUseTeacherCkEditorWithWebSpellChecker()
    {
        return false; //for now
        $notPreview = PreviewLaravelController::isNotPreview();
        return ($this->isA('teacher') && $this->schoolLocation->allow_wsc && $notPreview);
    }

    public function canUseTeacherCkEditorWithoutWebSpellChecker()
    {
        return false; //for now
        $notPreview = PreviewLaravelController::isNotPreview();
        return ($this->isA('teacher') && !$this->schoolLocation->allow_wsc && $notPreview);
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to user denied');
    }

    public function setPasswordAttribute($pw)
    {
        $this->attributes['password'] = Hash::needsRehash($pw) ? Hash::make($pw) : $pw;
        return $this;
    }

    public function resetAndSavePassword($pw)
    {
        $this->password = $pw;
        $this->save();
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->username;
    }

    public function hasAccessToTest(Test $test)
    {
        return (null !== $test->subject && $this->subjects()->pluck('id')->contains($test->subject->getKey()));
    }

    public function hasAccessToSharedSectionsTest(Test $test)
    {
        $sharedSectionIds = $this->schoolLocation->sharedSections()->pluck('id')->unique();
        $baseSubjectIds = $this->subjects()->pluck('base_subject_id')->unique();
        return
            collect($sharedSectionIds)->contains($test->subject->section()->pluck('id')->first())
            &&
            collect($baseSubjectIds)->contains($test->subject()->pluck('base_subject_id')->first());
    }

    public function makeOnboardWizardIfNeeded()
    {
        if (null === $this->onboardingWizardUserState) {
            if ($this->isA('teacher')) {
                $wizard = OnboardingWizard::where('role_id', 1)->first();
                $this->onboardingWizardUserState()->save(
                    OnboardingWizardUserState::make(
                        [
                            'id'                   => Str::uuid(),
                            'show'                 => true,
                            'onboarding_wizard_id' => $wizard->getKey()
                        ]
                    )
                );
            }
        }
    }

    public function getLastLoginAttribute()
    {
        return optional($this->loginLogs()->orderBy('created_at', 'desc')->first())->created_at;
    }

    public function emailDomainInviterAndInviteeAreEqual()
    {
        $originalUser = User::find($this->invited_by);
        if (null === $originalUser) {
            return false;
        }
        return (strtolower(explode('@', $originalUser->username)[1]) === strtolower(explode('@', $this->username)[1]));
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function scopeNotDemo($query, $tableAlias = null)
    {
        if (!$tableAlias) {
            $tableAlias = $this->getTable();
        }

        return $query->where(sprintf('%s.demo', $tableAlias), 0);
    }

    public function isAllowedSchool(School $school)
    {
        return $this->allowedSchoolLocations()->where('school_id', $school->getKey())->exists();
    }

    public function allowedSchoolLocations()
    {
        return $this->belongsToMany(SchoolLocation::class)->withPivot(['external_id'])->withTimestamps();
    }

    public function isAllowedToSwitchToSchoolLocation(SchoolLocation $schoolLocation)
    {
        return null !== $this->allowedSchoolLocations()->firstWhere($schoolLocation->getKeyName(),
                $schoolLocation->getKey());
    }

    public function hasMultipleSchools()
    {
        return !!($this->allowedSchoolLocations->count() > 1);
    }

    public function addSchoolLocation(SchoolLocation $schoolLocation)
    {
        if (!$this->allowedSchoolLocations->contains($schoolLocation)) {
            $this->allowedSchoolLocations()
                ->attach($schoolLocation->id, ['external_id' => $this->user_table_external_id]);
            return true;
        }
        return null;
    }

    public function addSchoolLocationAndCreateDemoEnvironment(SchoolLocation $schoolLocation)
    {
        if ($this->addSchoolLocation($schoolLocation)) {
            ActingAsHelper::getInstance()->setUser($this);
            $helper = new DemoHelper();
            $helper->createDemoForTeacherIfNeeded($this);
        }
    }

    public function removeSchoolLocation(SchoolLocation $schoolLocation)
    {
        if ($this->schoolLocation->is($schoolLocation)) {
            $newActiveSchoolLocation = $this->allowedSchoolLocations()->where('school_location_id', '<>',
                $schoolLocation->getKey())->first();
            if ($newActiveSchoolLocation !== null) {
                $this->schoolLocation()->associate($newActiveSchoolLocation);
                $this->save();
            }
        }

        $this->allowedSchoolLocations()->detach($schoolLocation);
// when only one left also delete that one;
//        if ($this->allowedSchoolLocations()->count() === 1) {
//            $this->allowedSchoolLocations()->detach($this->allowedSchoolLocations()->first());
//        }

        return $this;
    }

    public function removeSchoolLocationTeachers(SchoolLocation $schoolLocation)
    {
        foreach ($this->teacher as $teacher) {
            if (!$this->teacherBelongsToSchoolLocation($teacher, $schoolLocation)) {
                continue;
            }
            $teacher->delete();
        }
    }

    private function teacherBelongsToSchoolLocation(Teacher $teacher, SchoolLocation $schoolLocation)
    {
        if ($teacher->schoolClass->schoolLocation->id == $schoolLocation->id) {
            return true;
        }
        return false;
    }

    private function fromAnotherLocation($user)
    {
        $school = SchoolLocation::where('id', $user->getAttribute('school_location_id'))->first();
        if ($school === null) {
            return [];
        }
        return DB::table('school_location_user')
            ->selectRaw('distinct user_id')
            ->whereIn(
                'school_location_user.school_location_id',
                SchoolLocation::select('id')
                    ->where([
                        ['id', $user->getAttribute('school_location_id')],
                        ['school_id', $school->school_id],
                    ])
            );
    }

    public function resendEmailVerificationMail()
    {
        return Mail::to($this->username)->queue(new SendOnboardingWelcomeMail($this));
    }

    public function toggleVerified()
    {
        if ($this->account_verified === null) {
            $this->account_verified = Carbon::now();
        } else {
            $this->account_verified = null;
        }
        $this->save();
        return $this;
    }

    public function scopeWithRoleTeacher($query)
    {
        return $query->join('user_roles', 'users.id', '=', 'user_roles.user_id')->where('user_roles.role_id', 1);
    }

    public function getFullNameWithAbbreviatedFirstName(): string
    {
        $letter = Str::substr($this->name_first, 0, 1);
        filled($this->name_suffix) ? $suffix = $this->name_suffix . ' ' : $suffix = '';

        return sprintf('%s. %s%s', $letter, $suffix, $this->name);
    }

    public function hasIncompleteImport($withFinalizedCheck = true)
    {
        if (!optional($this->schoolLocation)->lvs_type) { // not lvs_active any more as active means that it will be taken along with the daily checks cron import
            return false;
        }

        $current = SchoolYearRepository::getCurrentSchoolYear();
        if ($current == null) {
            return false;
        }

        if ($this->isA('teacher')) {

            $baseSubjectId = BaseSubject::where('name', 'demovak')->value('id');
            $teacherRecords = Teacher::selectRaw('count(*) as cnt')
                ->leftJoin('teacher_import_logs', 'teachers.id', 'teacher_import_logs.teacher_id')
                ->leftJoin('school_classes', 'teachers.class_id', 'school_classes.id')
                ->where(function ($query) use ($baseSubjectId) {
                    $query->whereIn('teachers.subject_id', function ($query) use ($baseSubjectId) {
                        $query->select('id')
                            ->from('subjects')
                            ->where('base_subject_id', $baseSubjectId)
                            ->where('abbreviation', 'IMP')
                            ->whereNull('subjects.deleted_at');
                    })
                        ->orWhere(function ($query) {
                            $query->whereNull('teacher_import_logs.checked_by_teacher')
                                ->orWhereNull('teacher_import_logs.id');
                        });
                })
                ->where('teachers.user_id', $this->getKey())
                ->where('school_classes.demo', 0)
                ->where('school_classes.created_by', 'lvs')
                ->where('school_classes.school_year_id', $current->getKey())
//                ->where('school_classes.visible', 0) // if a class is checked by another teacher, then it might already be visible
                ->value('cnt');

            $classRecords = SchoolClass::selectRaw('count(*) as cnt')
                ->withoutGlobalScope('visibleOnly')
                ->where('school_classes.visible', 0)
                ->where('school_classes.created_by', 'lvs')
                ->leftJoin('school_class_import_logs', 'school_classes.id', 'school_class_import_logs.class_id')
                ->where('school_classes.school_year_id', $current->getKey())
                ->whereIn('school_classes.id', function ($query) {
                    $query->select('class_id')
                        ->from('teachers')
                        ->where('user_id', $this->getKey())
                        ->whereNull('teachers.deleted_at');
                })
                ->where(function ($query) use ($withFinalizedCheck) {
                    if ($withFinalizedCheck) {
                        $query->whereNull('school_class_import_logs.finalized');
                    } else {
                        $query->whereNull('checked_by_teacher');
                    }
                    $query->orWhereNull('school_class_import_logs.id');
                })->where('demo', 0)
                ->value('cnt');
            return ($classRecords + $teacherRecords) > 0;
        }

        if ($this->isA('school manager')) {
            $classRecords = SchoolClass::selectRaw('count(*) as cnt')->withoutGlobalScope('visibleOnly')
                ->where('school_classes.visible', 0)
                ->where('school_classes.created_by', 'lvs')
                ->where('school_classes.is_main_school_class', 1)
                ->leftJoin('school_class_import_logs', 'school_classes.id', 'school_class_import_logs.class_id')
                ->where('school_classes.school_location_id', $this->schoolLocation->getKey())
                ->where('school_classes.school_year_id', $current->getKey())
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('school_class_import_logs.checked_by_admin')
                            ->whereNull('school_class_import_logs.checked_by_teacher')
                            ->whereNotNull('school_class_import_logs.id');
                    });
                    $query->orWhereNull('school_class_import_logs.id');
                })->value('cnt');
            return $classRecords > 0;
        }

        return false;
    }


    public function generateMissingEmailAddress()
    {
        if ($this->isA('student')) {
            return sprintf(self::STUDENT_IMPORT_EMAIL_PATTERN, $this->getKey());
        }
        if ($this->isA('teacher')) {
            return sprintf(self::TEACHER_IMPORT_EMAIL_PATTERN, $this->getKey());
        }
    }

    public function hasImportMailAddress()
    {
        return ($this->generateMissingEmailAddress() === $this->username);
    }

    public function redirectToCakeWithTemporaryLogin($options = null)
    {
        $redirectUrl = $this->getTemporaryCakeLoginUrl($options);

        return redirect()->to($redirectUrl);
    }

    public function getRedirectUrlSplashOrStartAndLoginIfNeeded($options = null)
    {
        // added conditional for Thijs to test the new app with fallback if we forget to remove the conditional
        // should be removed before deployment to live
//        if(Carbon::now() > Carbon::createFromFormat('Y-m-d','2022-01-22')) {
        $this->loginThisUser();
        BaseHelper::doLoginProcedure();
        Log::stack(['loki'])->info("authenticated via Entree", []);
        if ($this->isA('student')) {
            if ($this->schoolLocation->allow_new_student_environment) {
                $options = [
                    'internal_page' => '/users/student_splash',
                ];
                return $this->getTemporaryCakeLoginUrl($options);
            }
        }
//        }
        return $this->getTemporaryCakeLoginUrl($options);
    }

    public function loginThisUser()
    {
        Auth::login($this);
        session()->put('session_hash', $this->getAttribute('session_hash'));
    }

    /**
     * @return mixed
     */
    public function getTemporaryCakeLoginUrl($options = null)
    {
        $temporaryLogin = TemporaryLogin::createForUser($this);
        if ($options) {
            $temporaryLogin->setAttribute('options', $options)->save();
        }

        return $temporaryLogin->createCakeUrl();
    }

    public function inSchoolLocationAsUser(User $user)
    {
        if (!$this->schoolLocation || !$user->schoolLocation) {
            return false;
        }

        if ($this->schoolLocation->is($user->schoolLocation)) {
            return true;
        }

        if ($this->isAllowedToSwitchToSchoolLocation($user->schoolLocation)) {
            return true;
        }

//        if ($user->isAllowedToSwitchToSchoolLocation($user->schoolLocation)) {
//            return true;
//        }

        return false;
    }

    public function inSameKoepelAsUser(User $user)
    {
        if (empty($this->schoolLocation) || empty($this->schoolLocation->school_id)) {
            return false;
        }

        if (empty($user->schoolLocation) || empty($user->schoolLocation->school_id)) {
            return false;
        }

        return $this->schoolLocation->school->is($user->schoolLocation->school);
    }

    public function removeEckId()
    {
        $this->eckidFromRelation()->delete();
        return $this;
    }

    public function removeExternalId()
    {
        $this->external_id = null;
        return $this;
    }

    public function transferClassesFromUser(User $user)
    {
        if ($user->isA('teacher') && $this->isA('teacher')) {
            $currentSchoolYear = SchoolYearRepository::getCurrentSchoolYear();
            $previousSchoolYear = SchoolYearRepository::getPreviousSchoolYear();

            $oldTeacherRecords = $this->teacher()
                ->withTrashed()
                ->get()
                ->filter(function (Teacher $t) use ($previousSchoolYear, $currentSchoolYear) {
                    return $t->schoolClass()->withoutGlobalScope('visibleOnly')->withTrashed()->first()->school_year_id == ($currentSchoolYear)->getKey()
                        || $t->schoolClass()->withoutGlobalScope('visibleOnly')->withTrashed()->first()->school_year_id == optional($previousSchoolYear)->getKey();
                });
            $user->teacher->each(function ($tRecord) use (
                $oldTeacherRecords,
                &$oldClassesSubjectsDone,
                $currentSchoolYear,
                $previousSchoolYear
            ) {
                if ($myRecord = $oldTeacherRecords->first(function ($oldRecord) use ($tRecord) {
                    return $tRecord->class_id == $oldRecord->class_id && $tRecord->subject_id == $oldRecord->subject_id;
                })) {
                    if ($myRecord->trashed()) {
                        $myRecord->restore();
                    }
                    $tRecord->delete();
                } else {
                    // search for old class with same name and attach subject id
                    $done = false;
                    try {
                        $oldSchoolClass = ImportHelper::getOldSchoolClassByNameOptionalyLeaveCurrentOut($this->school_location_id,
                            $tRecord->schoolClassWithoutVisibleOnlyScope->name, $tRecord->class_id);
                        if ($oldSchoolClass && ImportHelper::isDummySubject($tRecord->subject_id)) {

                            $subjects = $oldTeacherRecords->filter(function ($r) use ($oldSchoolClass) {
                                return $r->schoolClass()->withTrashed()->first()->name === $oldSchoolClass->name && !$r->trashed();
                            })->map(function ($r) {
                                return $r->subject_id;
                            })->unique();

                            $subjects->each(function ($subjectId) use ($tRecord, &$done, $currentSchoolYear) {
                                $tRecord->subject_id = $subjectId;
                                try {
                                    $record = Teacher::withTrashed()
                                        ->where('class_id', $tRecord->class_id)
                                        ->where('user_id', $this->getKey())
                                        ->where('subject_id', $tRecord->subject_id)
                                        ->orderBy('class_id', 'desc')
                                        ->first();
                                    if ($record && $record->schoolClass()->withTrashed()->first()->school_year_id === $currentSchoolYear->getKey()) {
                                        if ($record->trashed()) {
                                            $record->restore();
                                        }
                                    } else {
                                        Teacher::create([
                                            'class_id'   => $tRecord->class_id,
                                            'user_id'    => $this->getKey(),
                                            'subject_id' => $tRecord->subject_id
                                        ]);
                                    }
                                    $tRecord->delete();
                                } catch (\Throwable $e) {
                                    // could be that the teacher class already exists, then you get a database integrity constraint, that's okay we don't do
                                    // anything with it
                                }
                                $done = true;
                            });

                        }
                    } catch (\Throwable $th) {
                        Bugsnag::notifyException($th);
                    }
                    if (!$done) {
                        try {
                            $this->teacher()->save($tRecord);
                        } catch (\Throwable $e) {
                            // could be that the teacher class already exists, then you get a database integrity constraint, that's okay we don't do
                            // anything with it
                        }
                    }
                }
            });
        }
        if ($user->isA('student') && $this->isA('student')) {
            $user->students->each(function ($student) {
                $record = Student::withTrashed()->where('class_id', $student->class_id)->where('user_id',
                    $this->getKey())->first();
                if ($record) {
                    if ($record->trashed()) {
                        $record->restore();
                    }
                } else {
                    $this->students()->save(
                        Student::create([
                            'class_id' => $student->class_id,
                            'user_id'  => $this->id,
                        ])
                    );
                }
                $student->delete();
            });
//            $this->students()->saveMany($user->students);
        }

        $user->refresh();
        return $this;
    }

    public function hasNeedsToAcceptGeneralTerms()
    {
        if($this->schoolLocation->hasClientLicense()) {
            return false;
        }
        return (
            $this->isA('teacher')
            && $this->hasNoActiveLicense()
            && $this->generalTermsLog()->count() == 1
            && null == $this->generalTermsLog->accepted_at
            && $this->generalTermsValidationHasExpired()
        );
    }

    private function generalTermsValidationHasExpired()
    {
        return $this->generalTermsLog->created_at->startOfDay()->addDays(config('custom.default_general_terms_days'))->isBefore(Carbon::now()->startOfDay());
    }

    public function createGeneralTermsLogIfRequired()
    {
        if (
            $this->isA('teacher')
            && $this->schoolLocation->hasTrialLicense()
            && $this->hasNoActiveLicense()
            && $this->generalTermsLog()->count() == 0
        )
        {
            $this->generalTermsLog()->create();
        }
        return $this;
    }

    public function hasNoActiveLicense()
    {
        return $this->schoolLocation->licenses()->count() == 0 || $this->schoolLocation->licenses()->where('end', '>',
                Carbon::now())->count() == 0;
    }

    public function addJobUnique($job)
    {
        if (GlobalStateHelper::getInstance()->isQueueAllowed()) {
            if (!in_array($job, $this->uniqueJobs)) {
                Queue::push($job);
                $this->uniqueJobs[] = $job;
            }
        }
    }

    public function verifyPassword($attemptedPassword)
    {
        return Hash::check($attemptedPassword, $this->password);
    }

    public function scopeGuests($query)
    {
        return $query->where('guest', 1);
    }

    public function scopeWithoutGuests($query)
    {
        return $query->where('guest', 0);
    }

    public function setSessionHash($hash)
    {
        session()->put('session_hash', $hash);
        $this->setAttribute('session_hash', $hash);
        return $this->save();
    }

    public function shouldNotSendMail()
    {
        return $this->guest == true || $this->hasImportMailAddress();
    }

    public function scopeAvailableGuestAccountsForTake($query, $testTake)
    {
        return $query->select('users.uuid', 'users.name', 'users.name_first', 'users.name_suffix', 'test_participants.rating')
            ->guests()
            ->leftJoin('test_participants', 'test_participants.user_id', '=', 'users.id')
            ->where('test_participants.test_take_id', $testTake->getKey());
//            ->where('test_participants.available_for_guests', true);
    }

    public function scopeWhenKnownGuest($query, $guest)
    {
        return $query->when($guest, function ($query) use ($guest) {
            $query->where('name_first', $guest['name_first'])
                ->where('name', $guest['name']);
        });
    }

    public function getActiveLanguage($sessionOverride = false): string
    {
        if (!$sessionOverride && $language = session()->get('locale')) {
            return $language instanceof SystemLanguage ? $language->value : $language;
        }

        if ($language = UserFeatureSetting::getSetting($this, UserFeatureSettingEnum::SYSTEM_LANGUAGE, default: null)) {
            return $language->value;
        }

        return $this->schoolLocation?->school_language ?? BaseHelper::browserLanguage();
    }

    public function hasSingleSchoolLocation()
    {
        return ($this->allowedSchoolLocations()->count() == 1);
    }

    public function hasMultipleSchoolLocations()
    {
        return ($this->allowedSchoolLocations()->count() > 1);
    }

    public function hasSingleSchoolLocationNoSharedSections()
    {
        return ($this->allowedSchoolLocations()->count() == 1 && !$this->isPartOfSharedSection());
    }

    public function hasMultipleSchoolLocationsNoSharedSections()
    {
        return ($this->allowedSchoolLocations()->count() > 1 && !$this->isPartOfSharedSection());
    }

    public function hasSingleSchoolLocationSharedSections()
    {
        return ($this->allowedSchoolLocations()->count() == 1 && $this->isPartOfSharedSection());
    }

    public function hasMultipleSchoolLocationsSharedSections()
    {
        return ($this->allowedSchoolLocations()->count() > 1 && $this->isPartOfSharedSection());
    }

    public function getLanguageReadspeaker()
    {
        $locale = app()->getLocale();
        switch ($locale) {
            case 'nl':
                return 'nl_nl';
            case 'en':
                return 'en_uk';
            default:
                return 'nl_nl';
        }
    }

    public function getSearchFilterDefaultsTeacher()
    {
        if (!$this->isA('teacher') || $this->isToetsenbakker()) {
            return [];
        }

        $currentPeriod = PeriodRepository::getCurrentPeriod();
        if ($currentPeriod == null) {
            return [];
        }

        $results = DB::table('teachers')
            ->where([
                ['user_id', $this->getKey()],
                ['school_classes.school_year_id', $currentPeriod->school_year_id],
            ])
            ->join('school_classes', 'class_id', 'school_classes.id')
            ->select(['subject_id', 'education_level_id', 'education_level_year'])
            ->where('school_classes.school_location_id', auth()->user()->school_location_id)
            ->whereNull('school_classes.deleted_at')
            ->get();

        return [
            'subject_id'           => Subject::filtered(['user_current' => Auth::id()], [])->pluck('id')->toArray(),
            'education_level_id'   => $results->map(function ($result) {
                return $result->education_level_id;
            })->unique()->values()->toArray(),
            'education_level_year' => $results->map(function ($result) {
                return $result->education_level_year;
            })->unique()->values()->toArray(),
        ];
    }

    public function getFormalNameAttribute()
    {
        return sprintf('%s. %s %s', mb_substr($this->name_first, 0, 1, 'utf-8'), $this->name_suffix, mb_convert_encoding($this->name, 'utf-8'));
    }

    public function getFormalNameWithCurrentSchoolLocationShortAttribute()
    {
        $schoolLocation = $this->schoolLocation->name;
        if (strlen($schoolLocation) > 30) {
            $schoolLocation = sprintf('%s ...', substr($schoolLocation, 0, 28 - strlen($this->formal_name)));
        }

        return sprintf('%s(<span id="active_school">%s</span>)', $this->formal_name, $schoolLocation);
    }

    public function getFormalNameWithCurrentSchoolLocationAttribute()
    {
        return sprintf('%s(%s)', $this->formal_name, $this->schoolLocation->name);
    }

    public function scopeTeachersForStudent($query, User $student)
    {
        if (!$student->isA('student')) {
            throw new \Exception('Not a valid student');
        }

        return $query->whereIn(
            'id',
            Student::select('teachers.user_id')
                ->join('teachers', function ($join) use ($student) {
                    $join->on('students.class_id', '=', 'teachers.class_id')
                        ->where('students.user_id', '=', $student->id);
                })
        );
    }

    public function loadPValueStatsForAllSubjects()
    {
        $value = Subject::filterForStudent($this)->get()
            ->map(fn($subject) => PValueRepository::getPValuesForStudent($this, $subject))
            ->map(fn($user) => $user->developedAttainments)
            ->flatten()
            ->groupBy(fn($attainment) => $attainment->base_subject_id)
            ->map->avg(function ($attainment) {
                return $attainment->total_p_value;
            })->mapWithKeys(fn($item, $key) => [BaseSubject::find($key)->name => $item]);

        $this->setRelation('pValueStatsForAllSubjects', $value);
        return $this;
    }

    public function createTrialPeriodRecordIfRequired()
    {
        if (!$this->isA('Teacher')) {
            return false;
        }

        return $this->allowedSchoolLocations()->each(function ($location) {
            if (!$location->hasTrialLicense() || $this->trialPeriods()->withSchoolLocation($location)->exists()) {
                return true;
            }
            return $this->trialPeriods()->create([
                'school_location_id' => $location->getKey()
            ]);
        });
    }

    public function canHaveGeneralText2SpeechPrice()
    {
        $roles = Roles::getUserRoles($this);
        foreach ($roles as $role) {
            if (in_array($role, UserHelper::TEXT2SPEECH_PRICE_ROLES)) {
                return true;
            }
        }
        return false;
    }

    private function handleSchoolLocationsForNewTeacher()
    {
        if ($schoolLocation = SchoolLocation::find($this->school_location_id)) {
            $this->addSchoolLocation($schoolLocation);
        }
    }

    public function getTrialSchoolLocations()
    {
        return $this->allowedSchoolLocations()
            ->where('license_type', SchoolLocation::LICENSE_TYPE_TRIAL)
            ->select(['id', 'name', 'license_type', 'uuid'])
            ->get();
    }

    public function getDefaultAttainmentMode()
    {
//        SchoolClass::where('user_id', $this->id)

        return 'LEARNING_GOAL';
    }

    public function scopeToetsenbakkers($query)
    {
        return $query->whereIn(
            'id',
            SchoolLocationUser::select('user_id')
                ->whereIn(
                    'school_location_id',
                    SchoolLocation::select('id')->where('customer_code', config('custom.TB_customer_code'))
                )
        );
    }

    private function setForcePasswordChangeIfRequired(): void
    {
        if (app()->runningInConsole()) return;
        if (!$this->isDirty(['password']) || $this->isDirty('password_expiration_date')) {
            return;
        }

        if(Auth::id() && Auth::id() !== $this->id) {
            $this->password_expiration_date = Carbon::now();
            return;
        }
        $this->password_expiration_date = null;
    }

    public function getSessionLengthAttribute():int
    {
        $minutes = (int)UserFeatureSetting::getSetting(
            user   : $this,
            title  : UserFeatureSettingEnum::AUTO_LOGOUT_MINUTES,
            default: UserFeatureSettingEnum::AUTO_LOGOUT_MINUTES->initialValue(),
        );
        return session('extensionTime', $minutes * 60);
    }

    public function getUseAutoLogOutAttribute():int
    {
        return (bool)UserFeatureSetting::getSetting(
            user   : $this,
            title  : UserFeatureSettingEnum::ENABLE_AUTO_LOGOUT,
            default: UserFeatureSettingEnum::ENABLE_AUTO_LOGOUT->initialValue(),
        );
    }

    public function getNormalizationSettings()
    {
        return collect([
            UserFeatureSettingEnum::GRADE_DEFAULT_STANDARD,
            UserFeatureSettingEnum::GRADE_STANDARD_VALUE,
            UserFeatureSettingEnum::GRADE_CESUUR_PERCENTAGE,
        ])->mapWithKeys(function ($enum) {
                return [$enum->value => UserFeatureSetting::getSetting($this, $enum, default: $enum->initialValue())];
            });
    }

    public function getUserDataObject(): UserData
    {
        return new UserData([
            'username'    => $this->username,
            'uuid'        => $this->uuid,
            'name_first'  => $this->name_first,
            'name_suffix' => $this->name_suffix,
            'name'        => $this->name,
            'gender'      => $this->gender,
        ]);
    }

    public function wordLists()
    {
        return $this->hasMany(WordList::class);
    }

    public function words()
    {
        return $this->hasMany(Word::class);
    }
}
