<?php

namespace tcCore\View\Components\Question\Drawing\Shapes;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

abstract class Shape extends Component
{
    public string $title, $id, $icon, $shape;

    public function __construct()
    {
        $this->setProperties();
    }

    public function render(): View
    {
        $view = strtolower(class_basename($this));
        return view("components.question.drawing.shapes.$view", ['instance' => $this]);
    }

    abstract protected function setProperties(): void;
}
