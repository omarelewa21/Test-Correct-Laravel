<?php

namespace tcCore\View\Components\Partials\Header;

use Illuminate\Contracts\View\View;

class Assessment extends HeaderComponent
{
    public function __construct(

    )
    {
        parent::__construct();
    }

    public function render(): View
    {
        return view('components.partials.header.assessment');
    }
}
