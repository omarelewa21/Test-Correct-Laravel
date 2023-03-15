<?php

namespace tcCore\Http\Enums\CoLearning;

use tcCore\Http\Enums\Sortable;

enum RatingStatus: string implements Sortable
{
    case Green  = 'green';
    case Orange = 'orange';
    case Red    = 'red';
    case Grey   = 'grey';

    public static function get(int $answersToRate, int $answersRated, int|float $testParticipantsFinishedRatingPercentage): RatingStatus
    {
        $percentageRated = intval(
            $answersToRate > 0
                ? $answersRated / $answersToRate * 100
                : 0
        );

        if (self::participantRatingIsFallingBehind($percentageRated, $testParticipantsFinishedRatingPercentage)) {
            return RatingStatus::Red;
        }

        if ($percentageRated === 100) {
            return RatingStatus::Green;
        }
        if ($percentageRated === 0) {
            return RatingStatus::Grey;
        }
        return RatingStatus::Orange;
    }

    private static function participantRatingIsFallingBehind(int $percentageRated, float|int $testParticipantsFinishedRatingPercentage): bool
    {
        return $percentageRated < 50
            && $testParticipantsFinishedRatingPercentage > 50;
    }

    public function getSortWeight(): int
    {
        return match ($this) {
            self::Green => 40,
            self::Orange => 30,
            self::Grey => 20,
            self::Red => 10,
        };
    }

    /**
     * Returns whether two RatingStatusses are equal
     * @param RatingStatus $ratingStatus
     * @return bool
     */
    public function equals(RatingStatus $ratingStatus): bool
    {
        return $this->value === $ratingStatus->value;
    }
}
