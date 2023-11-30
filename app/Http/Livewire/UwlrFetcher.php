<?php

namespace tcCore\Http\Livewire;

use tcCore\Http\Helpers\UwlrImportHelper;
use tcCore\SchoolLocation;

class UwlrFetcher extends TCComponent
{

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
        if (count($this->uwlrDatasource) == 0) {
            dd('Geen schoollocaties gevonden met een gevuld veld lvs_type [ben je vergeten om deze in de database te zetten?]');
        }
        $this->setSearchFields();
        $this->setSchoolYears();
    }

    protected function getDataSource()
    {
        return SchoolLocation::whereNotNull('lvs_type')->whereNull('import_merge_school_location_id')->get()->map(function(SchoolLocation $l){ // where('lvs_active',true)-> not needed as we do it by hand. And if by hand then not needed to be active only by auto retrieval
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
        })->toArray();
    }

    public function updatedCurrentSource()
    {
        $this->setSearchFields();
        $this->setSchoolYears();

        if($this->uwlrDatasource[$this->currentSource]['school_year']) {
            $this->schoolYear = $this->uwlrDatasource[$this->currentSource]['school_year'];
        }
    }

    protected function setSchoolYears()
    {
        $this->schoolYears = [];
        $location = SchoolLocation::find($this->uwlrDatasource[$this->currentSource]['id']);
        if($location) {
            $this->schoolYears = UwlrImportHelper::getSchoolYearsForUwlrImport($location);

            if (!array_key_exists(0, $this->schoolYears)) {
                $this->addError(
                    'no_school_years',
                    sprintf('Geen schooljaren aanwezig in schoollocatie met id: %d en naam: %s', $location->id, $location->name)
                );
                $this->schoolYear = '';
//                dd(sprintf('Geen schooljaren aanwezig in schoollocatie met id: %d en naam: %s', $location->id, $location->name));
            } else {
                $this->schoolYear = $this->schoolYears[0];
            }
        }

    }

    public function updatedSchoolYear($data)
    {
        $this->setSearchFields();
        $this->uwlrDatasource[$this->currentSource]['school_year'] = $data;
    }

    private function setSearchFields()
    {
//        $this->clientCode = $this->uwlrDatasource[$this->currentSource]['client_code'];
//        $this->clientName = $this->uwlrDatasource[$this->currentSource]['client_name'];
//        $this->schoolYear = $this->schoolYear;
        $this->brinCode = $this->uwlrDatasource[$this->currentSource]['brin_code'];
        $this->dependanceCode = $this->uwlrDatasource[$this->currentSource]['dependance_code'];
    }

    public function fetch()
    {

        try {
            set_time_limit(0);
            $helper = UwlrImportHelper::getHelperAndStoreInDB($this->uwlrDatasource[$this->currentSource]['lvs_type'], $this->schoolYear, $this->brinCode, $this->dependanceCode);

            $this->resultIdendifier = $helper->getResultIdentifier();

            $parentSchoolLocation = SchoolLocation::where('external_main_code',$this->brinCode)->where('external_sub_code',$this->dependanceCode)->first();

            (new UwlrImportHelper)->handleSchoolLocationsToBeMergedIfAny($parentSchoolLocation,$this->resultIdendifier,$this->schoolYear);

            $this->report = $helper->getResultSet(true)->report();

        } catch(\Exception $e){
            dd($e);
            session()->flash('error', $e->getMessage());
            return $this->redirect(route('uwlr.grid'));
        }
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
        $rep = $this->report ?: [];
        return view('livewire.uwlr-fetcher')->with(['report' => $rep])->layout('layouts.app-admin');
    }
}
