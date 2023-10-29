<?php

namespace tcCore\View\Components\Question\Drawing;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use tcCore\View\Components\Question\Drawing\Shapes\Circle;
use tcCore\View\Components\Question\Drawing\Shapes\Freehand;
use tcCore\View\Components\Question\Drawing\Shapes\Line;
use tcCore\View\Components\Question\Drawing\Shapes\Rectangle;
use tcCore\View\Components\Question\Drawing\Shapes\Text;


class DrawingTool extends Component
{
    public const SHAPES = [
        'Rectangle' => Rectangle::class,
        'Circle'    => Circle::class,
        'Line'      => Line::class,
        'Freehand'  => Freehand::class,
        'Text'      => Text::class
    ];

    public Collection $shapes;

    public function __construct()
    {
        $this->setShapesProperty();
    }

    public function render(): View
    {
        return view('components.question.drawing.drawing-tool');
    }

    public function setShapesProperty(): void
    {
        $this->shapes = collect(self::SHAPES)->flatMap(fn($value, $key) => [$key => new $value()]);
    }
}
