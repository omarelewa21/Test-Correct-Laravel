<?php

namespace tcCore\Http\Controllers\EduK;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rules\In;
use SoapHeader;
use stdClass;
use tcCore\Http\Controllers\Controller;

class HomeController extends Controller
{


# Vul hier de credentials in die je hebt ontvangen van Edu-iX\

    public function create($ean, $sessionId, $signature)
    {
        $service = new EduIxService($sessionId, $signature);

        return [
            'eduProfile' => $service->getEduProfile(),
            'personCredit' => $service->getPersonCredit(),
            'schoolCredit' => $service->getSchoolCredit(),
        ];
    }


    public function index()
    {
//        dd(Input::all());
        (new EduIxService(Input::get('redirectSessionID'), Input::get('signature')))->script();
    }
}
class EduIxService {
    const EDUROUTEV4_WSDL_PROFILE = 'https://acc-lika.edu-ix.nl/soap/4.0/profile/wsdl';
    const EDUROUTEV4_NAMESPACE_PROFILE = 'urn:edu-ix:profile:4.0';

    const EDUROUTEV4_WSDL_CREDIT = 'https://acc-lika.edu-ix.nl/soap/4.0/credit/wsdl';
    const EDUROUTEV4_NAMESPACE_CREDIT = 'urn:edu-ix:credit:4.0';

    private $header;
    private $sessionId;

    private $digiDeliveryID;

    private static function getUsername()
    {
        return env('EDU_IX_USERNAME');
    }

    private static function getPassword()
    {
        return env('EDU_IX_PASSWORD');
    }

    private static function getPreSharedKey()
    {
        return env('EDU_IX_PRESHAREDKEY');
    }

    public function __construct($sessionID, $signature)
    {
        $this->sessionId = $sessionID;

        $this->checkSignature($sessionID, $signature);
        $this->initHeader();

    }

    public function script()
    {
        echo '<pre>';
        try {
            echo '<h1>Persoonsgegevens:</h1><br/>';
            print_r($this->getEduProfile());

            echo '<h1>Persoonlijke tegoeden:</h1><br/>';
            print_r($this->getPersonCredit());

            echo '<h1>Schooltegoeden:</h1><br/>';
            print_r($this->getSchoolCredit());

        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }

    public function getEduProfile() {
        # Call uitvoeren om persoonsgegevens op te halen
        $client_profile = new \SoapClient(self::EDUROUTEV4_WSDL_PROFILE);
        $client_profile->__setSoapHeaders($this->header);
        $request = new stdClass();
        $request->redirectSessionID = $this->sessionId;

        $result = $client_profile->getEduProfile($request);
        $this->digiDeliveryID = $result->digiDeliveryID;

        return $result;
    }

    public function getSchoolCredit() {
        # Call uitvoeren om schooltegoeden op te halen

        $client_credit = new \SoapClient(self::EDUROUTEV4_WSDL_CREDIT);
        $client_credit->__setSoapHeaders($this->header);
        # Request opstellen voor getSchoolCredit
        $request = new stdClass();
        $request->organisationID = $this->getDigiDeliveryID();
        $request->redirectSessionID = $this->sessionId;
        return $client_credit->getSchoolCredit($request);
    }

    public function getPersonCredit() {
        # Call uitvoeren om persoonelijke tegoeden op te halen
        $client_credit = new \SoapClient(self::EDUROUTEV4_WSDL_CREDIT);
        $client_credit->__setSoapHeaders($this->header);
        $request = new stdClass();
        $request->redirectSessionID = $this->sessionId;
        return $client_credit->getPersonCredit($request);
    }
    //

    /**
     * @param $sessionID
     * @param $signature
     */
    private function checkSignature($sessionID, $signature): void
    {
        if ($signature) {

            // Signature zelf berekenen
            $generatedSignature = md5($this->sessionId . self::getPreSharedKey());

            if ($generatedSignature != $signature) {
                echo 'Ontvangen signature is niet geldig.';
                exit;
            }

        } else {
            echo 'Geen signature ontvangen. Script gestopt.';
            exit;
        }
    }

    private function initHeader(): void
    {
# Eenmalig een header obstellen
        $headerObj = new stdClass();
        $headerObj->loginHeader = new stdClass();
        $headerObj->loginHeader->username = self::getUsername();
        $headerObj->loginHeader->password = self::getPassword();

        $this->header = new SoapHeader(self::EDUROUTEV4_NAMESPACE_PROFILE, 'authHeader', $headerObj, false);
    }

    private function getDigiDeliveryID()
    {
        if (!$this->digiDeliveryID) {

        }

        return $this->digiDeliveryID;
    }
}
