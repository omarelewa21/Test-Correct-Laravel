<?php
namespace tcCore\Http\Controllers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Saml2Auth;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use tcCore\SamlMessage;

class Saml2Controller extends Controller
{
    private function logger($data)
    {
        //logger($data);
    }

    public function redirectToEntree()
    {
        if(Auth::user()){
            Auth::logout();
        }
        return response()->redirectTo(route('saml2_login',['entree']));
    }

    /**
     * Generate local sp metadata.
     *
     * @param Saml2Auth $saml2Auth
     * @return \Illuminate\Http\Response
     */
    public function metadata(Saml2Auth $saml2Auth)
    {
        $metadata = $saml2Auth->getMetadata();

        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Process an incoming saml2 assertion request.
     * Fires 'Saml2LoginEvent' event if a valid user is found.
     *
     * @param Saml2Auth $saml2Auth
     * @param $idpName
     * @return \Illuminate\Http\Response
     */
    public function acs(Saml2Auth $saml2Auth, $idpName)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)',__FILE__,__METHOD__,__LINE__));

        $errors = $saml2Auth->acs();

        if (!empty($errors)) {
            $message = 'New Saml2 Errors'.PHP_EOL .
                'Last error: '.PHP_EOL.
                $saml2Auth->getLastErrorReason().PHP_EOL.
                'All errors: '. PHP_EOL .
                json_encode($errors['error']);
            $this->logger('with errors '.$message);
            Bugsnag::notifyException(new \Exception($message));
//            logger()->error('Saml2 error_detail', ['error' => $saml2Auth->getLastErrorReason()]);
            session()->flash('saml2_error_detail', [$saml2Auth->getLastErrorReason()]);

//            logger()->error('Saml2 error', ['error' => $errors['error']]);
            session()->flash('saml2_error', $errors);
            return redirect(config('saml2_settings.errorRoute'));
        }
        $user = $saml2Auth->getSaml2User();

        $redirectUrl = $user->getIntendedUrl();
        $this->logger('intended url '.$redirectUrl);

        $this->handleDetails($redirectUrl);

        $redirectUrl = config('saml2_settings.loginRoute');

        event(new Saml2LoginEvent($idpName, $user, $saml2Auth));

        if ($redirectUrl !== null) {
            return redirect($redirectUrl);
        } else {

            return redirect(config('saml2_settings.loginRoute'));
        }
    }

    private function handleDetails($redirectUrl)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)',__FILE__,__METHOD__,__LINE__));
        $sessionAr = [];
        if(Str::contains($redirectUrl,'entreeRegister')){
            $sessionAr['entreeReason'] = 'register';
        }

        $parsedUrlAr = parse_url($redirectUrl);
        if(isset($parsedUrlAr['query'])){
            parse_str($parsedUrlAr['query'], $queryAr);
            if(isset($queryAr['mId'])){
                $messages = SamlMessage::whereUuid($queryAr['mId'])->get();
                if($messages->count()){
                    $message = $messages->first();
                    if(optional($message->data)->url){
                        $sessionAr['finalRedirectTo'] = $message->data->url;
                        $sessionAr['mId'] = $queryAr['mId'];
                    }
                }
            }
        }
        if(count($sessionAr)){
            session($sessionAr);
        }
    }

    /**
     * Process an incoming saml2 logout request.
     * Fires 'Saml2LogoutEvent' event if its valid.
     * This means the user logged out of the SSO infrastructure, you 'should' log them out locally too.
     *
     * @param Saml2Auth $saml2Auth
     * @param $idpName
     * @return \Illuminate\Http\Response
     */
    public function sls(Saml2Auth $saml2Auth, $idpName)
    {
        $errors = $saml2Auth->sls($idpName, config('saml2_settings.retrieveParametersFromServer'));
        if (!empty($errors)) {
            logger()->error('Saml2 error', $errors);
            session()->flash('saml2_error', $errors);
            throw new \Exception("Could not log out");
        }

        return redirect(config('saml2_settings.logoutRoute')); //may be set a configurable default
    }

    /**
     * Initiate a logout request across all the SSO infrastructure.
     *
     * @param Saml2Auth $saml2Auth
     * @param Request $request
     */
    public function logout(Saml2Auth $saml2Auth, Request $request)
    {
        $returnTo = $request->query('returnTo');
        $sessionIndex = $request->query('sessionIndex');
        $nameId = $request->query('nameId');
        $saml2Auth->logout($returnTo, $nameId, $sessionIndex); //will actually end up in the sls endpoint
        //does not return
    }

    /**
     * Initiate a login request.
     *
     * @param Saml2Auth $saml2Auth
     */
    public function login(Saml2Auth $saml2Auth)
    {
        $this->logger(sprintf('entering %s method: %s (line %d)',__FILE__,__METHOD__,__LINE__));
        // todo set forceAuthn to dynamic in App op true;
        $redirectTo = config('saml2_settings.loginRoute');
        if(request()->get('entreeRegister')){
            $redirectTo = '/entreeRegister';
        }

        $forceAuth = true;
        if(config('entree.use_with_2_urls')){
            $set = 'small';
            if(request()->get('set') === 'full'){
                $forceAuth = false;
            }
            $redirectTo .= '?set='.$set;
        }

        $redirectTo = $this->handleCollectionOfNeededData($redirectTo);

        $saml2Auth->login($redirectTo, [], $forceAuth);
    }

    protected function handleCollectionOfNeededData(string $redirectTo) : string
    {
        $this->logger(sprintf('entering %s method: %s (line %d)',__FILE__,__METHOD__,__LINE__));
        if($directLink = request()->get('directlink')){
            $message = SamlMessage::create([
                'message_id' => 'not needed',
                'eck_id' => 'not needed',
                'data' => (object) ['url' => route('take.directLink', ['testTakeUuid' => $directLink])],
            ]);
            $redirectTo .= (Str::contains($redirectTo,'?') ? '&' : '?') . 'mId='.$message->uuid;
        } else if($mId = request()->get('mId')){
            $redirectTo .= (Str::contains($redirectTo,'?') ? '&' : '?') . 'mId='.$mId;
        }

        return $redirectTo;
    }

    public function register()
    {
        return redirect('/saml2/entree/login?entreeRegister=true');
    }
}
