<?php

namespace tcCore\Http\Enums\CoLearning;

use tcCore\Http\Enums\Sortable;

enum AbnormalitiesStatus: string implements Sortable
{
    case Happy   = 'happy';
    case Neutral = 'neutral';
    case Sad     = 'sad';
    case Default = 'default';

    /**
     * The average amount of abnormalities for a test_take is 100%.
     * - The status is determined by the difference compared to the average amount.
     * Happy: less than (<) 95% of the average amount of abnormalities
     * Neutral: less than (<) 115% of the average amount of abnormalities
     * Sad: more or equal to (>=) 115%  of the average amount of abnormalities
     * Default: no calculated percentage (NULL)
     */
    public static function get(int $testParticipantAbnormalities, int $averageAbnormalitiesAmount, bool $enoughDataAvailable)
    {
        if (!$enoughDataAvailable) {
            return self::Default;
        }
        if ($averageAbnormalitiesAmount === 0) {
            return self::Neutral;
        }

        $differenceWithTheAverageAbormalitiesCount = self::getPercentualDifferenceComparedToTheAverageAmountOfAbnormalities($testParticipantAbnormalities, $averageAbnormalitiesAmount);

        if ($differenceWithTheAverageAbormalitiesCount < -5) {
            return self::Happy;
        }
        if ($differenceWithTheAverageAbormalitiesCount < 15) {
            return self::Neutral;
        }
        return self::Sad;
    }

    public function getSortWeight(): int
    {
        return match ($this) {
            self::Happy => 4,
            self::Neutral => 3,
            self::Sad => 2,
            self::Default => 1,
        };
    }

    /**
     * Returns whether two abnormalitiesStatusses are equal
     * @param AbnormalitiesStatus $abnormalitiesStatus
     * @return bool
     */
    public function equals(AbnormalitiesStatus $abnormalitiesStatus): bool
    {
        return $this->value === $abnormalitiesStatus->value;
    }

    private static function getPercentualDifferenceComparedToTheAverageAmountOfAbnormalities(int $testParticipantAbnormalities, int $averageAbnormalitiesAmount): int|float
    {
        return (100 * $testParticipantAbnormalities / $averageAbnormalitiesAmount) - 100;
    }
}
