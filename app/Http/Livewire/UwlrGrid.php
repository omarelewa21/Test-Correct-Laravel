<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Helpers\RTTIImportHelper;
use tcCore\UwlrSoapEntry;
use tcCore\UwlrSoapResult;

class UwlrGrid extends Component
{
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

    public function mount()
    {
        Auth::loginUsingId(755);
        $this->resultSets = UwlrSoapResult::orderBy('created_at', 'desc')->with('entries')->get();
    }

    public function activateResult($id) {

        $this->activeResult = UwlrSoapResult::find($id)->asData()->toArray();

        $this->showImportModal = true;
    }

    public function triggerErrorModal($id){
        $this->errorMessages = UwlrSoapResult::find($id)->error_messages;

        $this->showErrorModal = true;
    }

    public function processResult($id)
    {
        $this->processingResultId = $id;
        $this->showProcessResultModal = true;
        $this->processingResult = '';
        $this->processingResultErrors = [];
    }

    public function startProcessingResult(){
        $helper = RTTIImportHelper::initWithUwlrSoapResult(
            UwlrSoapResult::find($this->processingResultId),
            'sobit.nl'
        );

        $result = $helper->process();
        if (array_key_exists('errors', $result)){
            if (!is_array($result['errors'])) {
                $result['errors'] = [$result['errors']];
            }
            $this->processingResultErrors =  $result['errors'];
            return false;
        }

        $this->processingResult = collect($result)->join('<BR>');
    }

    public function newImport()
    {
        return $this->redirect(route('uwlr.fetcher'));
    }

    public function getModalActiveTabHtmlProperty(){
        if (!$this->activeResult) return [];

        $arr =$this->activeResult[$this->modalActiveTab];
        foreach($arr as $key => $obj) {
            $r = $obj;
            if (is_object($obj)) {
                $r = (array) $obj;
            }

            foreach($r as $k => $value) {
                if (in_array($k, ['groepen', 'groep', 'samengestelde_groepen'])) {
                    unset($r[$k]);
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
