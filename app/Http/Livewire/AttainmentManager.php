<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use tcCore\Attainment;

class AttainmentManager extends Component
{
    public $subdomainId;

    public $subsubdomainId;

    public $domains = [];

    public $subdomains = [];

    public $subsubdomains = [];

    public $domainId;

    public $value;

    public $subjectId;

    public $eductionLevelId;

    public $type = 'attainments';

    public function mount()
    {
        $filter = [
            'education_level_id' => $this->eductionLevelId,
            'subject_id'         => $this->subjectId,
            'status'             => 'ACTIVE',
        ];
        $this->domains = [];
        DB::enableQueryLog();
        Attainment::filtered($filter)
            ->whereNull('attainment_id')
            ->get()
            ->each(function ($domain) {
                    $this->domains[$domain->id] = sprintf("[%s] %s", $domain->code, $domain->description);
            });
        dump(DB::getQueryLog());
        if ($this->value){
            Attainment::whereIn('id',$this->value)->each(function($attainment){
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

    public function updatedSubsubdomainId($value)
    {
        $this->emitUpdatedValuesEvent();
    }

    protected function emitUpdatedValuesEvent()
    {
            $this->emitUp('updated-attainment', array_filter([$this->domainId, $this->subdomainId, $this->subsubdomainId]));
    }

    public function updatedDomainId($value)
    {
        $this->subdomainId = '';
        $this->subdomains = [];
        $this->subsubdomainId = '';
        $this->subsubdomains = [];
        $this->reloadSubdomainsListForAttainmentId($value);
        $this->emitUpdatedValuesEvent();
    }

    public function updatedSubdomainId($value)
    {
        $this->subsubdomainId = '';
        $this->subsubdomains = [];
        $this->reloadSubsubdomainsListForAttainmentId($value);
        $this->emitUpdatedValuesEvent();
    }

    protected function reloadSubdomainsListForAttainmentId($attainmentId)
    {
        $this->reloadDescendantForAttainmentId($attainmentId,'subdomains');
    }

    protected function reloadSubsubdomainsListForAttainmentId($attainmentId)
    {
        $this->reloadDescendantForAttainmentId($attainmentId,'subsubdomains');
    }

    protected function reloadDescendantForAttainmentId($attainmentId,$level)
    {
        if (!empty($attainmentId)) {
            Attainment::where('attainment_id', $attainmentId)
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

    public function render()
    {
        return view('livewire.attainment-manager')->layout('layouts.base');
    }

    public function title()
    {
        return __('cms.Eindtermen');
    }
}
