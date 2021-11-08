<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Question;
use Tests\TestCase;

class QuestionInlineImagesTest extends TestCase
{

    public $options = [
        '/custom/imageload.php?filename=TzSD4yUANz-vergelijking.PNG',
        'http://testportal.test-correct.test/custom/imageload.php?filename=pVnRyl8TvhQPVI4C1YMh',
        'https://testportal.test-correct.nl/custom/imageload.php?filename=jMR54ewpel-anonymous%27%60%7E%3B.jpg',
        '/custom/imageload.php?filename=ABIcUx2v7x-1.png',
        'https://testportal.test-correct.nl/custom/imageload.php?filename=nlFT3wQk0c-01.1001.2000.3000_1.png',
        'https://testportal.test-correct.nl/custom/imageload.php?filename=Tex1qkvc4i-29092017-Erasmiaans-Toets-bespreken.jpg'
    ];

    public function testExample()
    {

//        $question = Question::find(25);
//
//        dd($question->getQuestionHtml());
    }
}
