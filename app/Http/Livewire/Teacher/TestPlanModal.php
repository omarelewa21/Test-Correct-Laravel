<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Http\Traits\Modal\WithPlanningFeatures;
use tcCore\Period;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class TestPlanModal extends TCModalComponent
{
    use WithPlanningFeatures;

    public $test;

    public $allowedPeriods;

    public $allowedInvigilators = [];
    public $allowedTeachers = [];

    public $request = ['date' => '', 'schoolClasses' => [], 'invigilators' => [], 'is_rtti_test_take' => false];

    public $selectedClassesContainerId;
    public $selectedInvigilatorsContrainerId;

    public $clickDisabled = false;

    public array $labels;

    public bool $allowedToEnableMrChadd;

    public function mount($testUuid)
    {
        $this->test = Test::whereUuid($testUuid)->first();

        $this->allowedPeriods = Period::filtered(['current_school_year' => true])->get();
        $this->allowedInvigilators = $this->getAllowedInvigilators();
        $this->allowedTeachers = $this->getAllowedTeachers();
        $this->resetModalRequest();
        $this->setLabels();

        $this->allowedToEnableMrChadd = (auth()->user()->schoolLocation->allow_mr_chadd && $this->test->isAssignment());
    }

    protected function rules()
    {
        $user = auth()->user();
        $rules = [
            'request.date'                  => 'required',
            'request.time_end'              => 'sometimes',
            'request.weight'                => 'required',
            'request.period_id'             => 'required',
//            'request.school_classes'        => 'required',
            'request.notify_students'       => 'required|boolean',
            'request.invigilators'          => 'required|min:1|array',
            'request.show_grades'           => 'sometimes|boolean',
            'request.show_correction_model' => 'sometimes|boolean',
        ];

        if ($this->isAssignmentType()) {
            $rules['request.time_end'] = 'required';
        }

//        if ($user->schoollocation->allow_guest_accounts) {
//            $rules['request.school_classes'] = 'sometimes';
//            if (!empty(request()->get('request.guest_accounts'))) {
//                $rules['request.guest_accounts'] = 'required|in:1';
//            }
//        }

        if ($user->isValidExamCoordinator() && empty($this->request['owner_id'])) {
            $rules['request.owner_id'] = 'required';
        }

        return $rules;
    }

    protected function getMessages()
    {
        return [
            'request.invigilators.required'   => __('validation.invigilator_required'),
            'request.school_classes.required' => __('validation.school_class_or_guest_accounts_required')
        ];
    }

    public function updatingRequestDate($value) {}

    public function plan()
    {
        $this->planTest();

        $controller = new TemporaryLoginController();
        $request = new Request();

        $action = $this->isAssignmentType(
        ) ? "Navigation.load('/test_takes/assignment_open_teacher')" : "Navigation.load('/test_takes/planned_teacher')";

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
        $testTake = new TestTake();
        $this->request['time_start'] = $this->request['date'];
        $this->request['test_take_status_id'] = TestTakeStatus::STATUS_PLANNED;

        $this->withValidator(function (Validator $validator) {
            $validator->after(function ($validator) {
                if (empty($this->classesAndStudents['children']) && empty($this->request['guest_accounts'])) {
                    $validator->errors()->add(
                        'request.school_classes',
                        __('validation.school_class_or_guest_accounts_required')
                    );
                }
            });
        })->validate();

        $this->clickDisabled = true;

        if ($this->isAssignmentType() && array_key_exists('time_end', $this->request) && $this->request['time_end']) {
            $this->request['time_end'] = Carbon::parse($this->request['time_end'])->endOfDay();
        }

        unset($this->request['school_classes']);

        $testTake->fill($this->request);
        if ($this->isAssignmentType()) {
            $testTake->setAttribute('test_take_status_id', TestTakeStatus::STATUS_TAKING_TEST);
        }

        $testTakeOwner = Auth::user()->isValidExamCoordinator() ? $this->request['owner_id'] : Auth::id();
        $testTake->setAttribute('user_id', $testTakeOwner);

        $testTake->save();
        $this->handleParticipants($testTake);

        return $testTake;
    }

    public function planNext()
    {
        $testTake = $this->planTest();

        $this->closeModal();

        $this->afterPlanningToast($testTake);
    }

    private function afterPlanningToast(TestTake $testTake)
    {
        $this->dispatchBrowserEvent(
            'after-planning-toast',
            [
                'message'       => __(
                    $testTake->isAssignmentType(
                    ) ? 'teacher.test_take_assignment_planned' : 'teacher.test_take_planned',
                    ['testName' => $testTake->test->name]
                ),
                'link'          => $testTake->directLink,
                'takeUuid'      => $testTake->uuid,
                'is_assignment' => $testTake->isAssignmentType()
            ]
        );
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
        if ($this->isAssignmentType()) {
            $this->request['time_end'] = now()->endOfDay();
        }
        $this->request['period_id'] = $this->allowedPeriods->first()->getKey();


        $this->request['test_id'] = $this->test->getKey();

        $this->request['invigilator_note'] = '';
        $this->request['scheduled_by'] = auth()->id();
        $this->request['test_kind_id'] = 3;

        $this->request['retake'] = 0;

        $this->request['school_classes'] = [];

        $this->request['invigilators'] = [$this->defaultInvigilator()];
        $this->request['owner_id'] = $this->defaultOwner();
        $this->setFeatureSettingDefaults($this->request);
    }

    private function defaultInvigilator(): int
    {
        return Auth::user()->isValidExamCoordinator() ? $this->defaultOwner() : Auth::id();
    }

    private function defaultOwner(): int
    {
        if (!Auth::user()->isValidExamCoordinator()) {
            return Auth::id();
        }
        if ($this->authorOfTestIsAnAllowedInvigilator()) {
            return $this->test->author_id;
        }

        return $this->allowedTeachers->sortBy('label')->first()['value'];
    }

    /**
     * @return bool
     */
    private function authorOfTestIsAnAllowedInvigilator(): bool
    {
        return $this->allowedInvigilators->contains(fn($user) => $user['value'] === $this->test->author_id);
    }

    protected function setLabels(): void
    {
        $this->labels = [
            'title' => __('teacher.Toets inplannen'),
            'date'  => __('teacher.Datum'),
            'cta'   => __('teacher.Inplannen')
        ];
    }
}
