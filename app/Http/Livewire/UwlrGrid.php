<?php

namespace tcCore\Http\Livewire;

use Carbon\Carbon;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\ImportHelper;
use tcCore\Jobs\ProcessUwlrSoapResultJob;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationSection;
use tcCore\UwlrSoapEntry;
use tcCore\UwlrSoapResult;

class UwlrGrid extends TCComponent
{
    public $showSuccessDialog = false;
    public $successDialogMessage = '';
    public $showImportModal = false;
    public $showProcessResultModal = false;
    public $resultSets;
    public $activeResult = null;
    public $modalActiveTab = 'school';
    public $processingResult = '';
    public $processingResultErrors = [];
    public $processingResultId;
    public $showErrorModal = false;
    public $showFailureModal = false;
    public $errorMessages = '';
    public $failureMessages = '';
    public $displayGoToErrorsButton = false;

    public function mount()
    {
        $this->resultSets = UwlrSoapResult::orderBy('created_at', 'desc')->get();
    }

    public function activateResult($id)
    {

        $this->activeResult = UwlrSoapResult::find($id)->asData()->toArray();
//        dd($this->activeResult);
        $this->currentResultId = $id;

        $this->showImportModal = true;
    }

    public function triggerErrorModal($id=null)
    {
        $id = $id ?: $this->processingResultId;

        $this->showImportModal = false;
        if ($id) {
            $this->errorMessages = UwlrSoapResult::find($id)->error_messages;
        }

        $this->showErrorModal = true;
    }

    public function triggerFailureModal($id=null)
    {
        $id = $id ?: $this->processingResultId;

        $this->showFailureModal = false;
        if ($id) {
            $this->failureMessages = UwlrSoapResult::find($id)->failure_messages;
        }

        $this->showFailureModal = true;
    }


    public function deleteImportData()
    {
        UwlrSoapEntry::deleteImportData();
        $this->successDialogMessage = 'Import data succesvol verwijderd';
        $this->showSuccessDialog = true;
    }

    public function deleteImportDataForResultSet($id)
    {
        $resultSet = UwlrSoapResult::findOrFail($id);
        $schoolLocation = SchoolLocation::where('external_main_code',$resultSet->brin_code)->where('external_sub_code',$resultSet->dependance_code)->first();
        if($schoolLocation){
            UwlrSoapEntry::deleteImportDataForSchoolLocationId($schoolLocation->getKey(), $id);
        }
        return $this->redirect(route('uwlr.grid'));
    }

    public function processResult($id)
    {
        $resultSet = UwlrSoapResult::find($id);
        $schoolLocation = SchoolLocation::where('external_main_code',$resultSet->brin_code)->where('external_sub_code',$resultSet->dependance_code)->first();
        if(!$schoolLocation){
            session(['error' => 'Geen schoollocatie gevonden']);
            return false;
        }
        session(['error'=>null]);

        $sectionFound = $schoolLocation->schoolLocationSections->first(function(SchoolLocationSection $sls){
            return optional($sls->section)->name === ImportHelper::DUMMY_SECTION_NAME;
        });

        if(!$sectionFound){
            session(['error' => 'Geen LVS sectie gevonden. Maak eerst een sectie aan met de naam `'.ImportHelper::DUMMY_SECTION_NAME.'` om verder te kunnen gaan']);
            return false;
        }
        session(['error'=>null]);

        set_time_limit(0);
        $this->processingResultId = $id;
        $this->showProcessResultModal = true;
        $this->displayGoToErrorsButton = false;
        $this->processingResult = '';
        $this->processingResultErrors = [];
    }

    public function startProcessingResult()
    {
        $result = UwlrSoapResult::findOrFail($this->processingResultId);
        $result->status = 'READYTOPROCESS';
        $result->save();
        if(BaseHelper::notOnLocal()) {
            dispatch(new ProcessUwlrSoapResultJob($this->processingResultId));
            // for logging
            $result->addToLog('jobInQueue',Carbon::now())->addQueueDataToLog('jobsAtInQueue',true);
        } else {
            set_time_limit(0);
            $helper = ImportHelper::initWithUwlrSoapResult(
                UwlrSoapResult::find($this->processingResultId),
                'sobit.nl'
            );


            $result = $helper->process();
            if (array_key_exists('errors', $result)) {
                if (!is_array($result['errors'])) {
                    $result['errors'] = [$result['errors']];
                }
                $this->processingResultErrors = $result['errors'];
                return false;
            }

            $this->processingResult = collect($result)->join('<BR>');
            $this->displayGoToErrorsButton = !empty(UwlrSoapResult::find($this->processingResultId)->error_messages);
        }
        return $this->redirect(route('uwlr.grid'));
    }

