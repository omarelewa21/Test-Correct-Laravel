<?php

namespace tcCore\View\Components\Question\Drawing\Shapes;

class Freehand extends shape
{
    public function setProperties()
    {
        $this->title = __('drawing-modal.Penlijn');
        $this->icon = $this->id = $this->shape = 'freehand';
    }
}
