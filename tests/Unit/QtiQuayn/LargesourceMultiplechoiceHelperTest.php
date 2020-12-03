<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit\QtiQuayn;


use tcCore\Http\Controllers\QtiImportController;
use tcCore\Test;
use tcCore\User;
use Tests\TestCase;

class LargesourceMultiplechoiceHelperTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function a_multiplechoice_questions_has_answers()
    {
        $zipDir = '';
        $basePath = '';

        $test = new Test();
        $question = simplexml_load_file(__DIR__.'/../../_fixtures_quayn_qti/largesourceMultiplechoiceSample1.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $result = QtiImportController::parseQuestion($question,$test,$zipDir, $basePath);


        $this->assertEquals('CompletionQuestion', $result->helper->getType());
        $this->assertEquals('multi', $result->helper->getSubType());
        $answers = $result->helper->getConvertedAr('answer');
        $this->assertCount(4, $answers);
        $this->assertEquals('trefwoordenregister.', trim($answers[0]['answer']));
        $this->assertEquals('inhoudsopgave.', trim($answers[1]['answer']));
        $this->assertEquals('voorkant van een boek.', trim($answers[2]['answer']));
        $this->assertEquals('achterkant van een boek.', trim($answers[3]['answer']));
    }
}
