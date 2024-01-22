<?php

namespace tcCore\Http\Helpers;


use Artisaninweb\SoapWrapper\SoapWrapper;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Ramsey\Uuid\Guid\Guid;
use Illuminate\Support\Facades\Auth;
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

    protected $soapError;
    protected $soapException;


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
    public function search($klantcode, $klantnaam, $schooljaar, $brincode, $dependancecode, $autoImport = null)
    {
        $this->searchParams = [
            'source'          => self::SOURCE,
            'client_code'     => $klantcode,
            'client_name'     => $klantnaam,
            'school_year'     => $schooljaar,
            'brin_code'       => $brincode,
            'dependance_code' => $dependancecode,
            'xsdversie'       => self::XSD_VERSION,
            'username_who_imported' => $autoImport ? 'AUTO_UWLR_IMPORT' : (optional(Auth::user())->username ?: 'system'),
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
            try {
                $this->result = $this->soapWrapper->call('leerlinggegevensServiceV2.HaalLeerlinggegevens', [
                    'leerlinggegevens_verzoek' => [
                        'schooljaar' => $schooljaar,
                        'brincode' => $brincode,
                        'dependancecode' => $dependancecode,
                        'xsdversie' => self::XSD_VERSION,
                    ]
                ]);

            } catch(\Exception $e){
                $this->soapError = $e->getMessage();
                $this->soapException = $e;
            }
            return $this;
        }

        dd(__FILE__ .' no autorization key was found for given brin/dependance code;');
    }

    public function storeInDB()
    {
        $this->resultSet = UwlrSoapResult::create(
            $this->searchParams
        );
        if($this->soapError){
            $this->resultSet->error_messages = $this->soapError;
            $this->resultSet->status = 'FAILED';
            $this->resultSet->save();
            $body = sprintf('There was an exception while retrieving data from SomToday%serror: %s%sdata:%s',PHP_EOL,$this->soapError,PHP_EOL,print_r($this->searchParams,true));
            if(!app()->runningInConsole()) { // we do this in the import helper
                Bugsnag::notifyException(new \LogicException($body, 0, $this->soapException));
            }
            throw new \Exception($body = sprintf('Er ging iets mis bij het ophalen van de gegevens via SomToday<br/>error: %s<br/>Data die gebruikt is:<pre>%s</pre>',$this->soapError,print_r($this->searchParams,true)));
        }
        if (!$this->result) {
            throw new \Exception('no result to store');
        }



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


    public function hasException()
    {
        return (bool) $this->soapException;
    }

    public function getException()
    {
        return $this->soapException;
    }

    public function getResultIdentifier()
    {
        return $this->resultIdentifier;
    }

    public function getResultSet($forceRefresh = true)
    {
        if($forceRefresh){
            $this->resultSet->fresh();
        }
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
