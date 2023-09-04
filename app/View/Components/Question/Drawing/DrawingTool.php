<?php

namespace tcCore\View\Components\Question\Drawing;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;


class DrawingTool extends Component
{
    const SHAPES = ['Rectangle'];

    public Collection $shapes;

    public function __construct()
    {
        $this->setShapesProperty();
    }

    public function render(): View
    {
        return view('components.question.drawing.drawing-tool');
    }

    public function setShapesProperty()
    {
        $this->shapes = collect(self::SHAPES)->flatMap(function ($shape) {
            $class = "tcCore\View\Components\Question\Drawing\Shapes\\$shape";
            return [
                $shape => new $class,
            ];
        });
    }
}
