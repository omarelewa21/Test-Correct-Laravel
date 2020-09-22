<?php namespace tcCore\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Response;
use tcCore\BaseSubject;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\DestroyUserRequest;
use tcCore\Http\Requests\UpdatePasswordForUserRequest;
use tcCore\Http\Requests\UserSwitchSchoolLocationRequest;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\Lib\Repositories\AverageRatingRepository;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\TeacherRepository;
use tcCore\Lib\User\Factory;
use tcCore\OnboardingWizardUserStep;
use tcCore\Subject;
use tcCore\User;
use tcCore\Http\Requests\CreateUserRequest;
use tcCore\Http\Requests\UpdateUserRequest;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\School;
use tcCore\SchoolClass;

class UsersController extends Controller
{

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
                $users->transform(function (User $u) {
                    $u->is_temp_teacher = $u->getIsTempTeacher();
                    return $u;
                });
                return Response::make($users, 200);
                break;
        }
    }

    /**
     * Switch School Location
     *
     * @param User $user
     * @param UpdateUserRequest $request
     * @return Response
     */
    public function switch_school_location(User $user, UserSwitchSchoolLocationRequest $request)
    {
        // nothing to do
        if ($user->school_location_id == $request->get('school_location_id')) {
            return Response::make($user, 200);
        }

        DB::beginTransaction();
        try {
            // get a corresponding teacher within the same new school location
            $teacher = SchoolHelper::getSomeTeacherBySchoolLocationId($request->get('school_location_id'));
            if (null === $teacher) {
                throw new \Exception(' target school must contain at least one teacher');
            }

            ActingAsHelper::getInstance()->setUser($teacher);

            $userFactory = new Factory(new User());
            $newUser = $userFactory->generate(
                $data = array_merge(
                    $request->validated(), [
                        'name_first'         => $user->name_first,
                        'name_suffix'        => $user->name_suffix,
                        'name'               => $user->name,
                        'user_roles'         => 1, // Teacher;
                        'send_welcome_email' => 1,
                        'username'           => $user->username,
                        'abbreviation'       => $user->abbreviation,
                    ]
                )
            );

            // we need to do this as with the userfactory the password is generated by bcrypt and we need to have the same as the old one
            $newUser->password = $user->password;
            $newUser->save();

            // we can always get the old user by checking the creation date of the new user and use that to see the vervallenDATETIME of the old user
            $user->username = sprintf('vervallen%d%s', date('YmdHis'), explode('@', $user->username)[1]);
            $user->save();
            $user->delete();

            DB::table('onboarding_wizard_user_steps')->where('user_id', $user->getKey())->update(['user_id' => $newUser->getKey()]);
            DB::table('onboarding_wizard_user_states')->where('user_id', $user->getKey())->update(['user_id' => $newUser->getKey()]);

        } catch (\Exception $e) {
            DB::rollBack();
            logger('Failed to switch school location of teacher' . $e);
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

        if(!Auth::user()->isA(['Administrator','Account manager']) && Auth::user()->school_location_id !== null) {
            $data['school_location_id'] = ActingAsHelper::getInstance()->getUser()->school_location_id;//SchoolHelper::getTempTeachersSchoolLocation()->getKey();
        }

        //UUID to ID mapping
        if (isset($data['school_id'])) {
            $data['school_id'] = School::whereUuid($data['school_id'])->first()->getKey();
        }

        if (isset($data['add_mentor_school_class'])) {
            $data['add_mentor_school_class'] = SchoolClass::whereUuid($data['add_mentor_school_class'])->first()->getKey();
        }
        
        if (isset($data['manager_school_classes'])) {
            foreach ($data['manager_school_classes'] as $key => $value) {
                $data['manager_school_classes'][$key] = SchoolClass::whereUuid($value)->first()->getKey();
            }
        }

        if (isset($data['student_parents_of'])) {
            foreach ($data['student_parents_of'] as $key => $value) {
                $data['student_parents_of'][$key] = User::whereUuid($value)->first()->getKey();
            }
        }

        $user = (new UserHelper())->createUserFromData($data);

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
//            dispatch_now(new SendWelcomeMail($userId, $request->get('url')));
        }

        return Response::make($users, 200);
    }

    /**
     * Display the specified user.
     *
     * @param User $user
     * @return Response
     */
    public function show(User $user, Request $request)
    {
        $user->load('roles', 'studentSchoolClasses', 'managerSchoolClasses', 'mentorSchoolClasses', 'teacher', 'teacher.schoolClass', 'teacher.subject', 'salesOrganization', 'school.schoolLocations', 'schoolLocation');

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
                $query->select(['test_participants.*', 'test_takes.time_start', 'test_takes.test_take_status_id AS test_take_test_take_status_id', 'tests.name'])->join('test_takes', 'test_participants.test_take_id', '=', 'test_takes.id')->join('tests', 'test_takes.test_id', '=', 'tests.id')->orderBy('test_takes.time_start', 'DESC');
            }]);
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
            $user->setAttribute('password', \Hash::make($request->get('password')));
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
            $user->setAttribute('password', \Hash::make($request->get('password')));
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
    public function update(User $user, UpdateUserRequest $request)
    {

        // Je gaat eruit met updateStudent, maar die kan enkel het wachtwoord aanpassen. Ik denk dat je wilt weten wie het update request uitvoert
        // als dat een student is dan moet die naar updateStudent en anders mag ook de rest....
        if (Auth::user()->hasRole('Student')) return $this->updateStudent($user, $request);

        $data = $request->all();

        //UUID to ID mapping
        if (isset($data['school_id'])) {
            $data['school_id'] = School::whereUuid($data['school_id'])->first()->getKey();
        }

        if (isset($data['add_mentor_school_class'])) {
            $data['add_mentor_school_class'] = SchoolClass::whereUuid($data['add_mentor_school_class'])->first()->getKey();
        }

        if (isset($data['manager_school_classes'])) {
            foreach ($data['manager_school_classes'] as $key => $value) {
                $data['manager_school_classes'][$key] = SchoolClass::whereUuid($value)->first()->getKey();
            }
        }

        if (isset($data['student_parents_of'])) {
            foreach ($data['student_parents_of'] as $key => $value) {
                $data['student_parents_of'][$key] = User::whereUuid($value)->first()->getKey();
            }
        }
        
        $user->fill($data);

        if ($request->filled('password')) {
//		    logger('try updating passwrd '. $request->get('password'));
            $user->setAttribute('password', \Hash::make($request->get('password')));
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

        if ($user->delete()) {
            return Response::make($user, 200);
        } else {
            return Response::make('Failed to delete user', 500);
        }
    }
}
