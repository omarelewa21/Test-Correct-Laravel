<?php

namespace tcCore\Http\Livewire\Teacher;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Http\Traits\Modal\WithPlanningFeatures;
use tcCore\Period;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestPlanModal extends ModalComponent
{
    use WithPlanningFeatures;

    public $test;

    public $allowedPeriods;

    public $allowedInvigilators = [];

    public $request = ['date' => '', 'schoolClasses' => [], 'invigilators' => []];

    public $selectedClassesContainerId;
    public $selectedInvigilatorsContrainerId;

    public function mount($testUuid)
    {
        $this->test = \tcCore\Test::whereUuid($testUuid)->first();

        $this->allowedPeriods = Period::filtered(['current_school_year' => true])->get();
        $this->allowedInvigilators = Teacher::getTeacherUsersForSchoolLocation(Auth::user()->schoolLocation)
            ->get()
            ->map(fn ($teacher) => ['value' => $teacher->id, 'label' => $teacher->name_full]);
        $this->resetModalRequest();
    }

    protected function rules()
    {
        $user = auth()->user();
        $rules = [
            'request.date'            => 'required',
            'request.time_end'        => 'sometimes',
            'request.weight'          => 'required',
            'request.period_id'       => 'required',
            'request.school_classes'  => 'required',
            'request.notify_students' => 'required|boolean',
        ];

        if ($this->isAssessmentType()) {
            $rules['request.time_end'] = 'required';
        }

        if ($user->schoollocation->allow_guest_accounts) {
            $rules['request.school_classes'] = 'sometimes';
            if (!empty(request()->get('request.guest_accounts'))) {
                $rules['request.guest_accounts'] = 'required|in:1';
            }
        }

        if($user->isValidExamCoordinator(false) && empty($this->request['owner_id'])){
            $rules['request.owner_id'] = 'required';
        }

        return $rules;
    }


    public function plan()
    {
        $this->planTest();

        $controller = new TemporaryLoginController();
        $request = new Request();

        $action = $this->isAssessmentType() ? "Navigation.load('/test_takes/assessment_open_teacher')" : "Navigation.load('/test_takes/planned_teacher')";

        $request->merge([
            'options' => [
                'page'        => '/',
                'page_action' => $action,
            ]
        ]);

        redirect($controller->toCakeUrl($request));
    }


    private function planTest()
    {
        $t = new TestTake();
        $this->request['time_start'] = $this->request['date'];
        $this->request['test_take_status_id'] = TestTakeStatus::STATUS_PLANNED;

        $this->withValidator(function (Validator $validator) {
            $validator->after(function ($validator) {
                if (empty($this->request['school_classes']) && empty($this->request['guest_accounts'])) {
                    $validator->errors()->add('request.school_classes', __('validation.school_class_or_guest_accounts_required'));
                }
            });
        })->validate();

        if ($this->isAssessmentType() && array_key_exists('time_end', $this->request) && $this->request['time_end']) {
            $this->request['time_end'] = Carbon::parse($this->request['time_end'])->endOfDay();
        }

        $t->fill($this->request);
        if ($this->isAssessmentType()) {
            $t->setAttribute('test_take_status_id', TestTakeStatus::STATUS_TAKING_TEST);
        }

        if(auth()->user()->isValidExamCoordinator(false)){
            $t->setAttribute('user_id', $this->request['owner_id']);
        }else{
            $t->setAttribute('user_id', auth()->id());
        }

        $t->save();

        return $t;
    }

    public function planNext()
    {
        $testTake = $this->planTest();

        $this->closeModal();

        $this->afterPlanningToast($testTake);
    }

    private function afterPlanningToast(TestTake $testTake){
        $this->dispatchBrowserEvent('after-planning-toast',
        [
            'message'       => __($testTake->isAssessmentType() ? 'teacher.test_take_assignment_planned' : 'teacher.test_take_planned', ['testName' => $testTake->test->name]),
            'link'          => $testTake->directLink,
            'takeUuid'      => $testTake->uuid,
            'is_assessment' => $testTake->isAssessmentType()
        ]);
    }

    public function render()
    {
        return view('livewire.teacher.test-plan-modal');
    }

    private function resetModalRequest()
    {
        $this->selectedClassesContainerId = 'selected_classes' . $this->test->getKey();
        $this->selectedInvigilatorsContrainerId = 'selected_invigilator' . $this->test->getKey();

        $this->request = [];

        $this->request['visible'] = 1;
        $this->request['date'] = now()->format('Y-m-d');
        if ($this->isAssessmentType()) {
            $this->request['time_end'] = now()->endOfDay();
        }
        $this->request['period_id'] = $this->allowedPeriods->first()->getKey();
        $this->request['invigilators'] = [auth()->id()];
        $this->request['weight'] = 5;
        $this->request['test_id'] = $this->test->getKey();
        $this->request['allow_inbrowser_testing'] = $this->isAssessmentType() ? 1 : 0;
        $this->request['invigilator_note'] = '';
        $this->request['scheduled_by'] = auth()->id();
        $this->request['test_kind_id'] = 3;

        $this->request['retake'] = 0;
        $this->request['guest_accounts'] = 0;
        $this->request['school_classes'] = [];
        $this->request['notify_students'] = true;

        $this->request['invigilators'] = [$this->defaultInvigilator()];
        $this->request['owner_id'] = $this->defaultInvigilator();
    }

    private function defaultInvigilator(): int
    {
        if($this->authorOfTestIsAnAllowedInvigilator()) {
            return Auth::user()->isValidExamCoordinator() ? $this->test->author_id : Auth::id();
        }
        
        return $this->allowedInvigilators->first()['value'];
    }

    /**
     * @return bool
     */
    private function authorOfTestIsAnAllowedInvigilator(): bool
    {
        return $this->allowedInvigilators->contains(fn($user) => $user['value'] === $this->test->author_id);
    }
}
