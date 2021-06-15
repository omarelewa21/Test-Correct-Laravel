<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Helpers\ImportHelper;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\User;
use tcCore\UwlrSoapEntry;
use tcCore\UwlrSoapResult;

class UwlrGrid extends Component
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
    public $errorMessages = '';
    public $displayGoToErrorsButton = false;


    public function mount()
    {
        $this->resultSets = UwlrSoapResult::orderBy('created_at', 'desc')->with('entries')->get();
    }

    public function activateResult($id)
    {

        $this->activeResult = UwlrSoapResult::find($id)->asData()->toArray();
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

    public function deleteMagister()
    {
        UwlrSoapEntry::deleteMagisterData();
        $this->successDialogMessage = 'Magister data succesvol verwijderd';
        $this->showSuccessDialog = true;
    }

    public function processResult($id)
    {
        $this->processingResultId = $id;
        $this->showProcessResultModal = true;
        $this->displayGoToErrorsButton = false;
        $this->processingResult = '';
        $this->processingResultErrors = [];
    }

    public function startProcessingResult()
    {
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


            foreach ($r as $k => $value) {
                if (in_array($k, ['groepen', 'groep', 'samengestelde_groepen'])) {
                    $groepCollection = collect($this->activeResult['groep']);
                    $samengesteldeGroepCollection = collect($this->activeResult['samengestelde_groep']);
                   if ($k == 'groep') {
                       $currentGroepKey = $r[$k]['key'];
                       $groep = $groepCollection->first(function($groep) use ($currentGroepKey) {
                           return $groep['key'] === $currentGroepKey;
                       });
                       $r[$k] = $groep['naam'];
                   }
                    if ($k == 'samengestelde_groepen') {
                        $samengesteldeGroepenKeys = $r[$k];

                        $samengesteldeGroepen = $samengesteldeGroepCollection->filter(function($groep) use ($samengesteldeGroepenKeys) {
                            return in_array($groep['key'],  $samengesteldeGroepenKeys);
                        })->map(function($groep){
                            return $groep['naam'];
                        });

                        $r[$k] = $samengesteldeGroepen->join(',');
                    }
                    if ($k == 'groepen') {
                        $groepenKeys = $r[$k];
                        $groepen = $groepCollection->filter(function($groep) use ($groepenKeys) {
                            return in_array($groep['key'],  $groepenKeys);
                        })->map(function($groep){
                            return $groep['naam'];
                        });

                        $r[$k] = $groepen->join(',');
                    }
                    //unset($r[$k]);
                }
            }
            $arr[$key] = $r;
        }

        return $arr;
    }

    public function render()
    {
        return view('livewire.uwlr-grid')->layout('layouts.app-admin');;
    }
}
