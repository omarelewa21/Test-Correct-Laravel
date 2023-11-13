<?php

namespace tcCore\View\Components\Partials\Sidebar\CoLearningTeacher;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use tcCore\AnswerRating;
use tcCore\Http\Enums\CoLearning\AbnormalitiesStatus;
use tcCore\Http\Enums\CoLearning\RatingStatus;
use tcCore\TestParticipant;

class StudentInfoContainer extends Component
{
    public readonly string $userFullName;
    public readonly bool $smartboardButtonActive;
    public readonly bool $smartboardButtonDisabled;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public TestParticipant $testParticipant,
        public ?AnswerRating   $activeAnswerRating = null,
    ) {
        $this->userFullName = $this->testParticipant->user->nameFull;
        $this->smartboardButtonActive = (!is_null($activeAnswerRating) || !$testParticipant->syncedWithCurrentQuestion)
            && $testParticipant->fresh()->discussing_answer_rating_id === $activeAnswerRating->id;
        $this->smartboardButtonDisabled = is_null($testParticipant?->discussing_answer_rating_id) || !$testParticipant->syncedWithCurrentQuestion;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.partials.sidebar.co-learning-teacher.student-info-container');
    }

}
