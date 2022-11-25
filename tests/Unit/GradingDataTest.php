<?php

namespace Tests\Unit;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\TestTake;
use tcCore\User;
use Tests\TestCase;

class GradingDataTest extends TestCase
{
    use DatabaseTransactions;

    private User $teacher;
    private TestTake $testTake;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacher = User::whereUsername('sander.smits+TESTSANDERdocent1@test-correct.nl')->first();
        $this->testTake = TestTake::whereUuid('b0333d3c-db00-419b-8d45-e9ca19d86798')->first();
    }

    /**
     * @test
     * */
    public function can_retrieve_same_data_as_used_in_cake()
    {
        $testTakeResponse = $this->get($this->getTestTakeRequest());
        $questionResponse = $this->get($this->getQuestionRequest());
        $participantResponse = $this->get($this->getParticipantRequest());

        $testTakeResponse->assertSuccessful();;
        $questionResponse->assertSuccessful();
        $participantResponse->assertSuccessful();

        $testTakeData = $testTakeResponse->getOriginalContent();
        $questionData = $questionResponse->getOriginalContent();
        $participantData = $participantResponse->getOriginalContent();

        $this->assertNotEmpty($testTakeData);
        $this->assertNotEmpty($questionData);
        $this->assertNotEmpty($participantData);
    }

    /**
     * @test
     * */
    public function can_retrieve_same_data_as_used_in_cake_but_with_answer_ratings()
    {
        $testTakeResponse = $this->get($this->getTestTakeForGradingRequest());

        dd($testTakeResponse->getOriginalContent());
    }

    /**
     * @test
     * */
    public function can_retrieve_same_data_as_used_in_cake_but_better()
    {
        /* -------- Old -------- */
        $testTakeResponse = $this->get($this->getTestTakeRequest());
        $questionResponse = $this->get($this->getQuestionRequest());
        $participantResponse = $this->get($this->getParticipantRequest());

        $testTakeResponse->assertSuccessful();;
        $questionResponse->assertSuccessful();
        $participantResponse->assertSuccessful();

        $testTakeData = $testTakeResponse->getOriginalContent();
        $questionData = $questionResponse->getOriginalContent();
        $participantData = $participantResponse->getOriginalContent();

        /* -------- New -------- */
        $fullDataResponse = $this->get($this->getTestTakeForGradingRequest());
        $fullDataResponse->assertSuccessful();
        $fullData = $fullDataResponse->getOriginalContent();


        /* -------- Assert amount of data is equal -------- */

        $this->assertEquals(count($participantData), $fullData->testParticipants->count());
        $this->assertEquals($questionData->count(), $fullData->test->testQuestions->count());

        /* -------- Assert id's data is equal -------- */
        $this->assertEquals($testTakeData['id'], $fullData->id);

        $participantUuids = collect($participantData)->map(fn($participant) => $participant['uuid'])->sort();
        $fullDataParticipantUuids = $fullData->testParticipants->map(fn($participant) => $participant->uuid)->sort();
        $this->assertEquals($participantUuids, $fullDataParticipantUuids);

        $testQuestionUuids = collect($questionData)->map(fn($question) => $question['uuid'])->sort();
        $fullDataTestQuestionUuids = $fullData->test->testQuestions->map(fn($testQuestion) => $testQuestion->uuid)->sort();
        $this->assertEquals($testQuestionUuids, $fullDataTestQuestionUuids);
    }

    private function getTestTakeRequest($params = []): string
    {
        return self::authUserGetRequest(
            sprintf('/api-c/test_take/%s', $this->testTake->uuid),
            $params,
            $this->teacher
        );
    }

    private function getQuestionRequest(): string
    {
        $params = [
            'filter' => [
                'test_id' => $this->testTake->test_id
            ],
            'mode'   => 'all',
            'order'  => [
                'order' => 'asc'
            ]
        ];

        return self::authUserGetRequest(
            '/api-c/test_question',
            $params,
            $this->teacher
        );
    }

    private function getParticipantRequest(): string
    {
        $params['mode'] = 'all';
        return self::authUserGetRequest(
            sprintf('/api-c/test_take/%s/test_participant', $this->testTake->uuid),
            $params,
            $this->teacher
        );
    }

    private function getTestTakeForGradingRequest($params = []): string
    {
        return self::authUserGetRequest(
            sprintf('/api-c/test_take/%s/grading', $this->testTake->uuid),
            $params,
            $this->teacher
        );
    }
}