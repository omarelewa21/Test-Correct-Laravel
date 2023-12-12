<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Response;
use tcCore\BaseSubject;
use tcCore\EmailConfirmation;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\AllowOnlyAsTeacherRequest;
use tcCore\Http\Requests\DestroyUserRequest;
use tcCore\Http\Requests\UpdatePasswordForUserRequest;
use tcCore\Http\Requests\UserImportRequest;
use tcCore\Http\Requests\UserMoveSchoolLocationRequest;
use tcCore\Http\Traits\UserNotificationForController;
use tcCore\Jobs\SendOnboardingWelcomeMail;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\Lib\Repositories\AverageRatingRepository;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\TeacherRepository;
use tcCore\Lib\User\Factory;
use tcCore\Subject;
use tcCore\TemporaryLogin;
use tcCore\TrialPeriod;
use tcCore\User;
use tcCore\Http\Requests\CreateUserRequest;
use tcCore\Http\Requests\UpdateUserRequest;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\UserRole;
use tcCore\UserSystemSetting;
use tcCore\Http\Enums\UserSystemSetting as UserSystemSettingEnum;

class UsersController extends Controller
{
    use UserNotificationForController;

    /**
     * Display a listing of the users.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $users = User::filtered($request->get('filter', []), $request->get('order', []))->with('salesOrganization');

        if (is_array($request->get('with')) && in_array('school_location', $request->get('with'))) {
            $users->with('school.schoolLocations', 'schoolLocation');
        }

        if (is_array($request->get('with')) && in_array('studentSchoolClasses', $request->get('with'))) {
            $users->with('studentSchoolClasses');
        }
        if (is_array($request->get('with')) && in_array('trial_info', $request->get('with'))) {
            $users = $users->with(['trialPeriods', 'trialPeriods.schoolLocation:id,name']);
        }

        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                $users = $users->get();
                if (is_array($request->get('with')) && in_array('studentSubjectAverages', $request->get('with'))) {
                    AverageRatingRepository::getSubjectAveragesOfStudents($users);
                }
                return Response::make($users, 200);
                break;
            case 'list':
                return Response::make($users->get(['users.id', 'users.uuid', 'users.name_first', 'users.name_suffix', 'users.name', 'users.username'])->keyBy('uuid'), 200);
                break;
            case 'paginate':
            default:
                $users = $users->paginate(15);
                if (is_array($request->get('with')) && in_array('studentSubjectAverages', $request->get('with'))) {
                    AverageRatingRepository::getSubjectAveragesOfStudents($users);
                }
                if (is_array($request->get('with')) && in_array('trial_info', $request->get('with'))) {
                    $users->each(function ($user) {
                        return $user->trialSchoolLocations = $user->getTrialSchoolLocations();
                    });
                }
                $users->transform(function (User $u) {
                    $u->is_temp_teacher = $u->getIsTempTeacher();
                    return $u;
                });
                return Response::make($users, 200);
                break;
        }
    }

    /**
     * Move School Location
     *
     * @param User $user
     * @param UpdateUserRequest $request
     * @return Response
     */
    public function move_school_location(User $user, UserMoveSchoolLocationRequest $request)
    {
        // nothing to do
        if ($user->school_location_id == $request->get('school_location_id')) {
            return Response::make($user, 200);
        }

        DB::beginTransaction();
        try {
            // get a corresponding teacher within the same new school location
            $teacherOrSchoolManager = SchoolHelper::getSomeTeacherOrSchoolManagerBySchoolLocationId($request->get('school_location_id'));
            if (null === $teacherOrSchoolManager) {
                throw new \Exception(' target school must contain at least one teacher');
            }

            ActingAsHelper::getInstance()->setUser($teacherOrSchoolManager);

            $userFactory = new Factory(new User());
            $newUser = $userFactory->generate(
                $data = array_merge(
                    $request->validated(),
                    [
                        'name_first'         => $user->name_first,
                        'name_suffix'        => $user->name_suffix,
                        'name'               => $user->name,
                        'user_roles'         => 1, // Teacher;
                        'send_welcome_email' => 1,
                        'username'           => $user->username,
                        'abbreviation'       => $user->abbreviation,
                        'invited_by'         => $user->invited_by,
                        'account_verified'  => $user->account_verified
                    ]
                )
            );

            // we need to do this as with the userfactory the password is generated by bcrypt and we need to have the same as the old one
            $newUser->password = $user->password;
            $newUser->save();

            $newUser->password_expiration_date = null;
            $newUser->save();

            // we can always get the old user by checking the creation date of the new user and use that to see the vervallenDATETIME of the old user
            $user->username = sprintf('vervallen-%d-%s-%s', $newUser->created_at->format('YmdHis'), $newUser->getKey(), explode('@', $user->username)[1]);
            $user->save();

            // if there are email confirmations waiting, move them to the new user
            if (EmailConfirmation::where('user_id', $user->getKey())->count()) {
                EmailConfirmation::where('user_id', $user->getKey())->update(['user_id' => $newUser->getKey()]);
            }

            $user->delete();

            DB::table('onboarding_wizard_user_steps')->where('user_id', $user->getKey())->update(['user_id' => $newUser->getKey()]);
            DB::table('onboarding_wizard_user_states')->where('user_id', $user->getKey())->update(['user_id' => $newUser->getKey()]);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::make('Failed to switch school location of teacher,' . print_r($e->getMessage(), true), 500);
        }
        DB::commit();
        if ($newUser->save()) {
            return Response::make($newUser, 200);
        } else {
            return Response::make('Failed to update user', 500);
        }
    }

