<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:05
 */

namespace Tests\Traits;


trait CompletionQuestionTrait
{

    private function createCompletionQuestion($attributes){
        $response = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
        $testQuestionId = $response->decodeResponseJson()['id'];
        $this->originalQuestionId = $testQuestionId;
        return $testQuestionId;
    }

    private function editCompletionQuestion($uuid,$attributes){
        $response = $this->put(
            'api-c/test_question/'.$uuid,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
    }

    private function createCompletionQuestionInGroup($attributes,$groupQuestionUuid){
        $url = 'api-c/group_question_question/'.$groupQuestionUuid;
        $response = $this->post(
            $url,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
        $testQuestionId = $response->decodeResponseJson()['id'];
        $this->originalQuestionId = $testQuestionId;
        return $testQuestionId;
    }

    private function editCompletionQuestionInGroup($uuidGroupQuestion,$uuidGroupQuestionQuestion,$attributes){
        $url = 'api-c/group_question_question/'.$uuidGroupQuestion.'/'.$uuidGroupQuestionQuestion;
        $response = $this->put(
            $url,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
    }

    private function getCompletionQuestionAttributes(array $overrides = []): array
    {
        return array_merge([
            'question'               => '<p>lorum [ipsum] dolor [sit] amet, consectetur adipiscing elit</p>',
            'type'                   => 'CompletionQuestion',
            'score'                  => '5',
            'order'                  => 0,
            'subtype'                => 'completion',
            'maintain_position'      => '0',
            'discuss'                => '1',
            'decimal_score'          => '0',
            'add_to_database'        => 1,
            'attainments'            => [],
            'note_type'              => 'NONE',
            'is_open_source_content' => 1,
            'tags'                   => [],
            'rtti'                   => null,
            'bloom'                  => null,
            'miller'                  => null,
            'test_id'                => '9',
            "closeable"=> 0,
        ], $overrides);
    }

    private function getCompletionQuestionSelectionAttributes(array $overrides = []): array
    {
        return $this->getCompletionQuestionAttributes(array_merge([
            'question'               => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis consequat pharetra augue dapibus ultricies. In hac habitasse platea dictumst. Quisque lacinia efficitur velit, fermentum [convallis|asdfasf|asdfsaf]sapien consequat eu. Maecenas vitae massa purus. Praesent in dolor in quam rutrum accumsan. Donec orci enim, maximus a tortor ut, porttitor sodales tortor. Sed pellentesque leo mi, nec hendrerit metus tempus non. In consequat, metus sed ornare sollicitudin, nisl nulla luctus arcu, eu dictum justo mi nec nisl. Maecenas convallis mauris eget aliquet scelerisque. Vestibulum faucibus tortor elit, sed lacinia est condimentum in. </p>',
            'subtype'                => 'multi',
            ], $overrides));
    }


}