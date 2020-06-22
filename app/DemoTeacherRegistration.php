<?php

namespace tcCore;

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
            Mail::to('support@test-correct.nl')->send(new TeacherRegistered($registration, false));
        });
    }


    //

    public static function registerIfApplicable(User $user)
    {
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
                    'name_first'                          => request('name_first'),
                    'name_suffix'                         => request('name_suffix'),
                    'name'                                => request('name'),
                    'mobile'                              => request('mobile'),
                    'username'                            => request('username'),
                    'subjects'                            => request('subjects'),
                    'remarks'                             => request('remarks'),
                    'how_did_you_hear_about_test_correct' => request('how_did_you_hear_about_test_correct'),
                    'user_id'                            => $user->getKey(),
                ];
            }  else {
                // dit is het scenario dat ik via tel a teacher kom
                $parameterBag = [
                    'name_first'                          => $user->name_first,
                    'name_suffix'                         => $user->suffix,
                    'name'                                => $user->name,
                    'username'                            => $user->username,
                    'how_did_you_hear_about_test_correct' => 'Got invited by a teacher.',
                    'user_id'                            => $user->getKey(),
                ];

                if ($user->emailDomainInviterAndInviteeAreEqual()){
                    if ($inviter = User::find($user->invited_by)) {
                        if ($demoTeacherRegistration = self::whereUsername($inviter->username)->first()) {
                            // merge de attributes;
                            $parameterBag = array_merge(
                                $demoTeacherRegistration->toArray(), $parameterBag
                            );
                            unset($parameterBag['id']);
                        }
                    }
                }
            }

            self::create($parameterBag);
        }
    }

}
