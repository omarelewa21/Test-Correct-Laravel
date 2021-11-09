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
}
