<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use tcCore\DemoTeacherRegistration;
use tcCore\Exceptions\Handler;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\Http\Requests\CreateDemoAccountRequest;
use tcCore\Http\Requests\UpdateDemoAccountRequest;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\Lib\User\Factory;
use tcCore\Mail\TeacherInTestSchoolTriesToUpload;
use tcCore\Mail\TeacherRegistered;
use tcCore\Teacher;
use tcCore\User;

class DemoAccountController extends Controller
{
    public function show(User $user, Request $request)
    {
        if (($user->getKey() !== Auth::user()->getKey()) && !Auth::user()->isA('Administrator')) {
            abort(403);
        }
        $data = DemoTeacherRegistration::where('user_id', $user->getKey())->first();
        return Response::make($data, 200);
    }

    public function showRegistrationCompleted(User $user)
    {
        if ($user->getKey() !== Auth::user()->getKey()) {
            abort(403);
        }
        $return = true;
        if ($user->schoolLocation->is(SchoolHelper::getTempTeachersSchoolLocation())) {
            $registration = DemoTeacherRegistration::where('user_id', $user->getKey())->first();
            if (null !== $registration) {
                $validator = Validator::make($registration->toArray(), $this->getRules());

                $return = $validator->fails();
            }
        }
        return Response::make(['status' => $return], 200);
    }

    public function update(User $user, UpdateDemoAccountRequest $request)
    {
        if (($user->getKey() !== Auth::user()->getKey()) && !Auth::user()->isA('Administrator')) {
            abort(403);
        }
        try {
            $registration = DemoTeacherRegistration::where('user_id', $user->getKey())->firstOrFail();

            $validatedRegistration = $request->validate($this->getRules());
        } catch (ValidationException $e) {
            $e->status = 425;
            $handler = resolve(Handler::class);
            return $handler->render($request, $e);
        }
        DB::beginTransaction();

        try {
            $registration->update($validatedRegistration);
            // don't mail when admin;
            if (!Auth::user()->isA('Administrator')) {
                Mail::to('support@test-correct.nl')->send(new TeacherInTestSchoolTriesToUpload($registration));
            }
        } catch (\Exception $e) {
            DB::rollBack();
//            logger('Failed to update registered teacher' . $e);
            return Response::make('Failed to update registered teacher' . print_r($e->getMessage(), true), 500);
        }
        DB::commit();


        return Response::make(['status' => 'ok'], 200);
    }

    public function store(CreateDemoAccountRequest $request)
    {
        // signal the DemoTeacherRegistration model to record the user in a DemoTeacherRegistration object;
        request()->merge([
            'shouldRegisterUser' => true,
        ]);
        if (auth()->user() === null) {
            // zorgen dat de user altijd bestaat.
            auth()->login(SchoolHelper::getBaseDemoSchoolUser());
        }
        try {
            $validatedRegistration = $request->validate([
                'school_location'                     => 'required',
                'website_url'                         => 'required',
                'address'                             => 'required',
                'postcode'                            => 'required',
                'city'                                => 'required',
                'gender'                              => 'required',
                'gender_different'                    => 'sometimes',
                'name_first'                          => 'required',
                'name_suffix'                         => 'sometimes',
                'name'                                => 'required',
                'mobile'                              => 'required',
                'username'                            => 'required|email',
                'abbreviation'                        => 'required',
                'subjects'                            => 'required',
                'remarks'                             => 'sometimes',
                'how_did_you_hear_about_test_correct' => 'sometimes',
            ]);
        } catch (ValidationException $e) {
            $e->status = 425;
            $handler = resolve(Handler::class);
            return $handler->render($request, $e);
        }
        DB::beginTransaction();

        try {

            $user = User::where('username', request('username'))->first();
            if (!$user) {
//                    if ($user->isA('teacher')) {
//                        }else{logger('klas '.$schoolClass->getKey.' bestaat al voor '.$user->getKey());}
//                    }

                $userFactory = new Factory(new User());
                $user = $userFactory->generate(
                    $data = array_merge(
                        $request->all(), [
                            'school_location_id' => SchoolHelper::getTempTeachersSchoolLocation()->getKey(),
                            'school_id'          => SchoolHelper::getTempTeachersSchoolLocation()->school_id,
                            'user_roles'         => 1, // Teacher;
                        ]
                    )
                );

                $demoHelper = (new DemoHelper())->setSchoolLocation(SchoolHelper::getTempTeachersSchoolLocation());

                $teacher = Teacher::withTrashed()
                    ->firstOrNew([
                        'user_id'    => $user->getKey(),
                        'class_id'   => $demoHelper->getDemoClass()->getKey(),
                        'subject_id' => $demoHelper->getDemoSubject()->getKey(),
                    ]);

                $teacher->trashed() ? $teacher->restore() : $teacher->save();

                try {
                    dispatch_now(new SendWelcomeMail($user->getKey()));
                } catch (\Throwable $th) {
                    Bugsnag::notifyException($th);
                }
            } else {
                DemoTeacherRegistration::create($validatedRegistration);
            }
        } catch (\Exception $e) {
            DB::rollBack();
//            logger('Failed to register teacher' . $e);
            return Response::make('Failed to register teacher' . print_r($e->getMessage(), true), 500);
        }
        DB::commit();


        return Response::make(['status' => 'ok'], 200);
    }

    public function notifySupportTeacherTriesToUpload(Request $request)
    {
        $user = User::whereUuid(request('userId'))->firstOrFail();
        $registration = DemoTeacherRegistration::whereUserId($user->id)->firstOrFail();
        try {
            Mail::to('support@test-correct.nl')->send(new TeacherInTestSchoolTriesToUpload($registration));
        } catch (\Throwable $th) {
            Bugsnag::notifyException($th);
        }
    }

    //
    private function getRules()
    {
        return [
            'school_location'                     => 'required',
            'website_url'                         => 'required',
            'address'                             => 'required',
            'postcode'                            => 'required',
            'city'                                => 'required',
            'gender'                              => 'required',
            'gender_different'                    => 'sometimes',
            'name_first'                          => 'required',
            'name_suffix'                         => 'sometimes',
            'name'                                => 'required',
            'mobile'                              => 'required',
            'username'                            => 'required|email',
            'abbreviation'                        => 'required',
            'subjects'                            => 'required',
            'remarks'                             => 'sometimes',
            'how_did_you_hear_about_test_correct' => 'sometimes',
        ];
    }
}