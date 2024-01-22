<?php


namespace tcCore\Http\Traits;

use tcCore\TestTakeStatus;


trait TestTakeStatusesTrait
{
    public function hasStatusPlanned(): bool
    {
        return $this->test_take_status_id === TestTakeStatus::STATUS_PLANNED;
    }

    public function hasStatusTestNotTaken(): bool
    {
        return $this->test_take_status_id === TestTakeStatus::STATUS_TEST_NOT_TAKEN;
    }

    public function hasStatusTakingTest(): bool
    {
        return $this->test_take_status_id === TestTakeStatus::STATUS_TAKING_TEST;
    }

    public function hasStatusHandedIn(): bool
    {
        return $this->test_take_status_id === TestTakeStatus::STATUS_HANDED_IN;
    }

    public function hasStatusTakenAway(): bool
    {
        return $this->test_take_status_id === TestTakeStatus::STATUS_TAKEN_AWAY;
    }

    public function hasStatusTaken(): bool
    {
        return $this->test_take_status_id === TestTakeStatus::STATUS_TAKEN;
    }

    public function hasStatusDiscussing(): bool
    {
        return $this->test_take_status_id === TestTakeStatus::STATUS_DISCUSSING;
    }

    public function hasStatusDiscussed(): bool
    {
        return $this->test_take_status_id === TestTakeStatus::STATUS_DISCUSSED;
    }

    public function hasStatusRated(): bool
    {
        return $this->test_take_status_id === TestTakeStatus::STATUS_RATED;
    }

    public function hasNotFinishedTakingTest(): bool
    {
        return $this->test_take_status_id <= TestTakeStatus::STATUS_TAKING_TEST;
    }

    public function hasFinishedTakingTest(): bool
    {
        return $this->test_take_status_id > TestTakeStatus::STATUS_TAKING_TEST;
    }
}