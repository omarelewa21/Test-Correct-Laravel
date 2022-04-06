<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\User;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\Question;
use tcCore\GroupQuestion;
use tcCore\DrawingQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestTrait;
use Tests\Traits\DrawingQuestionTrait;
use Tests\Traits\MultipleChoiceQuestionTrait;
use Tests\Traits\GroupQuestionTrait;
use Illuminate\Support\Facades\DB;

class SvgBackGroundImageTest extends TestCase
{
    /**
     * @test
     * @runInSeparateProcess
     * run in separateProcess needed because glide is sending headers and I dont have time to stub it;
     */
    public function if_i_create_an_svg_i_can_download_the_image_i_uploaded()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());
        Storage::fake(SvgHelper::DISK);
        $uuid = 'f0edd769-7363-4cc8-ab56-fa0067798f33';
        $svgHelper = new SvgHelper($uuid);
        $imageIdentifier = 'b6652000-236c-4f5a-bbf9-ff9cc1342e91';
        $svgHelper->addQuestionImage($imageIdentifier, UploadedFile::fake()->image('black_pixel.png'));
        $svgHelper->updateQuestionLayer(
            sprintf(
                '<image identifier="%s"/>',

                $imageIdentifier
            )
        );
        $href = route('drawing-question.background-question-svg', [
            'drawingQuestion' => $uuid,
            'identifier' => $imageIdentifier,
        ]);

        $response = $this->get($href);
        $response->assertStatus(200);
    }

    /**
     * @test
     * @runInSeparateProcess
     * run in separateProcess needed because glide is sending headers and I dont have time to stub it;
     */
    public function if_i_create_an_svg_i_can_download_a_background_image_in_the_correction_model_layer()
    {
        $this->withoutExceptionHandling();
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());
        Storage::fake(SvgHelper::DISK);
        $uuid = 'f0edd769-7363-4cc8-ab56-fa0067798f33';
        $svgHelper = new SvgHelper($uuid);
        $imageIdentifier = 'b6652000-236c-4f5a-bbf9-ff9cc1342e91';
        $svgHelper->addAnswerImage($imageIdentifier, UploadedFile::fake()->image('black_pixel.png'));
        $svgHelper->updateAnswerLayer(
            sprintf(
                '<image identifier="%s"/>',

                $imageIdentifier
            )
        );
        $href = route('drawing-question.background-answer-svg', [
            'drawingQuestion' => $uuid,
            'identifier' => $imageIdentifier,
        ]);

        $response = $this->get($href);
        $response->assertStatus(200);
    }

    /** @test */
    public function as_the_author_i_can_access_the_svg()
    {
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());
        Storage::fake(SvgHelper::DISK);
        $uuid = 'a0edd769-7363-4cc8-ab56-fa0067798f33';
        $svgHelper = new SvgHelper($uuid);

        $href = route('drawing-question.svg', [
            'drawingQuestion' => $uuid,
        ]);

        $response = $this->get($href);
        $this->assertXmlStringEqualsXmlString(
            $svgHelper->getSvgWithUrls(),
            $response->getContent()
        );

        $response->assertHeader('content-type', 'image/svg+xml');
        $response->assertStatus(200);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_serve_correction_model_png()
    {
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());
        Storage::fake(SvgHelper::DISK);
        $uuid = 'a0edd769-7363-4cc8-ab56-fa0067798f33';
        $svgHelper = new SvgHelper($uuid);

        $href = route('drawing-question.correction_model', [
            'drawingQuestion' => $uuid,
        ]);

        $response = $this->get($href);
        $response->assertStatus(200);
    }
}
