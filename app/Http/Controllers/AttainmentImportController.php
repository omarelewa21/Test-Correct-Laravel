<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Attainment;
use tcCore\ExcelAttainmentManifest;
use tcCore\Http\Requests;
use tcCore\Lib\Repositories\AverageRatingRepository;
use tcCore\Lib\Repositories\SchoolClassRepository;
use tcCore\Lib\User\Factory;
use tcCore\SchoolClass;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\SchoolClassesStudentImportRequest;
use tcCore\SchoolLocation;
use tcCore\User;

class AttainmentImportController extends Controller {

	/**
	 * Import attainments.
	 * @param SchoolClassesStudentImportRequest $request
	 * @return
	 */
	public function import(Requests\AttainmentImportRequest $request)
	{
	    $excelFile = $request->file('attainments');

        $attainmentManifest = new ExcelAttainmentManifest($excelFile);

        $heads = [];
        $added = 0;
        $existed = 0;
        DB::beginTransaction();
        try {
            collect($attainmentManifest->getAttainmentResources())->each(function($resource) use (&$heads, &$added, &$existed){
                logger('educationLevelIds');
                logger($resource->education_level_id);
                if($resource->subcode === null){$resource->subcode = '';};
                foreach(Arr::wrap($resource->education_level_id) as $education_level_id){
                    if(!Attainment::where('code',$resource->code)
                                    ->where('base_subject_id',$resource->base_subject_id)
                                    ->where('education_level_id',$education_level_id)
                                    ->where('subcode',$resource->subcode)
                                    ->first()){
                        // we need to create one
                        $headKey = sprintf('%s-%s',$resource->code,$education_level_id);
                        if($resource->subcode !== '' && $resource->subcode !== null){
                            if(!array_key_exists($headKey,$heads)){
                             $a = Attainment::where('code',$resource->code)
                                 ->where('base_subject_id',$resource->base_subject_id)
                                 ->where('education_level_id',$education_level_id)
                                 ->where('subcode','')->first();
                             if(!$a){
                                 logger('education level id '.$education_level_id);
                                 throw new \Exception(sprintf('No head attainment for this new attainment %s and key %s in class %s', $resource->code,$headKey, __CLASS__));
                             }
                             $heads[$headKey] = $a->getKey();
                            }
                            $data = array_merge((array) $resource,['attainment_id' => $heads[$headKey], 'education_level_id' => $education_level_id]);
                            logger('create attainment WITH subcode');
                            logger($data);
                            Attainment::create($data);
                            $added++;
                        } else {
                            $data = (array) $resource;
                            $data['education_level_id'] = $education_level_id;
                            $data['subcode'] = '';
                            logger('create attainment without subcode');
                            logger($data);
                            $a = Attainment::create($data);
                            $heads[$headKey] = $a->getKey();
                            $added++;
                        }
                    } else {
                        $existed++;
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
        return response()->json(['data' => $added.' nieuwe leerdoelen zijn toegevoegd, '.$existed.' bestonden er al voor dit vak'], 200);
	}

}
