<?php

namespace tcCore\View\Components\Question\Drawing\Shapes;

class Line extends Shape
{
    public function setProperties()
    {
        $this->title = __('drawing-modal.Rechte lijn');
        $this->icon = $this->id = $this->shape = 'line';
    }
}
