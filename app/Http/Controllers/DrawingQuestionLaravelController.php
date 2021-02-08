<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Shared\Drawing;
use tcCore\DrawingQuestion;

class DrawingQuestionLaravelController extends Controller
{
    public function show(DrawingQuestion $question)
    {
        $drawing = $question->answer;

        return view('components.question.drawing-modal', compact('drawing', $drawing));
    }
}
