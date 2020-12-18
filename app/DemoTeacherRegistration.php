<?php

namespace tcCore;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\Jobs\SendOnboardingWelcomeMail;
use tcCore\Lib\User\Factory;
use tcCore\Mail\TeacherRegistered;

class DemoTeacherRegistration extends Model
{
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::created(function (DemoTeacherRegistration $registration) {
            $count = DemoTeacherRegistration::where('username', $registration->username)->count();
            try {
                Mail::to('support@test-correct.nl')->send(new TeacherRegistered($registration, $count > 1));
            } catch (\Throwable $th) {
                Bugsnag::notifyException($th);
            }
        });
    }


    //

    public static function registerIfApplicable(User $user)
    {
        if (request('shouldRegisterUser') == true) {

            if ($user->schoolLocation->is(SchoolHelper::getTempTeachersSchoolLocation())) {
                // dit is het scenario dat ik vanaf het formulier van buiten kom.
                if (request('school_location') && request('website_url')) {
                    $parameterBag = [
                        'school_location'                     => request('school_location'),
                        'website_url'                         => request('website_url'),
                        'address'                             => request('address'),
                        'postcode'                            => request('postcode'),
                        'city'                                => request('city'),
                        'gender'                              => request('gender'),
                        'gender_different'                    => request('gender_different'),
                        'name_first'                          => request('name_first'),
                        'name_suffix'                         => request('name_suffix'),
                        'name'                                => request('name'),
                        'abbreviation'                        => request('abbreviation'),
                        'mobile'                              => request('mobile'),
                        'username'                            => request('username'),
                        'subjects'                            => request('subjects'),
                        'remarks'                             => request('remarks'),
                        'how_did_you_hear_about_test_correct' => request('how_did_you_hear_about_test_correct'),
                        'user_id'                             => $user->getKey(),
                    ];
                } else {
                    // dit is het scenario dat ik via tel a teacher kom
                    $inviter = User::find($user->invited_by);
                    $parameterBag = [
                        'name_first'                          => $user->name_first,
                        'name_suffix'                         => $user->name_suffix,
                        'name'                                => $user->name,
                        'abbreviation'                        => $user->abbreviation,
                        'username'                            => $user->username,
                        'how_did_you_hear_about_test_correct' => sprintf('uitgenodigd door:%s', $inviter->username),
                        'user_id'                             => $user->getKey(),
                    ];

                    if ($user->emailDomainInviterAndInviteeAreEqual()) {
                        if ($demoTeacherRegistration = self::whereUsername($inviter->username)->first()) {
                            // merge de attributes;
                            $attrToBeCopied = $demoTeacherRegistration->toArray();

                            foreach (['id', 'gender', 'gender_different', 'subjects', 'remarks', 'created_at', 'mobile'] as $attrToBeIgnored) {
                                unset($attrToBeCopied[$attrToBeIgnored]);
                            };

                            $parameterBag = array_merge(
                                $attrToBeCopied, $parameterBag
                            );
                        }
                    }
                }

                self::create($parameterBag);
            }
        }
    }

    public function addUserToRegistration($password = null)
    {
        try {
            $newRegistration = false;
            $user = User::where('username', request('username'))->first();
            if (!$user) {
                $user = User::where('username', $this->username)->first();
            }
            if (!$user) {
//                    if ($user->isA('teacher')) {
//                        }else{logger('klas '.$schoolClass->getKey.' bestaat al voor '.$user->getKey());}
//                    }
                ;

                $tempTeachersSchoolLocation = SchoolHelper::getTempTeachersSchoolLocation();
                $data = array_merge(
                    $password ? ['password' => $password] : [],
                    $this->toArray(), [
                        'school_location_id' => $tempTeachersSchoolLocation->getKey(),
                        'user_roles'         => 1, // Teacher;
                    ]
                );

                if (auth()->user() === null) {
                    // zorgen dat de user altijd bestaat.
                    auth()->login(SchoolHelper::getBaseDemoSchoolUser());
                }

                $userFactory = new Factory(new User());
                $user = $userFactory->generate($data);

                $demoHelper = (new DemoHelper())->setSchoolLocation($tempTeachersSchoolLocation);

                $teacher = Teacher::withTrashed()
                    ->firstOrNew([
                        'user_id'    => $user->getKey(),
                        'class_id'   => $demoHelper->getDemoClass()->getKey(),
                        'subject_id' => $demoHelper->getDemoSubject()->getKey(),
                    ]);

                $teacher->trashed() ? $teacher->restore() : $teacher->save();

                try {
                    Mail::to($user->getEmailForPasswordReset())->send(new SendOnboardingWelcomeMail($user));
                } catch (\Throwable $th) {
                    Bugsnag::notifyException($th);
                }

                $newRegistration = true;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            logger('Failed to register teacher' . $e);
            throw $e;
//            return Response::make('Failed to register teacher' . print_r($e->getMessage(), true), 500);
        }
        DB::commit();
        return $newRegistration;
    }
}
