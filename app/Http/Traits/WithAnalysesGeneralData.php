<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use tcCore\Http\Helpers\AnalysesGeneralDataHelper;
use tcCore\Http\Livewire\Student\Analyses\AnalysesSubjectDashboard;

trait WithAnalysesGeneralData
{
    public $generalStats = [];

    public function mountWithAnalysesGeneralData()
    {
        $this->setGeneralStats();
    }

    private function setGeneralStats()
    {
        $analysesHelper = new AnalysesGeneralDataHelper(Auth::user());

        $entity = ($this instanceof AnalysesSubjectDashboard) ? 'subject' : 'attainment';
        $method = 'getAllFor'.Str::ucfirst($entity);

        $this->generalStats = (array)$analysesHelper->$method($this->$entity, $this->filters);
    }

    public function updatedWithAnalysesGeneralData($name, $value)
    {
        if (Str::startsWith($name, 'filters.')) {
            $this->setGeneralStats();
        }
    }
}