    /**
     * Store a newly created user in storage.
     *
     * @param CreateUserRequest $request
     * @return Response
     */
    public function store(CreateUserRequest $request)
    {

        $data = $request->all();

        if (!Auth::user()->isA(['Administrator', 'Account manager']) && Auth::user()->school_location_id !== null) {
            $data['school_location_id'] = ActingAsHelper::getInstance()->getUser()->school_location_id; //SchoolHelper::getTempTeachersSchoolLocation()->getKey();
        }

        $user = (new UserHelper())->createUserFromData($data);
        if ($user->isA('teacher')) {
            $user->account_verified = Carbon::now();
            $user->save();
        }

        if ($user !== false) {
            return Response::make($user, 200);
        } else {
            return Response::make('Failed to create user', 500);
        }
    }

    /**
     * Offers a download to the specified drawing question from storage.
     *
     * @param User $user
     * @return Response
     */
    public function profileImage(User $user)
    {
        if (File::exists($user->getCurrentProfileImagePath())) {
            return Response::download($user->getCurrentProfileImagePath(), $user->getAttribute('profile_image_name', null));
        } else {
            return Response::make('User profile image not found', 404);
        }
    }

    public function sendWelcomeEmail(Request $request)
    {
        $users = User::filtered($request->get('filter', []), $request->get('order', []))->get(['users.id', 'users.name_first', 'users.name_suffix', 'users.name', 'users.username'])->keyBy('id');

        foreach ($users as $userId => $userData) {
            Queue::push(new SendWelcomeMail($userId, $request->get('url')));
//            dispatch_sync(new SendWelcomeMail($userId, $request->get('url')));
        }

        return Response::make($users, 200);
    }

    public function sendOnboardingWelcomeEmail(AllowOnlyAsTeacherRequest $request)
    {
        try {
            Mail::to(Auth::user()->getEmailForPasswordReset())->queue(new SendOnboardingWelcomeMail(Auth::user()));
        } catch (\Throwable $th) {
            Bugsnag::notifyException($th);
        }

        return Response::make('ok', 200);
    }

    public function confirmEmail(Request $request, EmailConfirmation $emailConfirmation)
    {
        // indien emailConfirmation === null => doorverwijzen naar login pagina
        if ($emailConfirmation === null || null == $emailConfirmation->user) {
            return Response::redirectTo(BaseHelper::getLoginUrl());
        }

        // indien wel oke, gebruiker erbij zoeken en account_verified op nu zetten, vervolgens pagina weergeven met bevestiging en knop naar login pagina
        $user = User::findOrFail($emailConfirmation->user->id);
        $alreadyVerified = true;

        if ($user->account_verified === null) {
            $user->setAttribute('account_verified', Carbon::now());
            $user->save();
            $alreadyVerified = false;
        }

        return view('account_verified', ['name' => $user->name, 'username' => $user->username, 'already_verified' => $alreadyVerified]);
    }

