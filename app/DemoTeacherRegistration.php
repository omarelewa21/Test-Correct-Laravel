<?php

namespace tcCore;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use tcCore\Http\Helpers\SchoolHelper;
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
                        'school_location' => request('school_location'),
                        'website_url' => request('website_url'),
                        'address' => request('address'),
                        'postcode' => request('postcode'),
                        'city' => request('city'),
                        'gender' => request('gender'),
                        'gender_different' => request('gender_different'),
                        'name_first' => request('name_first'),
                        'name_suffix' => request('name_suffix'),
                        'name' => request('name'),
                        'abbreviation' => request('abbreviation'),
                        'mobile' => request('mobile'),
                        'username' => request('username'),
                        'subjects' => request('subjects'),
                        'remarks' => request('remarks'),
                        'how_did_you_hear_about_test_correct' => request('how_did_you_hear_about_test_correct'),
                        'user_id' => $user->getKey(),
                    ];
                } else {
                    // dit is het scenario dat ik via tel a teacher kom
                    $inviter = User::find($user->invited_by);
                    $parameterBag = [
                        'name_first' => $user->name_first,
                        'name_suffix' => $user->name_suffix,
                        'name' => $user->name,
                        'abbreviation' => $user->abbreviation,
                        'username' => $user->username,
                        'how_did_you_hear_about_test_correct' => sprintf('uitgenodigd door:%s', $inviter->username),
                        'user_id' => $user->getKey(),
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
}
