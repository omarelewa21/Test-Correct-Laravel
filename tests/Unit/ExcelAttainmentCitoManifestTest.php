<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use tcCore\ExcelAttainmentCitoManifest;
use Tests\TestCase;

class ExcelAttainmentCitoManifestTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     */
    function test_function($domain, $learning_objective, $code, $subcode)
    {
        $manifest = new ExcelAttainmentCitoManifest();

        $result = $manifest->getCodeSubcodes($learning_objective, $domain);
        collect($result)->each(function($r,$key) use ($code,$subcode){
            $this->assertEquals($code[$key], $r['code']);
            $this->assertEquals($subcode[$key], $r['subcode']);
        });


    }

    function dataProvider()
    {
        return [
            [
                'K09 - het lichaam in stand houden: voeding en genotmiddelen, energie…',
                '9D - voedingsstoffen, afvalstoffen',
                ['K09'],
                ['D'],
            ],
            [
                'B1 - Wereld',
                '12 - kenmerken wereldsteden beschrijven en herkennen',
                ['B1'],
                ['12'],
            ],
            [
                '6 - Redox',
                '6.3 - elektrochemische cel',
                ['6'],
                ['.3'],
            ],
            [
                'B1 - Wereld',
                '09 - economische, politieke, sociale en culturele ontwikkelingskenmerken herkennen,',
                ['B1'],
                ['09'],
            ],
            [
                'C1 - kracht en beweging',
                'C2-01 - C2 energieomzettingen',
                ['C1'],
                ['01'],
            ],[
                'D - Markt',
                'D3, D4 - Marktmacht, marktfalen en welvaart',
                ['D','D'],
                ['3','4'],
            ],
            [
                '02 - tijdvak 2',
                '02 - tijdvak 2',
                ['02'],
                [''],
            ],
            [
                '1 - Rekenen',
                '1b - Verhoudingen',
                ['1'],
                ['b'],
            ],

        ];
    }

}