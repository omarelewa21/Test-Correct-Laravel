<?php

namespace tcCore\Http\Livewire;

use tcCore\LearningGoal;

class LearningGoalManager extends AttainmentManager
{

    public $type = 'learning_goals';

    public function mount()
    {
        $filter = [
            'education_level_id' => $this->eductionLevelId,
            'subject_id'         => $this->subjectId,
            'status'             => 'ACTIVE',
        ];
        $this->domains = [];

        LearningGoal::filtered($filter)
            ->whereNull('attainment_id')
            ->get()
            ->each(function ($domain) {
                    $this->domains[$domain->id] = sprintf("[%s] %s", $domain->code, $domain->description);
            });
        if ($this->value){
            LearningGoal::whereIn('id',$this->value)->each(function($attainment){
                if ($attainment->attainment_id == null) {
                    $this->domainId = $attainment->id;
                } elseif(!is_null($attainment->subcode)&&is_null($attainment->subsubcode)) {
                    $this->subdomainId = $attainment->id;
                } elseif(!is_null($attainment->subcode)&&!is_null($attainment->subsubcode)) {
                    $this->subsubdomainId = $attainment->id;
                }
            });
            $this->reloadSubdomainsListForAttainmentId($this->domainId);
            $this->reloadSubsubdomainsListForAttainmentId($this->subdomainId);
        } else {
            $this->updatedDomainId($this->domainId);
            $this->updatedSubDomainId($this->subdomainId);
        }
    }

    protected function reloadDescendantForAttainmentId($attainmentId,$level)
    {
        if (!empty($attainmentId)) {
            LearningGoal::where('attainment_id', $attainmentId)
                ->where('status', 'ACTIVE')
                ->get()
                ->each(function ($child) use ($level){
                    $description = $child->description;
                    if($level == 'subdomains' && $child->description == ''){
                        $description = __('Dit niveau is niet beschikbaar, klik voor het volgende niveau');
                    }
                    $this->$level[$child->id] = $description;
                });;
        }
    }

    protected function emitUpdatedValuesEvent()
    {
        $this->emitUp('updated-learning-goal', ['learning_goals' => array_filter([$this->domainId, $this->subdomainId, $this->subsubdomainId])]);
    }

    public function title()
    {
        return __('cms.Leerdoelen');
    }
}
