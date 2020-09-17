<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\ExcelAttainmentCitoManifest;
use tcCore\Http\Requests;
use tcCore\Http\Requests\SchoolClassesStudentImportRequest;
use tcCore\Question;
use tcCore\QuestionAttainment;

class AttainmentCitoImportController extends Controller {

	public function data()
    {
        return response()->json([
            'subjects' => BaseSubject::orderBy('name')->get(),
        ]);
    }

    /**
	 * Import attainments.
	 * @param SchoolClassesStudentImportRequest $request
	 * @return
	 */
	public function import(Requests\AttainmentImportRequest $request)
	{
	    $excelFile = $request->file('attainments');

	    $baseSubjectId = $request->subject_id;

        $attainmentManifest = new ExcelAttainmentCitoManifest($excelFile);

        $heads = [];
        $added = 0;
        $existed = 0;
        $notFound = [];
        DB::beginTransaction();
        try {
            collect($attainmentManifest->getAttainmentResources())->each(function($resource) use (&$heads, &$added, &$existed, $baseSubjectId, &$notFound){
                foreach($resource->code_subcode_ar as $item) {
                    $code = $item['code'];
                    $subcode = $item['subcode'];
                    $attainment = Attainment::where('code', $code)
                        ->where('base_subject_id', $baseSubjectId)
                        ->where('education_level_id', $resource->highest_level)
                        ->where('subcode', $subcode)
                        ->first();
                    if (!$attainment) {
                        logger('baseSubjectId ' . $baseSubjectId);
                        logger('resource');
                        logger((array)$resource);
                        $vak = BaseSubject::find($baseSubjectId)->name;
                        $niveau = EducationLevel::find($resource->highest_level)->name;
                        throw new \Exception(
                            sprintf(
                                'Could not find the corresponding attainment:<br />code => %s,<br />subcode => %s,<br />vak => %s (baseSubjectId => %s),<br/>niveau => %s (educationLevelId => %s)<br/>in class %s',
                                $code,
                                $subcode,
                                $vak,
                                $baseSubjectId,
                                $niveau,
                                $resource->highest_level,
                                __CLASS__));
                    }
                    $questions = Question::where('external_id', $resource->external_id)->get();
                    if ($questions->count() < 1) {
                        $notFound[] = $resource->external_id;
                    } else {
                        $questions->each(function (Question $question) use ($attainment, &$added, &$existed) {
                            if (QuestionAttainment::where('question_id', $question->getKey())
                                    ->where('attainment_id', $attainment->getKey())
                                    ->count() < 1) {
                                // we need to create one
                                QuestionAttainment::create([
                                    'question_id' => $question->getKey(),
                                    'attainment_id' => $attainment->getKey()
                                ]);
                                $added++;
                            } else {
                                $existed++;
                            }
                        });
                    }
                }
            });

        }
        catch(\Exception $e){
            DB::rollback();
            logger($e);
            logger($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        DB::commit();
        $return = sprintf('%s nieuwe koppelingen zijn toegevoegd<br/>%s bestonden er al voor dit vak',$added,$existed);
        if(count($notFound) > 0){
            $return = sprintf('%s<br/><span style="font-color:red">De volgende vragen konden niet gevonden worden %s</span>',$return,implode(', ',$notFound));
        }
        return response()->json(['data' => $return], 200);
	}

}
