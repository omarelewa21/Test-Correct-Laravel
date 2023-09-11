<?php

namespace tcCore\View\Components\Question\Drawing\Shapes;

class Line extends shape
{
    public function setProperties()
    {
        $this->title = __('drawing-modal.Rechte lijn');
        $this->icon = $this->id = $this->shape = 'line';
    }
}
