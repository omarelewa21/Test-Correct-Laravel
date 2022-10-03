<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use tcCore\Http\Controllers\PreviewAnswerModelController;
use tcCore\Http\Controllers\PrintTestController;
use tcCore\Test;
use Tests\TestCase;

class PdfGenerationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function speed_test_with_or_without_wiris()
    {
        $tests = [
            'base: test 10 questions no wiris or attachments' => 426,
            'wiris: test 10 questions, 10 wiris, no attachments' => 427,
            'attachments: test 10 questions, no wiris, 4pdf attachments' => 428,
        ];
        $testResults = [];
        $request = new Request();




        foreach($tests as $name => $test){
            $test = Test::find($test);

            $testResults['full'][$name] = (new PrintTestController)->featureSpeedTest($test, 'full');
        }
        foreach($tests as $name => $test){
            $test = Test::find($test);

            $testResults['main'][$name] = (new PrintTestController)->featureSpeedTest($test, 'main');
        }
        foreach($tests as $name => $test){
            $test = Test::find($test);

            $testResults['cover'][$name] = (new PrintTestController)->featureSpeedTest($test, 'cover');
        }
        $test = Test::find(428);

//        foreach($tests as $name => $test){
//            $test = Test::find($test);
//
//            $start = microtime(true);
//            (new PreviewAnswerModelController)->featureSpeedTest($test);
//            $testResults['answerModel'][$name] = microtime(true) - $start;
//        }

        dump($testResults);
    }

    /**
     * add function to printTestController:
     */
    public function featureSpeedTest(Test $test, string $whatToTest)
    {
        $this->test = $test;

        switch($whatToTest){
            case 'cover':
                $start = microtime(true);
                $this->generateCoverPdf();
                $end = microtime(true);
                return $end - $start;
            case 'main':
                $start = microtime(true);
                $this->generateMainPdf();
                $end = microtime(true);
                return $end - $start;
            case 'pdf-attachments':
                $start = microtime(true);
                $this->createPdfAttachmentsDownload();
                $end = microtime(true);
                return $end - $start;
            case 'full':
            default:
                $start = microtime(true);
                $this->createPdfDownload();
                $end = microtime(true);
                return $end - $start;
        }
    }
}