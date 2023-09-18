<?php

namespace tcCore\Http\Traits\Modal;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\Http\Helpers\Choices\ChildChoice;
use tcCore\Http\Helpers\Choices\ParentChoice;
use tcCore\Lib\TestParticipant\Factory as ParticipantFactory;
use tcCore\SchoolClass;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\TestKind;
use tcCore\UserFeatureSetting;

trait WithPlanningFeatures
{
    public $rttiExportAllowed = false;
    public array $classesAndStudents = [
        'parents'  => [],
        'children' => []
    ];

    public function mountWithPlanningFeatures()
    {
        $this->rttiExportAllowed = $this->isRttiExportAllowed();
    }

    protected function rules(): array
    {
        return $this->getConditionalRules() + [
                'testTake.weight'                  => 'required|numeric',
                'testTake.allow_inbrowser_testing' => 'required|boolean',
                'testTake.guest_accounts'          => 'required|boolean',
                'testTake.notify_students'         => 'required|boolean',
                'testTake.show_grades'             => 'sometimes|boolean',
                'testTake.show_correction_model'   => 'sometimes|boolean',
                'testTake.time_start'              => 'sometimes|date',
                'testTake.time_end'                => 'sometimes|nullable|date',
            ];
    }

    private function getConditionalRules(): array
    {
        if ($this->rttiExportAllowed) {
            $conditionalRules['testTake.is_rtti_test_take'] = 'required';
        }
        if($this->testTake->test->test_kind_id === TestKind::ASSIGNMENT_TYPE) {
            $conditionalRules['testTake.enable_mr_chadd'] = 'required';
        }
        return $conditionalRules;
    }

    public function isRttiExportAllowed(): bool
    {
        return !!Auth::user()->schoolLocation->is_rtti_school_location;
    }

    public function getSchoolClassesProperty(): array
    {
        $filters = $this->getFiltersForSchoolClasses();
        $classes = SchoolClass::filtered($filters)->get();
        return $this->buildChoicesArrayWithClasses($classes);
    }

    public function isAssignmentType()
    {
        return $this->test->isAssignment();
    }

    /**
     * @return array
     */
    private function getFiltersForSchoolClasses(): array
    {
        $filters = [
            'user_id' => auth()->id(),
            'current' => true,
        ];
        if (Auth::user()->isValidExamCoordinator()) {
            if (filled($this->test->scope)) {
                $filterAddOn = ['base_subject_id' => $this->test->subject()->value('base_subject_id')];
            } else {
                $filterAddOn = ['subject_id' => $this->test->subject_id];
            }
            $filters = $filters + $filterAddOn;
        }
        return $filters;
    }


    private function setFeatureSettingDefaults(&$plannable): void
    {
        $featureSettings = UserFeatureSettingEnum::initialValues()->merge(UserFeatureSetting::getAll(Auth::user()));

        $plannable['weight'] = $featureSettings[UserFeatureSettingEnum::TEST_TAKE_DEFAULT_WEIGHT->value];
        $plannable['allow_inbrowser_testing'] = $featureSettings[UserFeatureSettingEnum::TEST_TAKE_BROWSER_TESTING->value];
        $plannable['guest_accounts'] = $featureSettings[UserFeatureSettingEnum::TEST_TAKE_TEST_DIRECT->value];
        $plannable['notify_students'] = $featureSettings[UserFeatureSettingEnum::TEST_TAKE_NOTIFY_STUDENTS->value];
        $plannable['show_grades'] = $featureSettings[UserFeatureSettingEnum::REVIEW_SHOW_GRADES->value];
        $plannable['show_correction_model'] = $featureSettings[UserFeatureSettingEnum::REVIEW_SHOW_CORRECTION_MODEL->value];
    }

    protected function getAllowedTeachers()
    {
//        /*TODO: Fix this check for published items */
        if (filled($this->test->scope)) {
            $query = Teacher::getTeacherUsersForSchoolLocationByBaseSubjectInCurrentYear(
                Auth::user()->schoolLocation,
                $this->test->subject()->value('base_subject_id')
            );
        } else {
            $query = Teacher::getTeacherUsersForSchoolLocationBySubjectInCurrentYear(
                Auth::user()->schoolLocation,
                $this->test->subject_id
            );
        }

        return $query->get()->map(fn($teacher) => ['value' => $teacher->id, 'label' => $teacher->name_full]);
    }

    protected function getAllowedInvigilators()
    {
        // invigilators shouldn't be restricted to subject, those users could get to the test anyway
        $query = Teacher::getTeacherUsersForSchoolLocationInCurrentYear(Auth::user()->schoolLocation);
        return $query->get()->map(fn($teacher) => ['value' => $teacher->id, 'label' => $teacher->name_full]);
    }

    protected function buildChoicesArrayWithClasses(
        Collection $classes,
                   $selectedCallback = null,
    ): array {
        return $classes->map(function ($class) use ($selectedCallback) {
            return ParentChoice::build(
                value           : $class->uuid,
                label           : html_entity_decode($class->name),
                customProperties: ['parentId' => $class->uuid],
                children        : $class->studentUsers->map(
                    function ($studentUser) use ($selectedCallback, $class) {
                        $selected = false;
                        if (is_callable($selectedCallback)) {
                            $selected = $selectedCallback($studentUser);
                        }

                        return ChildChoice::build(
                            value           : $studentUser->uuid,
                            label           : html_entity_decode($studentUser->name_full),
                            customProperties: [
                                'parentId'    => $class->uuid,
                                'parentLabel' => html_entity_decode($class->name),
                                'selected'    => $selected,
                            ]
                        );
                    }
                )
            );
        })->toArray();
    }

    protected function handleParticipants(TestTake $testTake): void
    {
        ParticipantFactory::generateForUsers($testTake, $this->classesAndStudents);
        $testTake->dispatchNewTestTakePlannedEvent();
    }
}