    /**
     * Display the specified user.
     *
     * @param User $user
     * @return Response
     */
    public function show(User $user, Requests\ShowUserRequest $request)
    {
        $user->load(
            'roles',
            'studentSchoolClasses',
            'managerSchoolClasses',
            'mentorSchoolClasses',
            'ownTeachers',
            'ownTeachers.schoolClass',
            'ownTeachers.subject',
            'teacher.schoolClass',
            'teacher.subject',
            'salesOrganization',
            'school.schoolLocations',
            'schoolLocation',
            'trialPeriods'
        );

        if (is_array($request->get('with')) && in_array('studentSubjectAverages', $request->get('with'))) {
            AverageRatingRepository::getSubjectAveragesOfStudents(Collection::make([$user]));
        }

        if (is_array($request->get('with')) && (in_array('studentAverageGraph', $request->get('with')) || array_key_exists('studentAverageGraph', $request->get('with')))) {
            $baseSubjectOrSubject = null;
            $scorePercentage = false;
            if (array_key_exists('studentAverageGraph', $request->get('with'))) {
                $averageGraphSettings = $request->get('with')['studentAverageGraph'];

                if (array_key_exists('baseSubjectId', $averageGraphSettings)) {
                    $baseSubjectOrSubject = BaseSubject::find($averageGraphSettings['baseSubjectId']);
                }

                if (array_key_exists('subjectId', $averageGraphSettings)) {
                    $baseSubjectOrSubject = Subject::find($averageGraphSettings['subjectId']);
                }

                if (array_key_exists('percentage', $averageGraphSettings) && $averageGraphSettings['percentage'] == true) {
                    $scorePercentage = true;
                }
            }
            AverageRatingRepository::getAverageOverTimeOfStudent($user, $baseSubjectOrSubject, $scorePercentage);
        }

        if (is_array($request->get('with')) && (in_array('studentPValues', $request->get('with')) || array_key_exists('studentPValues', $request->get('with')))) {
            $baseSubjectOrSubject = null;
            if (array_key_exists('studentPValues', $request->get('with'))) {
                $studentPValuesSettings = $request->get('with')['studentPValues'];

                if (array_key_exists('baseSubjectId', $studentPValuesSettings)) {
                    $baseSubjectOrSubject = BaseSubject::find($studentPValuesSettings['baseSubjectId']);
                }

                if (array_key_exists('subjectId', $studentPValuesSettings)) {
                    $baseSubjectOrSubject = Subject::find($studentPValuesSettings['subjectId']);
                }
            }
            PValueRepository::getPValuesForStudent($user, $baseSubjectOrSubject);
        }

        if (is_array($request->get('with')) && in_array('teacherComparison', $request->get('with'))) {
            PValueRepository::compareTeacher($user);
        }

        if (is_array($request->get('with')) && in_array('teacherSchoolClassAverages', $request->get('with'))) {
            TeacherRepository::getTeacherParallelSchoolClasses($user);
        }

        if (is_array($request->get('with')) && in_array('testsParticipated', $request->get('with'))) {
            $user->load(['testParticipants' => function ($query) {
                $query->select(['test_participants.*', 'test_takes.uuid as test_take_uuid', 'test_takes.time_start', 'test_takes.test_take_status_id AS test_take_test_take_status_id', 'tests.name', 'test_takes.show_grades'])->join('test_takes', 'test_participants.test_take_id', '=', 'test_takes.id')->join('tests', 'test_takes.test_id', '=', 'tests.id')->orderBy('test_takes.time_start', 'DESC');
            }]);
        }

        if (Auth::user()->isA('Support') && is_array($request->get('with')) && in_array('sessionHash', $request->get('with'))) {
            $user->makeVisible('session_hash');
        }

        if ($this->hasTeacherRole($user)) {
            $externalId = $this->getExternalIdForSchoolLocationOfLoggedInUser($user);
            $user->teacher_external_id = $externalId;
        }

        return Response::make($user, 200);
    }


