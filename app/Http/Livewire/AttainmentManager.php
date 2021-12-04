<?php

namespace tcCore\Http\Livewire;

use Livewire\Component;
use tcCore\Attainment;

class AttainmentManager extends Component
{
    public $subdomainId;

    public $domains = [];

    public $subdomains = [];

    public $domainId = 1;


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

    public function showValues(){
        dd([
            $this->subdomainId, $this->domainId,
        ]);
    }

    public function updatedDomainId($value)
    {
        $this->subdomainId = '';
        $this->subdomains = [];

        Attainment::where('attainment_id', $value)
            ->where('status', 'ACTIVE')
            ->get()
            ->each(function ($subDomain) {
                $this->subdomains[$subDomain->id] = $subDomain->description;
            });;
            $this->subdomainId = rand(10, 10);
    }

    public function render()
    {
        return view('livewire.attainment-manager')->layout('layouts.base');
    }
}
