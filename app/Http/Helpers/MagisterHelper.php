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

    public static function guzzle()
    {
        $url = 'https://acc.idhub.nl/uwlr-l-alles-in-een/v2.3';

        $xml = trim('
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
</soapenv:Envelope>
        ');

        $client = new Client([
            'headers' => [
                'SOAPAction'                 => 'HaalLeerlinggegevens',
                'IddinkHub-Subscription-Key' => 'a52478c70c6a43df83f3bcd4f7a77327',
                'Content-Type'               => 'text/xml',
            ]
        ]);
try {
    $response = $client->post($url,
        ['body' => $xml]
    );
} catch (\Exception $e) {
    dd($e);
}

        $stream = $response->getBody();
        $stream->rewind();
        $string = $stream->getContents();
        dd($string);


    }



    public static function getResult1() {
        return '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Header/><SOAP-ENV:Body><le:leerlinggegevens_antwoord xmlns:le="http://www.edustandaard.nl/leerresultaten/2/leerlinggegevens"><le:leerlinggegevens><le:school><le:dependancecode>00</le:dependancecode><le:brincode>99DE</le:brincode><le:schooljaar>2018-2019</le:schooljaar><le:auteur>author</le:auteur><le:xsdversie>2.3</le:xsdversie><le:commentaar>comments</le:commentaar></le:school><le:groepen><le:groep key="H1A"><le:naam>H1A</le:naam><le:jaargroep>1</le:jaargroep><le:omschrijving>This is class 1A</le:omschrijving><le:mutatiedatum>2019-01-04T20:00:00Z</le:mutatiedatum></le:groep><le:groep key="H1B"><le:naam>H1B</le:naam><le:jaargroep>1</le:jaargroep><le:omschrijving>This is class 1B</le:omschrijving><le:mutatiedatum>2019-01-04T20:00:00Z</le:mutatiedatum></le:groep><le:groep key="H1C"><le:naam>H1C</le:naam><le:jaargroep>1</le:jaargroep><le:omschrijving>This is class 1C</le:omschrijving><le:mutatiedatum>2019-01-04T20:00:00Z</le:mutatiedatum></le:groep><le:groep key="H2A"><le:naam>H2A</le:naam><le:jaargroep>2</le:jaargroep><le:omschrijving>This is class 2A</le:omschrijving><le:mutatiedatum>2019-01-04T20:00:00Z</le:mutatiedatum></le:groep><le:groep key="H2B"><le:naam>H2B</le:naam><le:jaargroep>2</le:jaargroep><le:omschrijving>This is class 2B</le:omschrijving><le:mutatiedatum>2019-01-04T20:00:00Z</le:mutatiedatum></le:groep><le:groep key="H2C"><le:naam>H2C</le:naam><le:jaargroep>2</le:jaargroep><le:omschrijving>This is class 2C</le:omschrijving><le:mutatiedatum>2019-01-04T20:00:00Z</le:mutatiedatum></le:groep><le:samengestelde_groep key="H1Muziek"><le:naam>1st year Music group</le:naam><le:omschrijving>This composite group is for students/teachers of Music for year group 1</le:omschrijving></le:samengestelde_groep><le:samengestelde_groep key="H2Muziek"><le:naam>2nd year Music group</le:naam><le:omschrijving>This composite group is for students/teachers of Music for year group 2</le:omschrijving></le:samengestelde_groep><le:samengestelde_groep key="H1Sport"><le:naam>1st year Sport group</le:naam><le:omschrijving>This composite group is for students/teachers of Sport for year group 1</le:omschrijving></le:samengestelde_groep><le:samengestelde_groep key="H2Sport"><le:naam>2nd year Sport group</le:naam><le:omschrijving>This composite group is for students/teachers of Sport for year group 2</le:omschrijving></le:samengestelde_groep></le:groepen><le:leerlingen><le:leerling eckid="eckid_L1" key="L1"><le:achternaam>Demo01</le:achternaam><le:voorvoegsel>a</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1A"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L2" key="L2"><le:achternaam>Demo02</le:achternaam><le:voorvoegsel></le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1B"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L3" key="L3"><le:achternaam>Demo03</le:achternaam><le:voorvoegsel></le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L4" key="L4"><le:achternaam>Demo04</le:achternaam><le:voorvoegsel>d</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1A"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L5" key="L5"><le:achternaam>Demo05</le:achternaam><le:voorvoegsel></le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1B"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L6" key="L6"><le:achternaam>Demo06</le:achternaam><le:voorvoegsel></le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L7" key="L7"><le:achternaam>Demo07</le:achternaam><le:voorvoegsel></le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1A"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L8" key="L8"><le:achternaam>Demo08</le:achternaam><le:voorvoegsel></le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L9" key="L9"><le:achternaam>Demo09</le:achternaam><le:voorvoegsel>J</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L10" key="L10"><le:achternaam>Demo10</le:achternaam><le:voorvoegsel>a</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L11" key="L11"><le:achternaam>Demo11</le:achternaam><le:voorvoegsel>b</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1A"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L12" key="L12"><le:achternaam>Demo12</le:achternaam><le:voorvoegsel>c</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1B"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L13" key="L13"><le:achternaam>Demo13</le:achternaam><le:voorvoegsel>d</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L14" key="L14"><le:achternaam>Demo14</le:achternaam><le:voorvoegsel>e</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1A"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L15" key="L15"><le:achternaam>Demo15</le:achternaam><le:voorvoegsel>f</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1B"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L16" key="L16"><le:achternaam>Demo16</le:achternaam><le:voorvoegsel>g</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L17" key="L17"><le:achternaam>Demo17</le:achternaam><le:voorvoegsel>h</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1A"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L18" key="L18"><le:achternaam>Demo18</le:achternaam><le:voorvoegsel>i</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L19" key="L19"><le:achternaam>Demo19</le:achternaam><le:voorvoegsel>j</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Muziek"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="eckid_L20" key="L20"><le:achternaam>Demo20</le:achternaam><le:voorvoegsel>k</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="https://ketenid.nl/201703/a4b1f23f608da30d1f34c8890d9f32bbd46ac9c1268dfc44bf44c0821d9b9e355be6732385700d8ff04fc99e1276ecb66d9fd3ec415206d3ede3e6b4fb12108a" key="EC976431"><le:achternaam>Van steeg</le:achternaam><le:voorvoegsel>k</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/></le:samengestelde_groepen></le:leerling><le:leerling eckid="https://ketenid.nl/pilot/e1f70edaa9735265779a9b5ca83e8471193ead78a6f4eb587b35c96bed2aca85ab5bc7be0b7f6f32a8a7a48c812c517aef5930830f19232961a45b0719b38d4b" key="L21"><le:achternaam>Demo21</le:achternaam><le:voorvoegsel>l</le:voorvoegsel><le:roepnaam>Leerling</le:roepnaam><le:geboortedatum>2002-01-04</le:geboortedatum><le:jaargroep>1</le:jaargroep><le:groep key="H1C"/><le:samengestelde_groepen><le:samengestelde_groep key="H1Sport"/></le:samengestelde_groepen></le:leerling></le:leerlingen><le:leerkrachten><le:leerkracht eckid="eckid_T1" key="T1"><le:roepnaam>T</le:roepnaam><le:emailadres>email</le:emailadres><le:voorvoegsel>T</le:voorvoegsel><le:groepen><le:groep key="H1A"/><le:groep key="H1C"/><le:samengestelde_groep key="H1Muziek"/></le:groepen><le:achternaam>Teacher</le:achternaam></le:leerkracht><le:leerkracht eckid="eckid_T2" key="T2"><le:roepnaam>T</le:roepnaam><le:emailadres>email</le:emailadres><le:voorvoegsel>T</le:voorvoegsel><le:groepen><le:groep key="H1B"/><le:groep key="H2A"/><le:samengestelde_groep key="H1Sport"/><le:samengestelde_groep key="H1Muziek"/></le:groepen><le:achternaam>Teacher</le:achternaam></le:leerkracht><le:leerkracht eckid="eckid_T3" key="T3"><le:roepnaam>T</le:roepnaam><le:emailadres>email</le:emailadres><le:voorvoegsel>T</le:voorvoegsel><le:groepen><le:groep key="H1A"/><le:groep key="H1C"/><le:samengestelde_groep key="H1Muziek"/></le:groepen><le:achternaam>Teacher</le:achternaam></le:leerkracht><le:leerkracht eckid="eckid_T4" key="T4"><le:roepnaam>T</le:roepnaam><le:emailadres>email</le:emailadres><le:voorvoegsel>T</le:voorvoegsel><le:groepen><le:groep key="H2A"/><le:groep key="H2B"/><le:samengestelde_groep key="H1Sport"/></le:groepen><le:achternaam>Teacher</le:achternaam></le:leerkracht><le:leerkracht eckid="eckid_T5" key="T5"><le:roepnaam>T</le:roepnaam><le:emailadres>email</le:emailadres><le:voorvoegsel>T</le:voorvoegsel><le:groepen><le:groep key="H2A"/><le:samengestelde_groep key="H1Muziek"/></le:groepen><le:achternaam>Teacher</le:achternaam></le:leerkracht><le:leerkracht eckid="https://ketenid.nl/pilot/91da4cd777a34d9fc20bd3896283bd29f15877670d4d1bccdf7ad98684fc19d39f4b6e026cdfbb4fcd50070aff9ab2305781841968827faba0ff2d6dd4730349" key="T6"><le:roepnaam>T</le:roepnaam><le:emailadres>email</le:emailadres><le:voorvoegsel>T</le:voorvoegsel><le:groepen><le:groep key="H2A"/><le:samengestelde_groep key="H1Muziek"/></le:groepen><le:achternaam>Teacher</le:achternaam></le:leerkracht></le:leerkrachten></le:leerlinggegevens></le:leerlinggegevens_antwoord></SOAP-ENV:Body></SOAP-ENV:Envelope>';
    }

    private static function xml2array($string, $out = array())
    {
        $xmlObject   = simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA);
        foreach ((array) $xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? xml2array($node) : $node;
        }

        return $out;
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
                'location'       => 'https://acc.idhub.nl/uwlr-l-alles-in-een/v2.3',
                'uri'            => 'http://www.edustandaard.nl/leerresultaten/2/leerlinggegevens',
                "stream_context" =>
                    stream_context_create(array(
                        "http" => array(
                            "header" => "IddinkHub-Subscription-Key: a52478c70c6a43df83f3bcd4f7a77327\r\n".
                                "Content-Type: text/xml\r\n".
                                "SOAPAction: HaalLeerlinggegevens\r\n"
                        )
                    ))
            ));


        $result = $client->__soapCall("HaalLeerlinggegevens", [
            "HaalLeerlinggegevens" => [
                'schooljaar'     => '2019-2020',
                'brincode'       => '99DE',
                'dependancecode' => '00',
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
