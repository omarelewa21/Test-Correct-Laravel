<?php

namespace tcCore\Http\Enums\CoLearning;

enum AbnormalitiesStatus: string
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
        $percentualDifferenceComparedToTheAverageAbnormalitiesAmount = null;

        if ($averageAbnormalitiesAmount != 0) {
            $percentualDifferenceComparedToTheAverageAbnormalitiesAmount = (100 * $testParticipantAbnormalities / $averageAbnormalitiesAmount ) - 100;
        }
        if ($percentualDifferenceComparedToTheAverageAbnormalitiesAmount < -5) {
            return self::Happy;
        }
        if ($percentualDifferenceComparedToTheAverageAbnormalitiesAmount < 15) {
            return self::Neutral;
        }
        return self::Sad;
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
}
