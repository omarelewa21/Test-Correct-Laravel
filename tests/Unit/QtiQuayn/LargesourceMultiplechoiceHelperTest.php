<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit\QtiQuayn;


use tcCore\Test;
use tcCore\User;
use Tests\TestCase;

class LargesourceMultiplechoiceHelperTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function a_multiplechoice_questions_has_answers()
    {
        $this_currentTest_zipDir = '';
        $this_basePath = '';

        $test = new Test();
        $question = simplexml_load_file(__DIR__.'/../../_fixtures_quayn_qti/largesourceMultiplechoiceSample1.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $parts = explode('_', $question['type']);
        $helperName = "";
        foreach ($parts as $part) {
            $helperName .= ucfirst(strtolower($part));
        }
        $helperName .= 'Helper';
        $fullHelper = sprintf('tcCore\Http\Helpers\QtiImporter\\%s', $helperName);

        $this->assertEquals(
            'tcCore\Http\Helpers\QtiImporter\LargesourceMultiplechoiceHelper',
            $fullHelper
        );

        $helper = new $fullHelper;
        $helper->checkData($question, $test, $this_currentTest_zipDir, $this_basePath);

        $this->assertEquals('CompletionQuestion', $helper->getType());
        $this->assertEquals('multi', $helper->getSubType());
        $answers = $helper->getConvertedAr('answer');
        $this->assertCount(4, $answers);
        $this->assertEquals('trefwoordenregister.', trim($answers[0]['answer']));
        $this->assertEquals('inhoudsopgave.', trim($answers[1]['answer']));
        $this->assertEquals('voorkant van een boek.', trim($answers[2]['answer']));
        $this->assertEquals('achterkant van een boek.', trim($answers[3]['answer']));
    }
}
