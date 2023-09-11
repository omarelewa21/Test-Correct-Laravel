<?php

namespace tcCore\View\Components\Question\Drawing\Shapes;

class Circle extends shape
{
    public function setProperties()
    {
        $this->title = __('drawing-modal.Cirkel');
        $this->icon = $this->id = $this->shape = 'circle';
    }
}
