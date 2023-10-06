<?php

namespace tcCore\View\Components\Question\Drawing\Shapes;

class Freehand extends Shape
{
    public function setProperties(): void
    {
        $this->title = __('drawing-modal.Penlijn');
        $this->icon = $this->id = $this->shape = 'freehand';
    }
}
