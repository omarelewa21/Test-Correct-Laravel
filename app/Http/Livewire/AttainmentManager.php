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


    public function mount()
    {
        $filter = [
            'education_level_id' => 1,
            'subject_id'         => 2,
            'status'             => 'ACTIVE',
        ];
        $this->domains = [];

        Attainment::filtered($filter)
            ->whereNull('attainment_id')
            ->get()
            ->each(function ($domain) {
                    $this->domains[$domain->id] = $domain->description;
            });
        $this->updatedDomainId($this->domainId);
    }

    public function updatedSubdomainId($value) {
        $this->emitUp('updated-attainment', $value);
    }

    public function updatedDomainId($value)
    {
        $this->subdomainId = '';
        $this->subdomains = [];

        if (!empty($value)) {
            Attainment::where('attainment_id', $value)
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
