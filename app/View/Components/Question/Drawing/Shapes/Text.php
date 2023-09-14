<?php

namespace tcCore\View\Components\Question\Drawing\Shapes;

class Text extends Shape
{
    public function setProperties()
    {
        $this->title = __('drawing-modal.Tekst');
        $this->icon = $this->id = $this->shape = 'text';
    }
}
