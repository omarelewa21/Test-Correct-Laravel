<?php

namespace tcCore\Http\Traits\Modal;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\SchoolClass;
use tcCore\UserFeatureSetting;

trait WithPlanningFeatures
{
    public $rttiExportAllowed = false;

    public function mountWithPlanningFeatures()
    {
        $this->rttiExportAllowed = $this->isRttiExportAllowed();
    }

    public function isRttiExportAllowed(): bool
    {
        return !! Auth::user()->schoolLocation->is_rtti_school_location;
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
    }
}