<?php

namespace tcCore\Http\Livewire;

use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Helpers\SomeTodayHelper;

class UwlrFetcher extends Component
{
    public $clientCode = 'OV';

    public $clientName = 'Overig';

    public $schoolYear = '2019-2020';

    public $brinCode = '06SS';

    public $dependanceCode = '00';

    public $resultIdendifier = null;

    private $report;

    public function mount()
    {
        Auth::loginUsingId(755);
    }

    public function fetch()
    {
        $helper = (new SomeTodayHelper(new SoapWrapper()))->search(
            $this->clientCode,
            $this->clientName,
            $this->schoolYear,
            $this->brinCode,
            $this->dependanceCode
        )->storeInDB();

        $this->report = $helper->getResultSet()->report();
        $this->resultIdendifier = $helper->getResultIdentifier();
    }

    public function showGrid()
    {
        return $this->redirect(route('uwlr.grid'));
    }

    public function showGridWithModal()
    {
        return $this->redirect(
            route('uwlr.grid', ['modal' => $this->resultIdendifier])
        );
    }

    public function render()
    {
        $rep = $this->report? $this->report : [];
        return view('livewire.uwlr-fetcher')->with(['report' => $rep])->layout('layouts.app-admin');
    }
}
