<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\EckidUser;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use Tests\TestCase;

class PurifierTest extends TestCase
{
    /** @test */
    public function can_get_a_correct_ckeditor5_inline_imageInline_tag_after_HTMLPurifier()
    {
        $html = <<<HTML
        <p>
            CKeditor5 text with inline image styling. image is right aligned.
            <img class="image_resized image-style-align-right" style="width:8.48%;" src="/questions/inlineimage/abc.png" alt="alt tekst">
        </p>
        <p>
            CKeditor5 text with inline image styling. image is left aligned.
            <img class="image_resized image-style-align-left" style="width:8.48%;" src="/questions/inlineimage/abc.png" alt="alt tekst">
        </p>
        HTML;

        $purifiedHtml = clean($html);

        $this->assertStringContainsString('<img', $purifiedHtml);
        $this->assertStringContainsString('<p', $purifiedHtml);
        $this->assertStringContainsString('style="width:8.48%;"', $purifiedHtml);
        $this->assertStringContainsString('src="/questions/inlineimage/abc.png"', $purifiedHtml);
        $this->assertStringContainsString('image_resized', $purifiedHtml);
        $this->assertStringContainsString('image-style-align-right', $purifiedHtml);
    }

    /** @test */
    public function can_get_a_correct_ckeditor5_inline_imageBlock_tag_after_HTMLPurifier()
    {
        $html = <<<HTML
        <p>
            CKeditor5 text with a inline image styled as blocks fixed to the right side of the container
        </p>
        <figure class="image image_resized image-style-side" style="width:9.37%;">
            <img src="/questions/inlineimage/abc.png" alt="alt tekst">
        </figure>
        <p>
            CKeditor5 text with a number of inline images styled as blocks (not inline with text)
        </p>
        <figure class="image image_resized image-style-block-align-right" style="width:55.55%;">
            <img src="/questions/inlineimage/def.png" alt="alt2">
        </figure>
        <figure class="image image_resized image-style-block-align-left" style="width:12.22%;">
            <img src="/questions/inlineimage/ghi.png" alt="alternatief3">
        </figure>
        <figure class="image image_resized image-style-align-center" style="width:5.96%;">
            <img src="/questions/inlineimage/jkl.png" alt="different4">
        </figure>
        HTML;

        $purifiedHtml = clean($html);

        $this->assertStringContainsString('<img', $purifiedHtml);
        $this->assertStringContainsString('<figure', $purifiedHtml);
        $this->assertStringContainsString('style="width:9.37%;"', $purifiedHtml);
        $this->assertStringContainsString('src="/questions/inlineimage/abc.png"', $purifiedHtml);
        $this->assertStringContainsString('image', $purifiedHtml);
        $this->assertStringContainsString('image_resized', $purifiedHtml);
        $this->assertStringContainsString('image-style-side', $purifiedHtml);
        $this->assertStringContainsString('image-style-align-center', $purifiedHtml);
        $this->assertStringContainsString('image-style-block-align-left', $purifiedHtml);
        $this->assertStringContainsString('image-style-block-align-right', $purifiedHtml);
    }

    /** @test */
    public function can_get_a_correct_ckeditor5_inline_PASTED_image_after_HTMLPurifier()
    {
        $html = <<<HTML
        <p>&nbsp;</p>
        <p>
            Pasted images from clipboard have px as unit instead of percentage
            <img class="image_resized image-style-align-right" style="width:211.312px;" src="http://testwelcome.test-correct.test/questions/inlineimage/y7ZeWnCqq4snMjfzs8tZDbHzUbjtCea5LpNmRZEz.png" alt="y7ZeWnCqq4snMjfzs8tZDbHzUbjtCea5LpNmRZEz.png">
        </p>
        <figure class="image image_resized image-style-side" style="width:352.891px;">
            <img src="http://testwelcome.test-correct.test/questions/inlineimage/zDuupM5AQG9R8Vpu4yjq2ywRYjBzn3TQImM87u5H.png" alt="zDuupM5AQG9R8Vpu4yjq2ywRYjBzn3TQImM87u5H.png">
        </figure>
        HTML;

        $purifiedHtml = clean($html);

        $this->assertStringContainsString('src="http://testwelcome.test-correct.test/questions/inlineimage/zDuupM5AQG9R8Vpu4yjq2ywRYjBzn3TQImM87u5H.png"', $purifiedHtml);
        $this->assertStringContainsString('alt="zDuupM5AQG9R8Vpu4yjq2ywRYjBzn3TQImM87u5H.png"', $purifiedHtml);
        $this->assertStringContainsString('<img', $purifiedHtml);
        $this->assertStringContainsString('<figure', $purifiedHtml);
        $this->assertStringContainsString('style="width:352.891px;"', $purifiedHtml);
        $this->assertStringContainsString('style="width:211.312px;"', $purifiedHtml);
    }

    /** @test */
    public function can_get_a_correct_ckeditor5_alt_tekst_on_an_inline_image_after_HTMLPurifier()
    {
        $html = <<<HTML
            CKeditor5 text with image that has an edited alt text
            <img src="/questions/inlineimage/abc.png" alt="user input alt text">
        HTML;

        $purifiedHtml = clean($html);

        $this->assertStringContainsString('<img', $purifiedHtml);
        $this->assertStringContainsString('alt="user input alt text"', $purifiedHtml);
    }
    /** @test */
    public function can_add_latex_attributes_to_math_ml() {
        $expected = <<<HTML
            <math xmlns="http://www.w3.org/1998/Math/MathML">
               <semantics>
                  <mfrac><mn>1</mn><mn>2</mn></mfrac>
                  <annotation encoding="LaTeX">\\frac12</annotation>
               </semantics>
            </math> 
        HTML;

        $this->assertEquals($expected, clean($expected));

    }
}
