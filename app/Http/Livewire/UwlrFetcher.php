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

    private $result;

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

        $this->result = $helper->getResult();
        $this->resultIdendifier = $helper->getResultIdentifier();




    }



    public function render()
    {


        return view('livewire.uwlr-fetcher')->with(['result' => $this->result]);
    }
}