    public function newImport()
    {
        return $this->redirect(route('uwlr.fetcher'));
    }

    public function getModalActiveTabHtmlProperty()
    {
        if (!$this->activeResult) {
            return [];
        }

        $arr = $this->activeResult[$this->modalActiveTab];
        $result = [];
        foreach ($arr as $key => $obj) {

            // is een teacher
            $r = $obj;
            if (is_object($obj)) {
                $r = (array) $obj;
            }
            $groepCollection = collect($this->activeResult['groep']);
            $samengesteldeGroep = (isset($this->activeResult['samengestelde_groep'])) ? $this->activeResult['samengestelde_groep'] : [];
            $samengesteldeGroepCollection = collect($samengesteldeGroep);
            foreach ($r as $k => $value) {
                if (in_array($k, ['groepen', 'groep', 'samengestelde_groepen'])) {
                    $r[$k] = (array) $r[$k];
                   if ($k == 'groep') {

                       $currentGroepKey = $r[$k]['key'];
                       $groep = $groepCollection->first(function($groep) use ($currentGroepKey) {
                           if(is_object($groep)){
                               $groep = (array) $groep;
                           }
                           return $groep['key'] === $currentGroepKey;
                       });
                       $r[$k] = $groep['naam'];
                   }
                    if ($k == 'samengestelde_groepen') {
                        $samengesteldeGroepenKeys = $this->getSamengesteldeGroepenKeys($r[$k]);

                        $samengesteldeGroepen = $samengesteldeGroepCollection->filter(function($groep) use ($samengesteldeGroepenKeys) {
                            if(is_object($groep)){
                                $groep = (array) $groep;
                            }
                            return in_array($groep['key'],  $samengesteldeGroepenKeys);
                        })->map(function($groep){
                            if(is_object($groep)){
                                $groep = (array) $groep;
                            }
                            return $groep['naam'];
                        });

                        $r[$k] = $samengesteldeGroepen->join(',');
                    }
                    if ($k == 'groepen') {
                        $groepenKeys = $this->getGroepenKeys($r[$k]);

                        if($this->hasSamengesteldeGroepInGroepen($r[$k])){
                            $groepen = $samengesteldeGroepCollection->filter(function ($groep) use ($groepenKeys) {
                                if(is_object($groep)){
                                    $groep = (array) $groep;
                                }
                                return in_array($groep['key'], $groepenKeys);
                            })->map(function ($groep) {
                                if(is_object($groep)){
                                    $groep = (array) $groep;
                                }
                                return $groep['naam'];
                            });

                            $r['samengestelde_groepen'] = $groepen->join(',');
                            $r['groepen'] = '';

                        } else {
                            $groepen = $groepCollection->filter(function ($groep) use ($groepenKeys) {
                                if(is_object($groep)){
                                    $groep = (array) $groep;
                                }
                                return in_array($groep['key'], $groepenKeys);
                            })->map(function ($groep) {
                                if(is_object($groep)){
                                    $groep = (array) $groep;
                                }
                                return $groep['naam'];
                            });

                            $r[$k] = $groepen->join(',');
                        }
                    }
                    //unset($r[$k]);
                }
            }
            $arr[$key] = $r;
        }

        return $arr;
    }

    protected function getSamengesteldeGroepenKeys($data)
    {
        return $this->getGroepenKeys($data);
    }

    protected function hasSamengesteldeGroepInGroepen($data)
    {
        if(is_object($data)){
            return property_exists($data,'samengestelde_groep') && !empty($data->samengestelde_groep);
        }
        return array_key_exists('samengestelde_groep',$data);
    }

    protected function getGroepenKeys($data)
    {
        $data = (array) $data;
        if($this->hasSamengesteldeGroepInGroepen($data)){
            $returnData = [];
            foreach($data['samengestelde_groep'] as $gData){
                if(is_array($gData) && array_key_exists('key',$gData)){
                    $returnData[] = $gData['key'];
                } else {
                    $returnData[] = $gData;
                }
            }
            return $returnData;
        }
        return $data;
    }

    public function render()
    {
        return view('livewire.uwlr-grid')
            ->layout('layouts.app-admin');
    }
}
