<?php

namespace tcCore\View\Components;

use Illuminate\View\Component;

class UserPValueIndicator extends Component
{
    public $pValueNumber;

    public $disabled = false;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($pValue, $disabled=false)
    {
        $this->pValueNumber = number_format($pValue, 2);

        $this->disabled = $disabled;

        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.user-p-value-indicator');
    }
}
