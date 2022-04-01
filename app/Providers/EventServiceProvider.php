<?php namespace tcCore\Providers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\SamlMessage;
use tcCore\User;

class EventServiceProvider extends ServiceProvider {

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'event.name' => [
            'EventListener',
        ],
        'tcCore\Events\UserLoggedInEvent' => [
            'tcCore\Listeners\AddLoginLog',
            'tcCore\Listeners\SolveFailedLogin',
        ]
    ];

    public function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        Event::listen('Aacotroneo\Saml2\Events\Saml2LoginEvent', function (Saml2LoginEvent $event) {
            $messageId = $event->getSaml2Auth()->getLastMessageId();
            // Add your own code preventing reuse of a $messageId to stop replay attacks
            $user = $event->getSaml2User();
            $attr = $user->getAttributes();

            $entreeHelper = new EntreeHelper($attr, $messageId);

            $entreeHelper->blockIfReplayAttackDetected();

            $entreeHelper->blockIfEckIdAttributeIsNotPresent();

            $entreeHelper->handleIfRegistering();

            $entreeHelper->redirectIfBrinUnknown();

            $entreeHelper->redirectIfBrinNotSso();

            $entreeHelper->blockIfSchoolLvsActiveNoMailNotAllowedWhenMailAttributeIsNotPresent();

            $entreeHelper->redirectIfUserWasNotFoundForEckIdAndActiveLVS();

            $entreeHelper->redirectIfUserNotHasSameRole();

            //scenario 5 still needs implementation;
            $entreeHelper->redirectIfScenario5();

            $entreeHelper->redirectIfNoUserWasFoundForEckId();

            $entreeHelper->redirectIfUserNotInSameSchool();

            $entreeHelper->redirectIfNoMailPresentScenario();

            $entreeHelper->handleScenario2IfAddressIsKnownInOtherAccount();

            $entreeHelper->handleScenario1();

            dd('No ECK_id on the request error (something went wrong?) Entree user.');

            //$laravelUser = //find user by ID or attribute
            //if it does not exist create it and go on  or show an error message
        });
    }



}
