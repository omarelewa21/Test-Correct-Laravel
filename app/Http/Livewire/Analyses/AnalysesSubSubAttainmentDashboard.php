<?php

namespace tcCore\Http\Livewire\Analyses;

use tcCore\BaseAttainment;
use tcCore\Http\Traits\WithAnalysesGeneralData;
use tcCore\Lib\Repositories\TaxonomyRankingRepostitory;
use tcCore\Subject;

class AnalysesSubSubAttainmentDashboard extends AnalysesDashboard
{
    use WithAnalysesGeneralData;

    public $subject;

    protected $queryString = ['subject'];

    public $attainment;

    public $parentAttainment;

    public $parentParentAttainment;

    public $displayRankingPanel = false;

    public function getTopItemsProperty()
    {
        return true;
    }

    public function getDataProperty()
    {
        return true;
    }

    public function mount(?BaseAttainment $baseAttainment = null)
    {
        parent::mount();

        $this->attainment = $baseAttainment;
        $this->taxonomyIdentifier = $this->attainment->id;
        $this->parentAttainment = BaseAttainment::find($this->attainment->attainment_id);
        $this->parentParentAttainment = BaseAttainment::find($this->parentAttainment->attainment_id);
    }

    public function render()
    {
        $this->dispatchBrowserEvent('filters-updated');
        return view('livewire.analyses.analyses-sub-sub-attainment-dashboard')->layout('layouts.student');
    }

    public function getDataForGeneralGraph($subjectId, $taxonomy)
    {
        switch ($taxonomy) {
            case 'Miller':
                $data = $this->getMillerGeneralGraphData($subjectId);
                break;
            case 'RTTI':
                $data = $this->getRTTIGeneralGraphData($subjectId);
                break;
            case 'Bloom':
                $data = $this->getBloomGeneralGraphData($subjectId);
                break;
        }

        return [
            $showEmptyState = collect($data)->filter(fn($item) => $item[1] > 0)->isEmpty(),
            $this->transformForGraph($data)
        ];
    }

    private function transformForGraph($data)
    {
        return collect($data)->map(function ($item) {
            return [
                'x'       => $item[0],
                'value'   => $item[1],
                'tooltip' => trans_choice(
                    'student.tooltip_taxonomy_graph',
                    $item[2], [
                    'count_questions' => $item[2],
                    'p_value'         => number_format($item[1], 2),
                ])
            ];
        });
    }

    public function redirectBack()
    {
        return redirect(
            $this->getHelper()->getRouteForSubAttainmentShow(
                $this->parentAttainment,
                $this->subject
            )
        );
    }
}
