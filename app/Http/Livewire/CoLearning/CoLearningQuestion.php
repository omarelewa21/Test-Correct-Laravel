<?php

namespace tcCore\Http\Livewire\CoLearning;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

abstract class CoLearningQuestion extends Component
{
    public $answer = null;
    public $answered;
    public $question;
    public $questionNumber;
    public $answerNumber;

    public $answerRatingId;
    protected $answerRating;

    public $originalUrl;

    protected $listeners = [
        'getNextAnswerRating' => 'initializeComponent',
    ];

    public function mount()
    {
        $this->originalUrl = \Livewire::originalUrl();

        $this->initializeComponent();
    }

    public function initializeComponent($data = null)
    {
        if (isset($data)) {
            $this->answerRatingId = $data[0];
            $this->questionNumber = $data[1];
            $this->answerNumber = $data[2];
        }

        $this->answerRating = AnswerRating::find($this->answerRatingId);

        $this->question = $this->answerRating->answer->question;

        $this->redirectByWrongQuestionType();

        $this->answered = $this->answerRating->answer->isAnswered;

        $this->handleGetAnswerData();

        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function redirectByWrongQuestionType()
    {
        //16-12-22 if no errors/Exceptions have happened here after half a year, remove the code.
        if (!Str::endsWith(get_class($this), $this->question->type)) {
            $tries = request()->all()['tries'] ?? 1;

            if ($tries < 3) {
                $tries++;
                sleep(1);
                header(sprintf('location: %s?tries=%s', $this->originalUrl, $tries));
                exit;
            }

            Bugsnag::leaveBreadcrumb('answerRating', Breadcrumb::MANUAL_TYPE, ['answerRating' => $this->answerRating->id]);

            Bugsnag::notifyException(new \Exception('CO-Learning [student]: Question component instantiated with wrong question type.'));

            header(sprintf('location: /student/test-takes?tab=discuss'));
            exit;
        }

    }

    abstract public function render();

    abstract public function isQuestionFullyAnswered(): bool;

    abstract protected function handleGetAnswerData();

}