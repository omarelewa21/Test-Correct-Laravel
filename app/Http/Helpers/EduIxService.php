<?php


namespace tcCore\Http\Helpers;


use Exception;
use SoapHeader;
use stdClass;

class EduIxService
{
    const EDUROUTEV4_WSDL_PROFILE = 'https://acc-lika.edu-ix.nl/soap/4.0/profile/wsdl';
    const EDUROUTEV4_NAMESPACE_PROFILE = 'urn:edu-ix:profile:4.0';

    const EDUROUTEV4_WSDL_CREDIT = 'https://acc-lika.edu-ix.nl/soap/4.0/credit/wsdl';
    const EDUROUTEV4_NAMESPACE_CREDIT = 'urn:edu-ix:credit:4.0';

    private $header;
    private $sessionId;

    private $digiDeliveryID;

    private $eduProfile;

    private $personCredit;

    private $schoolCredit;

    private static function getUsername()
    {
        return config('custom.eduix.username');
    }

    private static function getPassword()
    {
        return config('custom.eduix.password');
    }

    private static function getPreSharedKey()
    {
        return config('custom.eduix.presharedkey');
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

    public function getEduProfile()
    {
        # Call uitvoeren om persoonsgegevens op te halen
        if (!$this->eduProfile) {

            $client_profile = new \SoapClient(self::EDUROUTEV4_WSDL_PROFILE);
            $client_profile->__setSoapHeaders($this->header);
            $request = new stdClass();
            $request->redirectSessionID = $this->sessionId;

            $this->eduProfile = $client_profile->getEduProfile($request);
            $this->digiDeliveryID = $this->eduProfile->digiDeliveryID;
        }

        return $this->eduProfile;
    }

    public function getSchoolCredit()
    {
        if (!$this->schoolCredit) {
            # Call uitvoeren om schooltegoeden op te halen
            $client_credit = new \SoapClient(self::EDUROUTEV4_WSDL_CREDIT);
            $client_credit->__setSoapHeaders($this->header);
            # Request opstellen voor getSchoolCredit
            $request = new stdClass();
            $request->organisationID = $this->getDigiDeliveryID();
            $request->redirectSessionID = $this->sessionId;
            $this->schoolCredit = $client_credit->getSchoolCredit($request);
        }
        return $this->schoolCredit;
    }

    public function getPersonCredit()
    {
        if (!$this->personCredit) {
            # Call uitvoeren om persoonelijke tegoeden op te halen
            $client_credit = new \SoapClient(self::EDUROUTEV4_WSDL_CREDIT);
            $client_credit->__setSoapHeaders($this->header);
            $request = new stdClass();
            $request->redirectSessionID = $this->sessionId;
            $this->personCredit = $client_credit->getPersonCredit($request);
        }
        return $this->personCredit;
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

    public function getDigiDeliveryID()
    {
        if (!$this->digiDeliveryID) {
            $this->getEduProfile();
        }

        return $this->digiDeliveryID;
    }

    public function getHomeOrganizationId()
    {
        return $this->getEduProfile()->homeOrganizationID;
    }

    public function getEan()
    {
        return $this->getPersonCredit()->personCreditInformation->personCredit->ean;
    }

    public function asJson()
    {
        return json_encode([
            'eduProfile' => $this->getEduProfile(),
            'personCredit' => $this->getPersonCredit(),
            'school_credit' => $this->getSchoolCredit(),
        ]);
    }


}