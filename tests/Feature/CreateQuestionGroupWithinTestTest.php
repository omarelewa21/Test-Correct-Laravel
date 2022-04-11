<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\TestQuestion;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\GroupQuestionTrait;
use tcCore\Traits\Dev\OpenQuestionTrait;
use tcCore\Traits\Dev\TestTrait;

class CreateQuestionGroupWithinTestTest extends TestCase
{
    use DatabaseTransactions, OpenQuestionTrait, TestTrait, GroupQuestionTrait;


    /** @test */
    public function a_teacher_can_add_a_question_group_to_a_test(): void
    {
        $testId = $this->addTestAndReturnTestId();

        $groupId = $this->addQuestionGroupAndReturnId($testId);

        $this->addOpenQuestionToGroup($groupId);

    }

    private function addOpenQuestionToGroup(int $groupId)
    {

        $response = $this->post(
            sprintf('group_question_question/%d', $groupId),
            static::getTeacherOneAuthRequestData(
                $this->getGroupOpenQuestionAttributes()
            )
        );

        $response->assertStatus(200);

        return $response->decodeResponseJson()['id'];
    }

    private function getGroupOpenQuestionAttributes(array $overrides = []): array
    {
        return array_merge([
            'question'               => '<p>vraag</p>\r\n',
            'answer'                 => '<p>antoord</p>\r\n',
            'type'                   => 'OpenQuestion',
            'score'                  => '5',
            'order'                  => 0,
            'subtype'                => 'short',
            'maintain_position'      => 0,
            'discuss'                => '1',
            'decimal_score'          => '0',
            'add_to_database'        => 1,
            'attainments'            => [],
            'note_type'              => 'NONE',
            'is_open_source_content' => 1,
            'tags'                   => [],
            'rtti'                   => null,
        ], $overrides);
    }


}
