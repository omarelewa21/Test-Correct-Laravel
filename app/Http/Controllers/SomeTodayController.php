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
//        $client = new SoapClient(self::WSDL);
//        var_dump($client->__getFunctions());
//        var_dump($client->__getTypes());
//        die;

        $this->soapWrapper->add('leerlinggegevensServiceV2', function ($service) {
            $service
                ->wsdl(self::WSDL)
                ->trace(true);
            // Optional: Set some extra options
//                ->options([
//                    'login'    => 'username',
//                    'password' => 'password'
//                ]);
//                ->classmap([
//                    GetConversionAmount::class,
//                    GetConversionAmountResponse::class,
//                ]);
        });

        // Without classmap
        $response = $this->soapWrapper->call('leerlinggegevensServiceV2.HaalLeerlinggegevens', [
            'schooljaar'              => '20-21',
            // 'wat is een schooljaar in jullie context? 20-21 of 2020-2021 of iets anders?'
            'brincode'                => '08SS',// 'BRINcode'
            'dependancecode'          => '',// 'Dependancecode'
            'schoolkey'               => 'pE4cetsilzf4LnNDccFparm7/FIwt/rQZGS66iIP5gr31bZnYQbuDGMZHKq+fJtyg86USjC3VeFEI0EckX57kw==',
            //  'is dit de autorisatiesleutel?/ of de Klantcode'
            'xsdversie'               => '2.2',// 'XSDversie'
            'gegevenssetid'           => 'OV',// 'is dit de klantcode?'
            'laatstontvangengegevens' => '01-01-1970',//


        ]);

        var_dump($response);

//        // With classmap
//        $response = $this->soapWrapper->call('Currency.GetConversionAmount', [
//            new GetConversionAmount('USD', 'EUR', '2014-06-05', '1000')
//        ]);
//
//        var_dump($response);
        exit;
    }
}
