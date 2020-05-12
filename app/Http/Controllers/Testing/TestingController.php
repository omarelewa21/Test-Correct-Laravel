<?php namespace tcCore\Http\Controllers\Testing;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Answer;
use tcCore\Http\Requests\CreateAnswerRequest;
use tcCore\Http\Requests\UpdateAnswerRequest;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\TestParticipant;

class TestingController extends Controller {

    public function store() {
        $file =  database_path(sprintf('seeds/testing/db_dump_%s.sql', request('flag')));
        if (file_exists($file)) {
            Artisan::call('test:refreshdb --file='.request('flag'));
            return 'ok';
        }


        return 'error';
    }

}
