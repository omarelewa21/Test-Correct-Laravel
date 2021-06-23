<?php

namespace tcCore\Http\Helpers;


use Artisaninweb\SoapWrapper\SoapWrapper;
use Ramsey\Uuid\Guid\Guid;
use tcCore\UwlrSoapEntry;
use tcCore\UwlrSoapResult;

class SomTodayHelper
{
    const WSDL = 'https://oop.somtoday.nl/services/v2/leerlinggegevens?wsdl';
    const WSDL_TEST = 'https://oop.test.somtoday.nl/services/v2/leerlinggegevens?wsdl';
    const SOURCE = 'SomToDay';
    const XSD_VERSION = '2.2';

    /**
     * @var SoapWrapper
     */
    protected $soapWrapper;

    private $result = null;

    private $resultIdentifier = null;


    /**
     * SoapController constructor.
     *
     * @param  SoapWrapper  $soapWrapper
     */
    public function __construct(SoapWrapper $soapWrapper)
    {
        $this->soapWrapper = $soapWrapper;
    }

    /**
     * Use the SoapWrapper
     */
    public function search($klantcode, $klantnaam, $schooljaar, $brincode, $dependancecode)
    {
        $this->searchParams = [
            'source'          => self::SOURCE,
            'client_code'     => $klantcode,
            'client_name'     => $klantnaam,
            'school_year'     => $schooljaar,
            'brin_code'       => $brincode,
            'dependance_code' => $dependancecode,
            'xsdversie'       => self::XSD_VERSION,
        ];


        $serviceUrl = $brincode === '06SS' ? self::WSDL_TEST : self::WSDL;

        $location = \tcCore\SchoolLocation::where('external_main_code', $brincode)->where('external_sub_code',
            $dependancecode)->first();

        if ($location) { // if not exists skip
            $authorization_key = $location->lvs_authorization_key;
            $this->soapWrapper->add('leerlinggegevensServiceV2',
                function ($service) use ($klantcode, $klantnaam, $serviceUrl, $authorization_key) {
                    $service
                        ->wsdl($serviceUrl)
                        ->trace(true)
                        ->header('http://www.edustandaard.nl/leerresultaten/2/autorisatie', 'autorisatie', [
                            'autorisatiesleutel' => $authorization_key,
                            'klantcode'          => $klantcode,
                            'klantnaam'          => $klantnaam,
                        ]);
                });

            // Without classmap
            $this->result = $this->soapWrapper->call('leerlinggegevensServiceV2.HaalLeerlinggegevens', [
                'leerlinggegevens_verzoek' => [
                    'schooljaar'     => $schooljaar,
                    'brincode'       => $brincode,
                    'dependancecode' => $dependancecode,
                    'xsdversie'      => self::XSD_VERSION,
                ]
            ]);
            return $this;
        }

        dd(__FILE__ .' no autorization key was found for given brin/dependance code;');
    }

    public function storeInDB()
    {
        if (!$this->result) {
            throw new \Exception('no result to store');
        }

        $this->resultSet = UwlrSoapResult::create($this->searchParams);

        $this->resultIdentifier = $this->resultSet->getKey();

        $this->storeInDBSchool($this->result->leerlinggegevens->school);
        $this->storeInDBGroep($this->result->leerlinggegevens->groepen);
        $this->storeInDBLeerlingen($this->result->leerlinggegevens->leerlingen);
        $this->storeInDBLeerkrachten($this->result->leerlinggegevens->leerkrachten);

        return $this;
    }

    public function getResult()
    {
        if (!$this->result) {
            throw new \Exception('no result to store');
        }
        return $this->result;
    }

    public function getResultIdentifier()
    {
        return $this->resultIdentifier;
    }

    public function getResultSet()
    {
        return $this->resultSet;
    }

    private function storeInDBSchool($school)
    {
        UwlrSoapEntry::create([
            'uwlr_soap_result_id' => $this->resultIdentifier,
            'key'                 => 'school',
            'object'              => serialize($school),
        ]);

    }

    private function storeInDBGroep($groepen)
    {
        collect(['groep', 'samengestelde_groep'])->each(function ($prop) use ($groepen) {
            collect($groepen->$prop)->each(function ($obj) use ($prop) {
                UwlrSoapEntry::create([
                    'uwlr_soap_result_id' => $this->resultIdentifier,
                    'key'                 => $prop,
                    'object'              => serialize($obj),
                ]);
            });
        });
    }

    private function storeInDBLeerlingen($leerlingen)
    {
        collect($leerlingen->leerling)->each(function ($obj) {
            UwlrSoapEntry::create([
                'uwlr_soap_result_id' => $this->resultIdentifier,
                'key'                 => 'leerling',
                'object'              => serialize($obj),
            ]);
        });

    }

    private function storeInDBLeerkrachten($leerkrachten)
    {
        collect($leerkrachten->leerkracht)->each(function ($obj) {
            UwlrSoapEntry::create([
                'uwlr_soap_result_id' => $this->resultIdentifier,
                'key'                 => 'leerkracht',
                'object'              => serialize($obj),
            ]);
        });
    }
}
