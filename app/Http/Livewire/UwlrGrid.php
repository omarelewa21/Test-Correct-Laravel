<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\UwlrSoapResult;

class UwlrGrid extends Component
{
    public $showImportModal = false;
    public $resultSets;
    public $activeResult = null;
    public $modalActiveTab = 'school';

    public function mount()
    {
        Auth::loginUsingId(755);
        $this->resultSets = UwlrSoapResult::orderBy('created_at', 'desc')->with('entries')->get();
    }

    public function activateResult($id) {
        $this->activeResult = UwlrSoapResult::find($id)->asData()->toArray();
        $this->showImportModal = true;
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
        return view('livewire.uwlr-grid');
    }
}
