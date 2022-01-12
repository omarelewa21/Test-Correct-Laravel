<?php

namespace tcCore\Http\Livewire;

use Livewire\Component;
use tcCore\Attainment;

class AttainmentManager extends Component
{
    public $subdomainId;

    public $domains = [];

    public $subdomains = [];

    public $domainId;

    public $value;

    public $subjectId;

    public $eductionLevelId;

    public function mount()
    {
        $filter = [
            'education_level_id' => $this->eductionLevelId,
            'subject_id'         => $this->subjectId,
            'status'             => 'ACTIVE',
        ];
        $this->domains = [];

        Attainment::filtered($filter)
            ->whereNull('attainment_id')
            ->get()
            ->each(function ($domain) {
                    $this->domains[$domain->id] = $domain->description;
            });
        if ($this->value){
            Attainment::whereIn('id',$this->value)->each(function($attainment){
                if ($attainment->attainment_id == null) {
                    $this->domainId = $attainment->id;
                } else {
                    $this->subdomainId = $attainment->id;
                }
            });
            $this->reloadSubdomainsListForAttainmentId($this->domainId);
        } else {
            $this->updatedDomainId($this->domainId);
        }
    }

    public function updatedSubdomainId($value)
    {
        $this->emitUpdatedValuesEvent();
    }

    private function emitUpdatedValuesEvent()
    {
            $this->emitUp('updated-attainment', array_filter([$this->domainId, $this->subdomainId]));
    }

    public function updatedDomainId($value)
    {
        $this->subdomainId = '';
        $this->subdomains = [];
        $this->reloadSubdomainsListForAttainmentId($value);
        $this->emitUpdatedValuesEvent();
    }

    private function reloadSubdomainsListForAttainmentId($attainmentId)
    {
        if (!empty($attainmentId)) {
            Attainment::where('attainment_id', $attainmentId)
                ->where('status', 'ACTIVE')
                ->get()
                ->each(function ($subDomain) {
                    $this->subdomains[$subDomain->id] = $subDomain->description;
                });;
        }
    }

    public function render()
    {
        return view('livewire.attainment-manager')->layout('layouts.base');
    }
}
