<?php namespace tcCore\Providers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
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
            if (array_key_exists('eckId', $userData['attributes']) && ! empty($userData['attributes']['eckId'][0])) {
                $laravelUser = User::findByEckId($userData['attributes']['eckId'][0])->first();
                if ($laravelUser) {
                    $laravelUser->handleEntreeAttributes($userData['attributes']);

                    $url = $laravelUser->getTemporaryCakeLoginUrl();
                    header("Location: $url");
                    exit;
                } else {
                    Session::put('saml_attributes', $user->getAttributes());
                    header("Location: /login?tab=entree");
                    exit;
                }
            }



            dd('No ECK_id on the request error (something went wrong?) Entree user.');

            //$laravelUser = //find user by ID or attribute
            //if it does not exist create it and go on  or show an error message
        });
    }



}
