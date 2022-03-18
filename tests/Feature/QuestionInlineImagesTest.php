<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use tcCore\Question;
use tcCore\Services\QuestionHtmlConverter;
use Tests\TestCase;

class QuestionInlineImagesTest extends TestCase
{
    /**
     * @test
     */
    public function question_html_converter_can_convert_question_html_inline_image_sources_to_named_route_by_searching_for_a_pattern()
    {
        $question = Question::where('question', 'like', "%" . Question::INLINE_IMAGE_PATTERN . "%")->first();
        $questionHtml = $question->getQuestionHtml();
        $questionHtmlConverter = new QuestionHtmlConverter($questionHtml);
        $routeWithoutImageNameParameter = config('app.base_url') . 'questions/inlineimage/';

        $convertedHtml = $questionHtmlConverter->convertImageSourcesWithPatternToNamedRoute('inline-image', 'filename=');

        $this->assertNotEquals($questionHtml, $convertedHtml);
        $this->assertStringContainsString($routeWithoutImageNameParameter, $convertedHtml);
        $this->assertStringNotContainsString(Question::INLINE_IMAGE_PATTERN, $convertedHtml);
    }

    /**
     * @test
     */
    public function question_computed_html_attribute_is_converted_without_inline_image_pattern()
    {
        $question = Question::where('question', 'like', "%" . Question::INLINE_IMAGE_PATTERN . "%")->first();

        $originalQuestionHtml = $question->getQuestionHtml();
        $convertedQuestionHtml = $question->converted_question_html;

        $this->assertNotEquals($originalQuestionHtml, $convertedQuestionHtml);

        $this->assertTrue(Str::contains($originalQuestionHtml, Question::INLINE_IMAGE_PATTERN));
        $this->assertFalse(Str::contains($convertedQuestionHtml, Question::INLINE_IMAGE_PATTERN));
    }

    /**
     * @test
     */
    public function can_convert_html_with_mathml_entities()
    {
        $HTMLWithMath = '<p>Codecogs:&nbsp;<img alt="\sqrt[3]{2}" src="https://latex.codecogs.com/gif.latex?%5Csqrt%5B3%5D%7B2%7D" />&nbsp;</p><p>&nbsp;</p><p>Wiris:&nbsp; &nbsp;<math xmlns="http://www.w3.org/1998/Math/MathML"><mroot><mn>2</mn><mn>3</mn></mroot></math></p>                <p>&nbsp;</p><p>&nbsp;</p><p>Codecogs:&nbsp;<img alt="\sqrt[a]{b}" src="https://latex.codecogs.com/gif.latex?%5Csqrt%5Ba%5D%7Bb%7D" /> = 10</p><p>Wiris:&nbsp; &nbsp;<math xmlns="http://www.w3.org/1998/Math/MathML"><mroot><mi>b</mi><mi>a</mi></mroot><mo>=</mo><mn>10</mn></math></p><p>&nbsp;</p><h1 style="margin:24pt 0in 0.0001pt"><span style="font-size:14pt"><span style="line-height:115%"><span style="font-family:Cambria,serif"><span style="color:#365f91">Grafisch ontwerp onboarding wizard</span></span></span></span></h1><p style="margin:0in 0in 10pt">&nbsp;</p><p style="margin:0in 0in 10pt"><span style="font-size:11pt"><span style="line-height:115%"><span style="font-family:Calibri,sans-serif">De wizard ziet er uit als onderstaande screenshots, maar dan met een min/max knop erboven:</span></span></span></p><p style="margin:0in 0in 10pt"><span style="font-size:11pt"><span style="line-height:115%"><span style="font-family:Calibri,sans-serif"><img height="553" src="https://testportal.test-correct.nl/custom/imageload.php?filename=PoOSvV6RZQ-2020_05_12_11_40_271.png" width="1001" /></span></span></span></p><p style="margin:0in 0in 10pt"><span style="font-size:11pt"><span style="line-height:115%"><span style="font-family:Calibri,sans-serif">Dus:</span></span></span></p><p style="margin:0in 0in 10pt"><span style="font-size:11pt"><span style="line-height:115%"><span style="font-family:Calibri,sans-serif"><span lang="NL" style="font-size:16.0pt"><span style="line-height:115%">Onboarding wizard</span></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>Max/minimize knopje</b></span></span></span></p><p style="margin:0in 0in 10pt">&nbsp;</p><p style="margin:0in 0in 10pt"><span style="font-size:11pt"><span style="line-height:115%"><span style="font-family:Calibri,sans-serif">Welkom terug Carlo. Je bent goed bezig! (als de gebruiker al een keer ingelogd, anders Welkom Carlo, we willen je graag snel op weg helpen binnen Test-Correct!)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Logo </span></span></span></p>';

        $questionHtmlConverter = new QuestionHtmlConverter($HTMLWithMath);
        $convertedHtml = $questionHtmlConverter->convertImageSourcesWithPatternToNamedRoute('inline-image', 'filename=');

        $this->assertNotEquals($HTMLWithMath, $convertedHtml);
        $this->assertStringContainsString('<math', $convertedHtml);
        $this->assertStringNotContainsString('testportal.test-correct.nl/custom/imageload.php?filename=PoOSvV6RZQ-2020_05_12_11_40_271.png', $convertedHtml);
    }

    /** @test */
    public function integration_test_with_sample_that_does_not_convert_correct()
    {
        $html = '<p><strong>Aangepast groepje</strong><math xmlns="http://www.w3.org/1998/Math/MathML"></math><mroot><mn></mn><math xmlns="http://www.w3.org/1998/Math/MathML"><mroot><mn>5</mn><mn>2</mn></mroot></math></mroot></p>

<p><img height="142" src="https://testportal.test-correct.nl/custom/imageload.php?filename=f2x9TseUoNmdXZo9BfDw" width="354" alt="imageload.php?filename=f2x9TseUoNmdXZo9BfDw"></p>

<p> </p>';
        $questionHtmlConverter = new QuestionHtmlConverter($html);

        $convertedHtml = $questionHtmlConverter->convertImageSourcesWithPatternToNamedRoute('inline-image', Question::INLINE_IMAGE_PATTERN );
        dd($convertedHtml);
    }

    /** @test */
    public function integration_test_for_willem_van_oranje_where_question_inline_image_is_rendered_from_portal_not_laravel()
    {
        $html = '<p><img height="142" src="https://testportal.test-correct.nl/custom/imageload.php?filename=f2x9TseUoNmdXZo9BfDw" width="354" alt="imageload.php?filename=f2x9TseUoNmdXZo9BfDw"></p>';

        $questionHtmlConverter= new QuestionHtmlConverter($html);
        $convertedHtml = $questionHtmlConverter->convertImageSourcesWithPatternToNamedRoute('inline-image', Question::INLINE_IMAGE_PATTERN );
        dd($convertedHtml);
    }
}
