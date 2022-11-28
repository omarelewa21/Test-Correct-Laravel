<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Attachment;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Factories\Questions\FactoryQuestionRanking;
use tcCore\QuestionAttachment;
use Tests\TestCase;

/**
 * FactoryQuestionAttachmentTest:
 *
 * Test functionality of adding Attachments to Questions
 *
 */
class FactoryQuestionAttachmentTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @test
     * add multiple video attachments to a question, with default values,
     * or an attachment with a specified link
     */
    public function can_add_video_attachments_to_a_question()
    {
        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionOpenShort::create()->addVideoAttachment()->addVideoAttachment(),
                FactoryQuestionRanking::create()->addVideoAttachment('https://vimeo.com/148751763'),
            ]);

        $testFactory->getTestModel()->fresh()->testQuestions->each(function ($question) {
            $question->question->attachments->each(function ($attachment) {
                $this->assertNotNull($attachment->link);
                $this->assertTrue(strpos($attachment->link, 'youtu') || strpos($attachment->link, 'vimeo'));
            });
        });
    }

    /** @test */
    public function can_add_multiple_types_of_attachments_to_one_question()
    {
        $startCountAttachment = Attachment::count();
        $startCountQuestionAttachment = QuestionAttachment::count();

        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionOpenShort::create()
                    ->addImageAttachment()
                    ->addAudioAttachment()
                    ->addVideoAttachment()
                    ->addPdfAttachment(),
            ]);

        $this->assertEquals($startCountAttachment + 4, Attachment::count());
        $this->assertEquals($startCountQuestionAttachment + 4, QuestionAttachment::count());
    }

    /** @test */
    public function can_add_image_attachments_to_a_question()
    {
        $startCountAttachment = Attachment::count();
        $startCountQuestionAttachment = QuestionAttachment::count();

        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionOpenShort::create()->addImageAttachment(),
            ]);

        $testFactory->getTestModel()->fresh()->testQuestions->each(function ($question) {
            $question->question->attachments->each(function ($attachment) {
                $this->assertNotNull($attachment);
            });
        });

        $this->assertGreaterThan($startCountAttachment, Attachment::count());
        $this->assertGreaterThan($startCountQuestionAttachment, QuestionAttachment::count());
    }

    /** @test */
    public function can_add_audio_attachments_to_a_question()
    {
        $startCountAttachment = Attachment::count();
        $startCountQuestionAttachment = QuestionAttachment::count();

        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionOpenShort::create()->addAudioAttachment(),
            ]);

        $this->assertFalse($testFactory->getTestModel()->fresh()->testQuestions->first()->question->attachments->isEmpty());

        $this->assertGreaterThan($startCountAttachment, Attachment::count());
        $this->assertGreaterThan($startCountQuestionAttachment, QuestionAttachment::count());
    }

    /** @test */
    public function can_add_audio_attachments_with_settings_to_a_question()
    {
        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionOpenShort::create()
                    ->addAudioAttachment(true, true, 150)
                    ->addAudioAttachment(false, false, 150)
                    ->addAudioAttachment(true)
                    ->addAudioAttachment(false, true),
            ]);

        $AllSettingsAttachmentjson = $testFactory->getTestModel()->fresh()->testQuestions->first()->question->attachments->first()->json;

        $this->assertStringContainsString('timeout', $AllSettingsAttachmentjson);
        $this->assertStringContainsString('play_once', $AllSettingsAttachmentjson);
        $this->assertStringContainsString('pausable', $AllSettingsAttachmentjson);

        $testFactory->getTestModel()->fresh()->testQuestions->first()->question->attachments->each(function ($attachment) {
            $this->assertNotEquals("[]", $attachment->json);
        });
    }

    /** @test */
    public function can_add_pdf_attachment_to_a_question()
    {
        $startCountAttachment = Attachment::count();
        $startCountQuestionAttachment = QuestionAttachment::count();

        $testFactory = FactoryTest::create()
            ->addQuestions([
                FactoryQuestionOpenShort::create()->addPdfAttachment(),
            ]);

        $this->assertGreaterThan($startCountAttachment, Attachment::count());
        $this->assertGreaterThan($startCountQuestionAttachment, QuestionAttachment::count());
    }
}
