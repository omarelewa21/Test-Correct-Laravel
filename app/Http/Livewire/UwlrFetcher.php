<?php

namespace tcCore\Http\Livewire;

use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Helpers\MagisterHelper;
use tcCore\Http\Helpers\SomTodayHelper;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationSchoolYear;
use tcCore\SchoolYear;
use tcCore\UwlrSoapEntry;

class UwlrFetcher extends Component
{
    public $clientCode = 'OV';

    public $clientName = 'Overig';

    public $schoolYears = [];

    public $schoolYear = '';

    public $brinCode = '06SS';

    public $dependanceCode = '00';

    public $resultIdendifier = null;

    public $currentSource = 0;

    public $uwlrDatasource = [ ];

    private $report;

    public function mount()
    {
        $this->uwlrDatasource = $this->getDataSource();
        $this->setSearchFields();
        $this->setSchoolYears();
    }

    protected function getDataSource()
    {
        return SchoolLocation::whereNotNull('lvs_type')->get()->map(function(SchoolLocation $l){ // where('lvs_active',true)-> not needed as we do it by hand. And if by hand then not needed to be active only by auto retrieval
           return [
               'id'             => $l->getKey(),
               'name'            => $l->name,
               'client_code'     => $l->lvs_client_code,
               'client_name'     => $l->lvs_client_name,
               'brin_code'       => $l->external_main_code,
               'dependance_code' => $l->external_sub_code,
               'lvs_type'       => $l->lvs_type,
               'school_year'    => ''
           ];
        });
    }

    public function updatedCurrentSource()
    {
        $this->setSearchFields();
        $this->setSchoolYears();

    }

    protected function setSchoolYears()
    {
        $this->schoolYears = [];
        $location = SchoolLocation::find($this->uwlrDatasource[$this->currentSource]['id']);
        if($location) {
            $years = $location->schoolLocationSchoolYears->map(function(SchoolLocationSchoolYear $slsy){
               return sprintf('%d-%d', $slsy->schoolYear->year, $slsy->schoolYear->year + 1);
            });
            $this->schoolYears = collect($years)->sortDesc();
            $this->schoolYear = $this->schoolYears->first();
        }

    }

    public function updatedSchoolYear($data)
    {
        $this->setSearchFields();
    }

    private function setSearchFields()
    {
        $this->clientCode = $this->uwlrDatasource[$this->currentSource]['client_code'];
        $this->clientName = $this->uwlrDatasource[$this->currentSource]['client_name'];
        $this->schoolYear = $this->schoolYear;
        $this->brinCode = $this->uwlrDatasource[$this->currentSource]['brin_code'];
        $this->dependanceCode = $this->uwlrDatasource[$this->currentSource]['dependance_code'];
    }

    public function fetch()
    {
        try {
            $helper = $this->getHelper();

            $this->report = $helper->getResultSet()->report();
            $this->resultIdendifier = $helper->getResultIdentifier();
        } catch(\Exception $e){
            session()->flash('error', $e->getMessage());
            return $this->redirect(route('uwlr.grid'));
        }
    }

    public function getHelper()
    {
        $helper = null;
        switch ($this->uwlrDatasource[$this->currentSource]['lvs_type']) {
            case SchoolLocation::LVS_MAGISTER:
                $helper = MagisterHelper::guzzle($this->schoolYear,$this->brinCode, $this->dependanceCode)->parseResult()->storeInDB($this->brinCode, $this->dependanceCode);
                break;
            case SchoolLocation::LVS_SOMTODAY:
                $helper = (new SomTodayHelper(new SoapWrapper()))->search(
                    $this->clientCode,
                    $this->clientName,
                    $this->schoolYear,
                    $this->brinCode,
                    $this->dependanceCode
                )->storeInDB();
                break;
            default:
                throw new \Exception(sprintf('No valid lvs_type (%s)',$this->uwlrDatasource[$this->currentSource]['lvs_type']));
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
