<?php

namespace tcCore\View\Components\Question\Drawing\Shapes;

class Rectangle extends Shape
{
    public function setProperties()
    {
        $this->title = __('drawing-modal.Rechthoek');
        $this->icon = 'square';
        $this->id = $this->shape = 'rect';
    }
}
