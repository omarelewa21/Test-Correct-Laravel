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

class SmallsourceMultiplechoiceHelperTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function a_multiplechoice_questions_has_answers()
    {
        $zipDir = '';
        $basePath = '';

        $test = new Test();
        $question = simplexml_load_file(__DIR__.'/../../_fixtures_quayn_qti/smallsourceMultiplechoiceSample1.xml',
            'SimpleXMLElement', LIBXML_NOCDATA);

        $result = QtiImportController::parseQuestion($question,$test,$zipDir, $basePath);

        $helper = $result->helper;
        $this->assertEquals('MultipleChoiceQuestion', $helper->getType());
        $this->assertEquals('MultipleChoice', $helper->getSubType());
        $answers = $helper->getConvertedAr('answer');
        $this->assertCount(6, $answers);
        $this->assertEquals('door de afbeelding op de voorkant te bekijken.', trim($answers[0]['answer']));
        $this->assertEquals('door de titel te lezen en de naam van de schrijver.', trim($answers[1]['answer']));
        $this->assertEquals('door op de achterkant van het boek te kijken.', trim($answers[2]['answer']));
        $this->assertEquals('door de eerste bladzijde te lezen of een ander fragment.', trim($answers[3]['answer']));
    }
}
