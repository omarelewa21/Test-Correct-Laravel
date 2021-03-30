<?php


namespace tcCore\Http\Helpers;


use Artisaninweb\SoapWrapper\SoapWrapper;
use GuzzleHttp\Client;
use Ramsey\Uuid\Guid\Guid;
use SoapClient;
use tcCore\UwlrSoapEntry;
use tcCore\UwlrSoapResult;

class MagisterHelper
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

    public static function guzzle() {
        $url = 'https://acc.idhub.nl/uwlr-l-alles-in-een/v2.3';

      $xml = trim(  '

<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:aut="http://www.edustandaard.nl/leerresultaten/2/autorisatie" xmlns:leer="http://www.edustandaard.nl/leerresultaten/2/leerlinggegevens">
 <soapenv:Header>
  <aut:autorisatie>
       <aut:autorisatiesleutel>HubUwlrLDemoAuthKey</aut:autorisatiesleutel>
       <aut:klantcode>HubUwlrLDemo</aut:klantcode>
       <aut:klantnaam>HubUwlrLDemoClient</aut:klantnaam>
    </aut:autorisatie>
 </soapenv:Header>
 <soapenv:Body>
    <leer:leerlinggegevens_verzoek>
      <leer:schooljaar>2019-2020</leer:schooljaar>
       <leer:brincode>99DE</leer:brincode>
       <leer:dependancecode>00</leer:dependancecode>
        <leer:xsdversie>2.3</leer:xsdversie>
       <leer:gegevenssetid></leer:gegevenssetid>
    </leer:leerlinggegevens_verzoek>
 </soapenv:Body>
</soapenv:Envelope>  ');

        $client = new Client([
            'headers' => [
                'SOAPAction' => 'HaalLeerlinggegevens',
                'IddinkHub-Subscription-Key' => 'a52478c70c6a43df83f3bcd4f7a77327',
                'Content-Type' => 'text/xml',
            ]
        ]);

        $response = $client->post($url,
            ['body' => $xml]
        );



        echo  var_dump($response->getBody()->getContents());

    }

    public static function doeiets()
    {
        $url = 'https://acc.idhub.nl/uwlr-l-alles-in-een/v2.3';
        $key = ['IddinkHub-Subscription-Key' => 'a52478c70c6a43df83f3bcd4f7a77327'];

        $soapHeaderNamespace = 'autorisatie';

        $soapHeader = new \SoapHeader('http://www.edustandaard.nl/leerresultaten/2/autorisatie', 'autorisatie', [
            'autorisatiesleutel' => 'HubUwlrLDemoAuthKey',
            'klantcode'          => 'HubUwlrLDemo',
            'klantnaam'          => 'HubUwlrLDemoClient',
        ]);


        $client = new SoapClient(null,

            array(
                'location' => 'https://acc.idhub.nl/uwlr-l-alles-in-een/v2.3',
                'uri' => 'leer:http://www.edustandaard.nl/leerresultaten/2/leerlinggegevens',
                "stream_context" =>
                      stream_context_create(array("http"=>array(
                          "header"=> "IddinkHub-Subscription-Key: a52478c70c6a43df83f3bcd4f7a77327\r\n".
                              "Content-Type: text/xml\r\n".
                              "SOAPAction: HaalLeerlinggegevens\r\n"
                      )))));



        $result = $client->__soapCall("HaalLeerlinggegevens", [
            "HaalLeerlinggegevens" => [
                'schooljaar'     => '2020-2021',
                'brincode'       => '99DE',
                'dependancecode' => '01',
                'xsdversie'      => '2.3',
            ]

        ], null, $soapHeader);


        return $key;

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

        $this->soapWrapper->add('leerlinggegevensServiceV2', function ($service) use ($klantcode, $klantnaam) {
            $service
                ->wsdl(self::WSDL)
                ->trace(true)
                ->header('http://www.edustandaard.nl/leerresultaten/2/autorisatie', 'autorisatie', [
                    'autorisatiesleutel' => 'I/m9xDqU7mfwIpZYS7xQvaFzJ4rdQiYsgKxHP7fXMlgYnghl9C5xl3L0OZIN7q92UKuHnFsyqsco3kmQsASQ8Q==',
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
