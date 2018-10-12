<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\DrawingQuestion;

class DrawingQuestionsController extends Controller {
    /**
     * Offers a download to the specified drawing question from storage.
     *
     * @param DrawingQuestion $drawingQuestion
     * @return Response
     */
    public function bg(DrawingQuestion $drawingQuestion)
    {
        if (File::exists($drawingQuestion->getCurrentBgPath())) {
            return Response::download($drawingQuestion->getCurrentBgPath(), $drawingQuestion->getAttribute('bg_name', null));
        } else {
            return Response::make('Drawing question background not found', 404);
        }
    }
}
