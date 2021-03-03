<?php

namespace tcCore\Http\Controllers;

use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Http\Request;
use SoapClient;

class SomeTodayController extends Controller
{
    const WSDL = 'https://oop.test.somtoday.nl/services/v2/leerlinggegevens?wsdl';

    /**
     * @var SoapWrapper
     */
    protected $soapWrapper;

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
    public function index()
    {
        $this->soapWrapper->add('leerlinggegevensServiceV2', function ($service) {
            $service
                ->wsdl(self::WSDL)
                ->trace(true)
                ->header('http://www.edustandaard.nl/leerresultaten/2/autorisatie', 'autorisatie', [
                    'autorisatiesleutel' => 'I/m9xDqU7mfwIpZYS7xQvaFzJ4rdQiYsgKxHP7fXMlgYnghl9C5xl3L0OZIN7q92UKuHnFsyqsco3kmQsASQ8Q==',
                    'klantcode'          => 'OV',
                    'klantnaam'          => 'Overig',
                ]);
        });

        // Without classmap
        $response = $this->soapWrapper->call('leerlinggegevensServiceV2.HaalLeerlinggegevens', [
            'leerlinggegevens_verzoek' => [
                'schooljaar'     => '2019-2020',
                'brincode'       => '06SS',
                'dependancecode' => '00',
                'xsdversie'      => '2.2',
            ]
        ]);

        $school = $response->leerlinggegevens->school;
        $groepen = $response->leerlinggegevens->groepen->groep;


        return view('sometoday')->with(compact(['school', 'groepen']));


        ($response->leerlinggegevens->leerlingen);
        var_dump($response->leerlinggegevens->leerkrachten);


    }
}
