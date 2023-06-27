<?php

namespace tcCore\Http\Traits\Modal;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\SchoolClass;
use tcCore\Teacher;
use tcCore\UserFeatureSetting;

trait WithPlanningFeatures
{
    public $rttiExportAllowed = false;

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
                'testTake.allow_wsc'               => 'sometimes|required|boolean',
                'testTake.show_grades'             => 'sometimes|boolean',
                'testTake.show_correction_model'   => 'sometimes|boolean',
                'testTake.time_start'              => 'sometimes|date',
                'testTake.time_end'                => 'sometimes|nullable|date',
            ];
    }

    private function getConditionalRules(): array
    {
        $conditionalRules = [];
        if (!$this->testTake->guest_accounts) {
            $conditionalRules['selectedClasses'] = 'required';
        }
        if ($this->rttiExportAllowed) {
            $conditionalRules['testTake.is_rtti_test_take'] = 'required';
        }
        return $conditionalRules;
    }

    public function isRttiExportAllowed(): bool
    {
        return !!Auth::user()->schoolLocation->is_rtti_school_location;
    }

    public function getSchoolClassesProperty()
    {
        $filters = $this->getFiltersForSchoolClasses();
        return SchoolClass::filtered($filters)->optionList();
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
}