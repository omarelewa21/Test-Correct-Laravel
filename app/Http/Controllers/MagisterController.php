<?php

namespace tcCore\Http\Controllers;

use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Http\Request;
use SoapClient;

class MagisterController extends Controller
{
    const WSDL = 'https://acc.idhub.nl';
    const PATH = '/uwlr-l-alles-in-een/v2.3/';

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
//                ->wsdl(self::WSDL)
                ->options([
                    'uri'      => self::PATH,
                    'location' => self::WSDL,
                    'encoding' => 'UTF-8',
                    'stream_context' => stream_context_create([
                        'http' => [
                            'IddinkHub-Subscription-Key' => 'a52478c70c6a43df83f3bcd4f7a77327',
                            'SOAPAction' => 'HaalLeerlinggegevens',
                            'Content-Type' => 'text/xml',
                        ],
                    ])
                ])
                ->trace(true)
                ->header('http://www.edustandaard.nl/leerresultaten/2/autorisatie', 'autorisatie', [
                    'autorisatiesleutel' => 'HubUwlrLDemoAuthKey',
                    'klantcode'          => 'HubUwlrLDemo',
                    'klantnaam'          => 'HubUwlrLDemoClient',

                ]);


        });


        // Without classmap
        $response = $this->soapWrapper->call(
            'leerlinggegevensServiceV2.HaalLeerlinggegevens',
            [
                'leerlinggegevens_verzoek' => [
                    'schooljaar'     => '2020-2021',
                    'brincode'       => '99DE',
                    'dependancecode' => '01',
                    'xsdversie'      => '2.3',
                ],
//            ],
//            [

            ]
        );

        $school = $response->leerlinggegevens->school;
        $groepen = $response->leerlinggegevens->groepen->groep;

        dd($response);
        return view('somtoday')->with(compact(['school', 'groepen']));


        ($response->leerlinggegevens->leerlingen);
        var_dump($response->leerlinggegevens->leerkrachten);


    }
}
