<?php

namespace tcCore\Http\Livewire;

use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Helpers\MagisterHelper;
use tcCore\Http\Helpers\SomeTodayHelper;
use tcCore\UwlrSoapEntry;

class UwlrFetcher extends Component
{
    public $clientCode = 'OV';

    public $clientName = 'Overig';

    public $schoolYear = '2019-2020';

    public $brinCode = '06SS';

    public $dependanceCode = '00';

    public $resultIdendifier = null;

    public $currentSource = 0;

    public $uwlrDatasource = [ ];

    private $report;

    public function mount()
    {
        $this->uwlrDatasource = UwlrSoapEntry::DATASOURCES;
        $this->setSearchFields();
    }

    public function updatedCurrentSource()
    {
        $this->setSearchFields();
    }


    private function setSearchFields()
    {
        $this->clientCode = $this->uwlrDatasource[$this->currentSource]['client_code'];
        $this->clientName = $this->uwlrDatasource[$this->currentSource]['client_name'];
        $this->schoolYear = $this->uwlrDatasource[$this->currentSource]['school_year'];
        $this->brinCode = $this->uwlrDatasource[$this->currentSource]['brin_code'];
        $this->dependanceCode = $this->uwlrDatasource[$this->currentSource]['dependance_code'];
    }

    public function fetch()
    {
        $helper = $this->getHelper();

        $this->report = $helper->getResultSet()->report();
        $this->resultIdendifier = $helper->getResultIdentifier();
    }

    public function getHelper()
    {
        $helper = null;
        switch ($this->currentSource) {
            case 0:
                $helper = MagisterHelper::guzzle($this->schoolYear,$this->brinCode, $this->dependanceCode)->parseResult()->storeInDB($this->brinCode, $this->dependanceCode);
                break;
            case 1:
                $helper = (new SomeTodayHelper(new SoapWrapper()))->search(
                    $this->clientCode,
                    $this->clientName,
                    $this->schoolYear,
                    $this->brinCode,
                    $this->dependanceCode
                )->storeInDB();
                break;
        }
        return $helper;
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
        $rep = $this->report ? $this->report : [];
        return view('livewire.uwlr-fetcher')->with(['report' => $rep])->layout('layouts.app-admin');
    }
}
