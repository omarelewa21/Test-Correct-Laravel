<?php

namespace tcCore\View\Components\Partials\Sidebar\CoLearningTeacher;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use tcCore\Http\Enums\CoLearning\AbnormalitiesStatus;
use tcCore\Http\Enums\CoLearning\RatingStatus;
use tcCore\TestParticipant;

class StudentInfoContainer extends Component
{
    public readonly string $userFullName;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public TestParticipant $testParticipant,
    )
    {
        $user = $this->testParticipant->user;

        $this->userFullName = Str::replace(
            search: '  ',
            replace: ' ',
            subject: sprintf('%s %s %s', $user->name_first, $user->name_suffix, $user->name)
        );

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
