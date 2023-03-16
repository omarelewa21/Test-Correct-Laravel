<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Http\Enums\CoLearning\AbnormalitiesStatus;
use tcCore\Http\Enums\CoLearning\RatingStatus;

use Tests\TestCase;

class CoLearningStudentStatusTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * @dataProvider abnormalitiesDataProvider
     */
    public function canGetAbnormalitiesStatusses(AbnormalitiesStatus $expected, int $testParticipantAmountOfAbnormalities, int $averageAmountOfAbnormalities, bool $enoughDataAvailable)
    {
        $this->assertObjectEquals(
            $expected,
            AbnormalitiesStatus::get(
                testParticipantAbnormalities: $testParticipantAmountOfAbnormalities,
                averageAbnormalitiesAmount: $averageAmountOfAbnormalities,
                enoughDataAvailable: $enoughDataAvailable,
            )
        );
    }

    /**
     * @test
     * @dataProvider ratingsDataProvider
     */
    public function canGetRatingStatusses(RatingStatus $expected, $answersToRate, $answersRated, $testParticipantsFinishedRatingPercentage)
    {
        $this->assertObjectEquals(
            $expected,
            RatingStatus::get(
                $answersToRate,
                $answersRated,
                $testParticipantsFinishedRatingPercentage
            )
        );
    }

    public function abnormalitiesDataProvider()
    {
        return [
            'more than 5 percent under the average'                                       => [
                'expected'                             => AbnormalitiesStatus::Happy,
                'testParticipantAmountOfAbnormalities' => 94,
                'averageAmountOfAbnormalities'         => 100,
                'enoughDataAvailable'                  => true,
            ],
            'less (or equal) than 5 percent under the average'                            => [
                'expected'                             => AbnormalitiesStatus::Neutral,
                'testParticipantAmountOfAbnormalities' => 95,
                'averageAmountOfAbnormalities'         => 100,
                'enoughDataAvailable'                  => true,
            ],
            'way more than 5 percent less than the average'                               => [
                'expected'                             => AbnormalitiesStatus::Happy,
                'testParticipantAmountOfAbnormalities' => 3,
                'averageAmountOfAbnormalities'         => 4,
                'enoughDataAvailable'                  => true,
            ],
            'less than 15 percent over the average'                                       => [
                'expected'                             => AbnormalitiesStatus::Neutral,
                'testParticipantAmountOfAbnormalities' => 114,
                'averageAmountOfAbnormalities'         => 100,
                'enoughDataAvailable'                  => true,
            ],
            'more or equal than 15 percent over the average #1'                           => [
                'expected'                             => AbnormalitiesStatus::Sad,
                'testParticipantAmountOfAbnormalities' => 115,
                'averageAmountOfAbnormalities'         => 100,
                'enoughDataAvailable'                  => true,
            ],
            'more or equal than 15 percent over the average #2'                           => [
                'expected'                             => AbnormalitiesStatus::Sad,
                'testParticipantAmountOfAbnormalities' => 200,
                'averageAmountOfAbnormalities'         => 100,
                'enoughDataAvailable'                  => true,
            ],
            'any difference from the average but not enough data (rated >= 4 answers) #1' => [
                'expected'                             => AbnormalitiesStatus::Default,
                'testParticipantAmountOfAbnormalities' => 0,
                'averageAmountOfAbnormalities'         => 100,
                'enoughDataAvailable'                  => false,
            ],
            'any difference from the average but not enough data (rated >= 4 answers) #2' => [
                'expected'                             => AbnormalitiesStatus::Default,
                'testParticipantAmountOfAbnormalities' => 0,
                'averageAmountOfAbnormalities'         => 0,
                'enoughDataAvailable'                  => false,
            ],
            'any difference from the average but not enough data (rated >= 4 answers) #3' => [
                'expected'                             => AbnormalitiesStatus::Default,
                'testParticipantAmountOfAbnormalities' => 100,
                'averageAmountOfAbnormalities'         => 0,
                'enoughDataAvailable'                  => false,
            ],
            'any difference from the average but not enough data (rated >= 4 answers) #4' => [
                'expected'                             => AbnormalitiesStatus::Default,
                'testParticipantAmountOfAbnormalities' => 100,
                'averageAmountOfAbnormalities'         => 100,
                'enoughDataAvailable'                  => false,
            ],
            'average abnormalities amount is zero #1'                                     => [
                'expected'                             => AbnormalitiesStatus::Neutral,
                'testParticipantAmountOfAbnormalities' => 1,
                'averageAmountOfAbnormalities'         => 0,
                'enoughDataAvailable'                  => true,
            ],
            'average abnormalities amount is zero #2'                                     => [
                'expected'                             => AbnormalitiesStatus::Neutral,
                'testParticipantAmountOfAbnormalities' => 5, //not realistic that the average is (rounded) 0 when an individual amount > 1 or even more
                'averageAmountOfAbnormalities'         => 0,
                'enoughDataAvailable'                  => true,
            ],
        ];
    }

    public function ratingsDataProvider()
    {
        return [
            'Green #1' => [
                'expected'                                 => RatingStatus::Green,
                'answersToRate'                            => 2,
                'answersRated'                             => 2,
                'testParticipantsFinishedRatingPercentage' => 100,
            ],
            'Green #2' => [
                'expected'                                 => RatingStatus::Green,
                'answersToRate'                            => 2,
                'answersRated'                             => 2,
                'testParticipantsFinishedRatingPercentage' => 10,
            ],
            'Green #3' => [
                'expected'                                 => RatingStatus::Green,
                'answersToRate'                            => 1,
                'answersRated'                             => 1,
                'testParticipantsFinishedRatingPercentage' => 100,
            ],
            'Orange #1' => [
                'expected'                                 => RatingStatus::Orange,
                'answersToRate'                            => 2,
                'answersRated'                             => 1,
                'testParticipantsFinishedRatingPercentage' => 100,
            ],
            'Orange #2' => [
                'expected'                                 => RatingStatus::Orange,
                'answersToRate'                            => 2,
                'answersRated'                             => 1,
                'testParticipantsFinishedRatingPercentage' => 10,
            ],
            'Red #1' => [
                'expected'                                 => RatingStatus::Red,
                'answersToRate'                            => 2,
                'answersRated'                             => 0, // 0% IS < 50% of answersToRate
                'testParticipantsFinishedRatingPercentage' => 51, // > 50
            ],
            'Red #2' => [
                'expected'                                 => RatingStatus::Red,
                'answersToRate'                            => 1,
                'answersRated'                             => 0, // 0% IS < 50% answersToRate
                'testParticipantsFinishedRatingPercentage' => 75, // > 50
            ],
            'NOT Red #1 (50% or less participants are finished with rating)' => [
                'expected'                                 => RatingStatus::Grey,
                'answersToRate'                            => 2,
                'answersRated'                             => 0,
                'testParticipantsFinishedRatingPercentage' => 50, //50 or less means it is not Red
            ],
            'NOT Red #2 (50% or more of the available answers rated by the testParticipant)' => [
                'expected'                                 => RatingStatus::Orange,
                'answersToRate'                            => 2,
                'answersRated'                             => 1, // 50% IS NOT < 50% answersToRate
                'testParticipantsFinishedRatingPercentage' => 51,
            ],
            'Grey #1' => [
                'expected'                                 => RatingStatus::Grey,
                'answersToRate'                            => 0, //it is possible both answers were not answered by the other studenta that were taking the test
                'answersRated'                             => 0,
                'testParticipantsFinishedRatingPercentage' => 0,
            ],
            'Grey #2 (start of CO-Learning, nothing rated yet)' => [
                'expected'                                 => RatingStatus::Grey,
                'answersToRate'                            => 2,
                'answersRated'                             => 0,
                'testParticipantsFinishedRatingPercentage' => 0,
            ],
        ];
    }

}