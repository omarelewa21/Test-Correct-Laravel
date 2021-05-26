<?php namespace tcCore\Providers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

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
	];

	public function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        Event::listen('Aacotroneo\Saml2\Events\Saml2LoginEvent', function (Saml2LoginEvent $event) {
            $messageId = $event->getSaml2Auth()->getLastMessageId();
            // Add your own code preventing reuse of a $messageId to stop replay attacks

            $user = $event->getSaml2User();
            $userData = [
                'id' => $user->getUserId(),
                'attributes' => $user->getAttributes(),
                'assertion' => $user->getRawSamlAssertion()
            ];
            // find user by eckId
            $user = User::findByEckId($userData['attributes']['eckId'])->first();
            if ($user) {
                // update email adress user with the one posted from entree
                // of alleen als t emailadres eindigt op test-correct.nl
                // of alleen als die voldoet aan s_<userId>@test-correct.nl of t_<userId>@test-correct.nl
                $user->redirectToCakeWithTemporaryLogin();
                exit;
            }

            //$laravelUser = //find user by ID or attribute
                //if it does not exist create it and go on  or show an error message
        });
    }



}
