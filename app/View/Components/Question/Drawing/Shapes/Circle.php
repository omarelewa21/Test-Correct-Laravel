<?php

namespace tcCore\View\Components\Question\Drawing\Shapes;

class Circle extends Shape
{
    public function setProperties(): void
    {
        $this->title = __('drawing-modal.Cirkel');
        $this->icon = $this->id = $this->shape = 'circle';
    }
}
