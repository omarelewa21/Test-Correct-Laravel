<?php


namespace tcCore\Http\Helpers;


use Artisaninweb\SoapWrapper\SoapWrapper;
use Ramsey\Uuid\Guid\Guid;
use tcCore\UwlrSoapEntry;
use tcCore\UwlrSoapResult;

class SomeTodayHelper
{
    const WSDL = 'https://oop.test.somtoday.nl/services/v2/leerlinggegevens?wsdl';
    const SOURCE = 'SomeToDay';
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
            'source' => self::SOURCE,
            'client_code'      => $klantcode,
            'client_name'      => $klantnaam,
            'school_year'     => $schooljaar,
            'brin_code'       => $brincode,
            'dependance_code' => $dependancecode,
            'xsdversie' => self::XSD_VERSION,
        ];

        $this->soapWrapper->add('leerlinggegevensServiceV2', function ($service) use ($klantcode, $klantnaam) {
            $service
                ->wsdl(self::WSDL)
                ->trace(true)
                ->header('http://www.edustandaard.nl/leerresultaten/2/autorisatie', 'autorisatie', [
                    'autorisatiesleutel' => 'pmjkO4VbG8uOGNS1T3Q3sHBnOxf/q76osIPUTCbXGQZcs9q5UlgnhUP8p6sUNzf1ZbhLPOfzk85y9OdD9Nz3Rw==',
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

    public function storeInDB()
    {
        if (!$this->result) {
            throw new \Exception('no result to store');
        }
        dd($this->result);

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

    public function getResultSet(){
        return $this->resultSet;
    }

    private function storeInDBSchool($school)
    {
        UwlrSoapEntry::create([
            'uwlr_soap_result_id' => $this->resultIdentifier,
            'key' => 'school',
            'object' => serialize($school),
        ]);

    }

    private function storeInDBGroep($groepen)
    {
       collect(['groep', 'samengestelde_groep'])->each(function($prop) use ($groepen) {
           collect($groepen->$prop)->each(function($obj) use ($prop) {
               UwlrSoapEntry::create([
                   'uwlr_soap_result_id' => $this->resultIdentifier,
                   'key' => $prop,
                   'object' => serialize($obj),
               ]);
           });
       });
    }

    private function storeInDBLeerlingen($leerlingen)
    {
        collect($leerlingen->leerling)->each(function($obj) {
            UwlrSoapEntry::create([
                'uwlr_soap_result_id' => $this->resultIdentifier,
                'key' => 'leerling',
                'object' => serialize($obj),
            ]);
        });

    }

    private function storeInDBLeerkrachten($leerkrachten)
    {
        collect($leerkrachten->leerkracht)->each(function($obj) {
            UwlrSoapEntry::create([
                'uwlr_soap_result_id' => $this->resultIdentifier,
                'key' => 'leerkracht',
                'object' => serialize($obj),
            ]);
        });
    }
}
