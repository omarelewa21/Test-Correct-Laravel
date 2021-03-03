<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Shared\Drawing;
use tcCore\Answer;
use tcCore\DrawingQuestion;

class DrawingQuestionLaravelController extends Controller
{
    public function show(Answer $answer)
    {
        $file = Storage::get($answer->getDrawingStoragePath());

        return file_get_contents($file);
    }
}
