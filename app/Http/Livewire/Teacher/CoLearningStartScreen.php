<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\TestTake;

class CoLearningStartScreen extends Component
{

    public int|TestTake $testTake;

    public function mount(TestTake $test_take)
    {
        $this->testTake = $test_take;


    }

    public function goToNewCoLearning() {
        return redirect()->route('teacher.co-learning', ['test_take' => $this->testTake->uuid]);
    }

    public function goToOldCoLearning() {
        //todo implement to prevent breaking testing?

        //how?  cake triggers javascript in the modal/popup to change properties and redirect

        //PUT / UPDATE:
        //$test_take['test_take_status_id'] = 7;
        //$test_take['discussion_type'] = $type;

        $response = $this->testTake->update([
            'test_take_status_id' => 7,
            'discussion_type' => 'ALL'
        ]);

        if($response) {
            return CakeRedirectHelper::redirectToCake('test_takes.discussion', $this->testTake->uuid);
        }
        return false;
    }

    public function redirectBack()
    {
        return TestTake::redirectToDetail(
            testTakeUuid: $this->testTake->uuid,
            returnRoute: Str::replaceFirst(config('app.base_url'), '', Livewire::originalUrl()),
        );
    }

    public function render()
    {
        return view('livewire.teacher.co-learning-start-screen')
            ->layout('layouts.co-learning-teacher');

    }
}
