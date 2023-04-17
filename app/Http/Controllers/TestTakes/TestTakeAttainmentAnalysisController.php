<?php namespace tcCore\Http\Controllers\TestTakes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\Attainment;
use tcCore\Http\Controllers\Controller;
use tcCore\TestTakeEvent;
use tcCore\TestTake;
use tcCore\User;

class TestTakeAttainmentAnalysisController extends Controller {

    /**
     * Display a listing of attainment scores of this test take.
     *
     * @return Response
     */
    public function index(TestTake $testTake, Request $request)
    {
        $participantIdsBuilder = $testTake->testParticipants()->select('id');
        $data = DB::table('p_values')
            ->leftJoin('questions','p_values.question_id','=','questions.id')
            ->leftJoin('question_attainments','questions.id','=','question_attainments.question_id')
            ->leftJoin('attainments','question_attainments.attainment_id','=','attainments.id')
            ->selectRaw('attainments.*,
                    sum(p_values.score) as total_score, 
                    sum(p_values.max_score) as total_max_score,count(question_attainments.question_id) as questions_per_attainment,
                    count(distinct p_values.test_participant_id) as count_testparticipants,
                    sum(p_values.score)/sum(p_values.max_score) as p_value')
            ->whereIn('test_participant_id',$participantIdsBuilder)
            ->whereNull('p_values.deleted_at')
            ->whereNull('question_attainments.deleted_at')
            ->whereNull('questions.deleted_at')
            ->whereNull('attainments.deleted_at')
            ->whereNotNull('attainments.id')
            ->groupBy('attainments.id')
            ->orderBy('attainments.code','asc')
            ->orderBy('attainments.subcode')
            ->get();

        return Response::make(Attainment::hydrate($data->toArray()), 200);
    }


    /**
     * Display the specified test take attainment details per student.
     *
     * @param  TestTakeEvent  $testTakeEvent
     * @return Response
     */
    public function show(TestTake $testTake, Attainment $attainment)
    {
        $participantIdsBuilder = $testTake->testParticipants()->select('id');
        $data = DB::table('p_values')
            ->leftJoin('questions','p_values.question_id','=','questions.id')
            ->leftJoin('question_attainments','questions.id','=','question_attainments.question_id')
            ->leftJoin('test_participants','p_values.test_participant_id','=','test_participants.id')
            ->leftJoin('users','test_participants.user_id','=','users.id')
            ->selectRaw('users.name_first,users.name_suffix,users.name,
                    sum(p_values.score) as total_score, 
                    sum(p_values.max_score) as total_max_score,count(question_attainments.question_id) as questions_per_attainment,
                    count(distinct p_values.test_participant_id) as count_testparticipants,
                    sum(p_values.score)/sum(p_values.max_score) as p_value')
            ->whereIn('test_participant_id',$participantIdsBuilder)
            ->where('question_attainments.attainment_id',$attainment->getKey())
            ->whereNull('p_values.deleted_at')
            ->whereNull('question_attainments.deleted_at')
            ->whereNull('questions.deleted_at')
            ->whereNull('users.deleted_at')
            ->whereNull('test_participants.deleted_at')
            ->whereNotNull('question_attainments.attainment_id')
            ->orderBy('p_value','desc')
            ->groupBy('test_participant_id')
            ->get();

        return Response::make(User::hydrate($data->toArray()), 200);
    }


}
