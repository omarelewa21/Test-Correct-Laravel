<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Shared\Drawing;
use tcCore\Answer;
use tcCore\DrawingQuestion;
use tcCore\Http\Helpers\SvgHelper;

class DrawingQuestionLaravelController extends Controller
{
    public function show(Answer $answer)
    {
        $file = Storage::get($answer->getDrawingStoragePath());

        if (substr($file, 0, 4) ==='<svg') {
            header('Content-type: image/svg+xml');
            echo $file;
            die;
        }

        return file_get_contents($file);
    }

    public function showAnswerModel(DrawingQuestion $question)
    {
        $svgHelper = new SvgHelper($question->uuid);

        $file = Storage::disk(SvgHelper::DISK)->path(sprintf('%s/correction_model.png', $question->uuid));

        return file_get_contents($file);
        $file = $svgHelper->getCorrectionModelPNG();

        return $file;

    }
}
