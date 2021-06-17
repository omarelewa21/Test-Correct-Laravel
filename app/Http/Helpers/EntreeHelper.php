<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:33
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use tcCore\SamlMessage;
use tcCore\SchoolLocation;
use tcCore\User;

class EntreeHelper
{
    private $attr;

    private $messageId;

    private $location = null;

    public function __construct($attr, $messageId)
    {
        $this->attr = $attr;
        $this->messageId = $messageId;
    }

    public function redirectIfBrinUnknown()
    {
        $brinZesCode = $this->getBrinFromAttributes();

        if (strlen($brinZesCode) === 6) {
            $external_main_code = substr($brinZesCode, 0, 4);
            $external_sub_code = substr($brinZesCode, 4, 2);

            $this->location = SchoolLocation::where('external_main_code', $external_main_code)
                ->where('external_sub_code', $external_sub_code)
                ->first();
        }
        if ($this->location == null) {
            $url = route('auth.login', ['tab' => 'login', 'message_brin' => 'brin_not_found']);
            if (App::runningUnitTests()) {
                return $url;
            }
        }
        return $this->location;
    }

    public static function shouldPromptForEntree(User $user)
    {
        return (optional($user->schoolLocation)->lvs_active && empty($user->eck_id));
    }

    private function getBrinFromAttributes()
    {
        if (array_key_exists('nlEduPersonHomeOrganizationBranchId',
                $this->attr) && $this->attr['nlEduPersonHomeOrganizationBranchId'][0]) {
            return $this->attr['nlEduPersonHomeOrganizationBranchId'][0];
        }
        return null;
    }

    private function createSamlMessage()
    {
        $this->validateAttributes();

        return SamlMessage::create([
            'message_id' => $this->messageId,
            'eck_id'     => $this->attr['eckId'][0],
            'email'      => $this->attr['mail'][0],
        ]);
    }

    private function validateAttributes()
    {

        if (!array_key_exists('eckId', $this->attr) || !array_key_exists(0, $this->attr['eckId'])) {
            throw new \Exception('no eckId found in saml request');
        }

        if (!array_key_exists('mail', $this->attr) || !array_key_exists(0, $this->attr['mail'])) {
            throw new \Exception('no mail found in saml request');
        }
    }

    public function blockIfReplayAttackDetected()
    {
        $message = SamlMessage::whereMessageId($this->messageId)->first();
        if ($message) {
            dd('preventing reuse of messageId');
        }
    }

    public function redirectIfscenario5()
    {
        if ($this->location->lvs_active) {
            return true;
        }
        dd('not yet implemented scenario 5');
    }

    public function redirectIfEckIdNotKnown()
    {
        $samlMessage = $this->createSamlMessage();

        $laravelUser = User::findByEckId($this->attr['eckId'][0])->first();
        if ($laravelUser) {
            $laravelUser->handleEntreeAttributes($attr);
            $url = $laravelUser->getTemporaryCakeLoginUrl();
            if (App::runningUnitTests()) {
                return $url;
            }
            header("Location: $url");
            exit;
        } else {
            $url = route('auth.login', ['tab' => 'entree', 'uuid' => $samlMessage->uuid]);
            if (App::runningUnitTests()) {
                return $url;
            }
            header("Location: $url");
            exit;
        }
    }

}