    /**
     * +     * UpdateStudent the specified user in storage.
     * +     *
     * +     * @param User $user
     * +     * @param UpdateUserRequest $request
     * +     * @return Response
     * +     */
    public function updateStudent(User $user, UpdateUserRequest $request)
    {

        if ($request->has('password')) {
            Log::stack(['loki'])->info("updateStudent@UsersController.php password reset");
            $user->setAttribute('password', $request->get('password'));
            $this->sendPasswordChangedMail($user);
        }

        if ($user->save()) {
            return Response::make($user, 200);
        } else {
            return Response::make('Failed to update user', 500);
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param User $user
     * @param UpdateUserRequest $request
     * @return Response
     */
    public function updatePasswordForUser(User $user, UpdatePasswordForUserRequest $request)
    {

        $user->fill($request->only('password'));

        if ($request->filled('password')) {
            Log::stack(['loki'])->info("updatePasswordForUser@UsersController.php password reset");
            $user->setAttribute('password', $request->get('password'));
            $this->sendPasswordChangedMail($user);
        }

        if ($user->save()) {
            return Response::make($user, 200);
        } else {
            return Response::make('Failed to update user', 500);
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param User $user
     * @param UpdateUserRequest $request
     * @return Response
     */

    public function updateUserFeature(User $user, Request $request)
    {
        $settingEnum = UserSystemSettingEnum::tryFrom($request->input('info') ?? '');
        if (!$settingEnum) {
            return Response::make(['error' => 'Invalid setting key'], 400);
        }
        $currentValue = UserSystemSetting::getSetting($user, $settingEnum);
        $newValue = !$currentValue;
        UserSystemSetting::setSetting($user, $settingEnum, $newValue);
        return Response::make(['success' => 'Feature updated successfully']);
    }

    public function getUserSystemSettings(User $user)
    {
        return Response::make(UserSystemSetting::getAll($user),200);
    }
 
    public function update(User $user, UpdateUserRequest $request)
    {

        // Je gaat eruit met updateStudent, maar die kan enkel het wachtwoord aanpassen. Ik denk dat je wilt weten wie het update request uitvoert
        // als dat een student is dan moet die naar updateStudent en anders mag ook de rest....
        if (Auth::user()->hasRole('Student')) return $this->updateStudent($user, $request);

        $user->fill($request->all());

        if ($request->filled('password')) {
            Log::stack(['loki'])->info("update@UsersController.php password reset");
            $user->setAttribute('password', $request->get('password'));
            $this->sendPasswordChangedMail($user);
        }

        if ($user->save()) {
            return Response::make($user, 200);
        } else {
            return Response::make('Failed to update user', 500);
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param User $user
     * @return Response
     */
    public function destroy(User $user, DestroyUserRequest $request)
    {
        // 20190515 security issue with users deleting users just by id, see trello
        // for safety now disabled
        // @TODO fix security issue with deletion of users (as well update/ add and such)
        //	    return Response::make($user,200);

        try {
            if ($user->delete()) {
                UserRole::where('user_id', $user->id)->delete();
                return Response::make($user, 200);
            } else {
                return Response::make('Failed to delete user', 500);
            }
        } catch (\Exception $e) {
            return Response::make($e->getMessage(), 403);
        }
    }

    public function temporaryLogin(Request $request, $tlid)
    {
        $temporaryLogin = TemporaryLogin::whereUuid($tlid)->where('created_at', '>', Carbon::now()->subSeconds(10))->first();
        if (!$temporaryLogin) {
            return;
        }

        $user = User::where('id', $temporaryLogin->user_id)->first();
        //        $temporaryLogin->forceDelete();

        Auth::login($user);
        return (new UserHelper())->handleAfterLoginValidation(Auth::user(), true);
    }

    public function isAccountVerified(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        if ($user && $user->account_verified) {
            return Response::make(true, 200);
        }
        return Response::make(null, 204);
    }

    public function toggleAccountVerified(User $user)
    {
        $user->toggleVerified();
        if ($user->account_verified) {
            return new JsonResponse(['account_verified' => $user->account_verified->format('Y-m-d H:i:s')]);
        }

        return new JsonResponse(['account_verified' => '']);
    }

    public function import(UserImportRequest $request, $type)
    {
        $userRoles = [];
        if ($type == 'teacher') {
            $userRoles  = [1];
        }
        if ($type == 'student') {
            $userRoles  = [3];
        }
        $defaultData = [
            'user_roles'         => $userRoles,
            'school_location_id' => auth()->user()->school_location_id,
        ];

        DB::beginTransaction();
        try {
            $users = collect($request->all()['data'])->map(function ($row) use ($defaultData, $type) {
                $attributes = array_merge($row, $defaultData);

                $user = User::where('username', $attributes['username'])->first();
                if ($user) {
                    if ($user->isA('teacher') && $type == 'teacher') {
                        $this->handleExternalId($user, $attributes);
                    } else {
                        throw new \Exception('conflict: exists but not teacher');
                    }
                } else {
                    $userFactory = new Factory(new User());
                    $user = $userFactory->generate(
                        array_merge(
                            $attributes,
                            ['account_verified' => Carbon::now()]
                        )
                    );
                }
                $user->save();
                return $user;
            });
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::make('Failed to import teachers' . print_r($e->getMessage(), true), 500);
        }
        DB::commit();

        return Response::make($users, 200);
    }

    protected function handleExternalId(&$user, $attributes)
    {
        if (!array_key_exists('external_id', $attributes)) {
            return;
        }
        if (!array_key_exists('school_location_id', $attributes)) {
            return;
        }
        $schoolLocations = $user->allowedSchoolLocations;
        foreach ($schoolLocations as $schoolLocation) {
            if ($schoolLocation->pivot->external_id == $attributes['external_id'] && $attributes['school_location_id'] == $schoolLocation->id) {
                return;
            }
        }
        foreach ($schoolLocations as $schoolLocation) {
            if ((is_null($schoolLocation->pivot->external_id) || $schoolLocation->pivot->external_id == '') && $attributes['school_location_id'] == $schoolLocation->id) {
                $user->allowedSchoolLocations()->detach($schoolLocation);
            }
        }
        $user->allowedSchoolLocations()->attach([$attributes['school_location_id'] => ['external_id' => $attributes['external_id']]]);
        $user->external_id = $attributes['external_id'];
    }

    protected function getExternalIdForSchoolLocationOfLoggedInUser(User $user)
    {
        $allowedSchoolLocation = $user->allowedSchoolLocations()->wherePivot('school_location_id', Auth::user()->school_location_id)->first();
        if (is_null($allowedSchoolLocation)) {
            return $user->external_id;
        }
        return $allowedSchoolLocation->pivot->external_id;
    }

    public function hasTeacherRole($user)
    {
        foreach ($user->roles as $role) {
            if ($role->name == 'Teacher') {
                return true;
            }
        }
        return false;
    }

    public function getTimeSensitiveUserRecords(User $user)
    {
        $records = [
            'userGeneralTermsLog' => $user->generalTermsLog,
            'trialPeriod' => $user->trialPeriodsWithSchoolLocationCheck
        ];
        return Response::make($records, 200);
    }

    public function setGeneralTermsLogAcceptedAtForUser(User $user)
    {
        $user->generalTermsLog()->update(['accepted_at' => Carbon::now()]);
        return Response::make(true, 200);
    }

    public function verifyPassword(User $user)
    {
        if ($user->verifyPassword(request()->get('password'))) {
            return Response::make($user, 200);
        }
        return Response::make('refused', 403);
    }

    public function getReturnToLaravelUrl(Request $request, User $user)
    {
        $parameters = [];
        if ($state = $request->get('state')) {
            if ($state == 'discussed') {
                $parameters = ['login_tab' => 2, 'guest_message_type' => 'success', 'guest_message' => 'done_with_colearning'];
            }
            if ($state == 'glance') {
                $parameters = ['login_tab' => 2, 'guest_message_type' => 'success', 'guest_message' => 'done_with_review'];
            }
        }

        $url = [];
        if ($user->guest) {
            //Paste relative route behind config base url because route() defaults to https, making it break on non https envs
            $relativeRoute = route('auth.login', $parameters, false);
            $finalUrl = sprintf('%s%s', config('app.base_url'), substr($relativeRoute, 0, 1) === '/' ? substr($relativeRoute, 1) : $relativeRoute);
            $url['url'] = $finalUrl;
        }

        return Response($url, 200);
    }

    public function updateTrialDate(Request $request, User $user)
    {
        $tp = TrialPeriod::whereUuid($request->get('user_trial_period_uuid'))->get();
        DB::table('users')->where('id', $user->id)->update(['has_package' => $request->get('has_package')]);
        if ($tp->count()) {
            $tp->first()->update([
                'trial_until' => Carbon::parse($request->get('date'))->startOfDay()
            ]);
        }

        return Response::make($user, 200);
    }

    public function createTrialRecord(Request $request, User $user)
    {
        $createdTrialRecordUuid = $user->createTrialPeriodRecordIfRequired(false, $request->get('school_location_uuid'));

        if($createdTrialRecordUuid) {
            return Response::make(["uuid" => $createdTrialRecordUuid], 200);
        }

        return Response::make(false, 422);
    }

    public function toetsenbakkers(Request $request)
    {
        $toetsenbakkers = Auth::user()->isA('Account manager')
            ? User::toetsenbakkers()->notDemo()->get()->each->append('name_full')
            : [];

        return Response::make($toetsenbakkers, 200);
    }

    public function account(Request $request)
    {
        if (!Auth::user()->isA('Teacher')) {
            return redirect()->to(url()->previous());
        }

        return view('account-settings', ['role' => 'teacher', 'user' => Auth::user()]);
    }
}
