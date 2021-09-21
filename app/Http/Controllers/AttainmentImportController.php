<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\ExcelAttainmentManifest;
use tcCore\ExcelAttainmentUpdateOrCreateManifest;
use tcCore\Http\Requests;
use tcCore\Lib\Repositories\AverageRatingRepository;
use tcCore\Lib\Repositories\SchoolClassRepository;
use tcCore\Lib\User\Factory;
use tcCore\SchoolClass;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\SchoolClassesStudentImportRequest;
use tcCore\SchoolLocation;
use tcCore\User;

class AttainmentImportController extends Controller
{
    protected $attainmentsCollection;

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
            collect($attainmentManifest->getAttainmentResources())->each(function ($resource) use (&$heads, &$added, &$existed) {
                logger('educationLevelIds');
                logger($resource->education_level_id);
                if ($resource->subcode === null) {
                    $resource->subcode = '';
                };
                foreach (Arr::wrap($resource->education_level_id) as $education_level_id) {
                    if (!Attainment::where('code', $resource->code)
                        ->where('base_subject_id', $resource->base_subject_id)
                        ->where('education_level_id', $education_level_id)
                        ->where('subcode', $resource->subcode)
                        ->first()) {
                        // we need to create one
                        $headKey = sprintf('%s-%s', $resource->code, $education_level_id);
                        if ($resource->subcode !== '' && $resource->subcode !== null) {
                            if (!array_key_exists($headKey, $heads)) {
                                $a = Attainment::where('code', $resource->code)
                                    ->where('base_subject_id', $resource->base_subject_id)
                                    ->where('education_level_id', $education_level_id)
                                    ->where('subcode', '')->first();
                                if (!$a) {
                                    logger('education level id ' . $education_level_id);
                                    throw new \Exception(sprintf('No head attainment for this new attainment %s and key %s in class %s', $resource->code, $headKey, __CLASS__));
                                }
                                $heads[$headKey] = $a->getKey();
                            }
                            $data = array_merge((array)$resource, ['attainment_id' => $heads[$headKey], 'education_level_id' => $education_level_id]);
                            logger('create attainment WITH subcode');
                            logger($data);
                            Attainment::create($data);
                            $added++;
                        } else {
                            $data = (array)$resource;
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

        } catch (\Exception $e) {
            DB::rollback();
            logger($e);
            logger($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        DB::commit();
        return response()->json(['data' => $added . ' nieuwe leerdoelen zijn toegevoegd, ' . $existed . ' bestonden er al voor dit vak'], 200);
    }

    public function deleteAttainmentsNotPresentInImport(Requests\AttainmentImportRequest $request)
    {
        DB::beginTransaction();
        $deleted = 0;
        try{
            $this->checkDeleteRoutineHasNotYetBeenExecuted();
            $attainmentsDbIds = Attainment::withTrashed()->get()->pluck('id');
            $excelFile = $request->file('attainments');
            $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($excelFile);
            $attainmentsImportIds = collect($attainmentManifest->getAttainmentResources())->pluck('id');
            $diffIds = array_diff($attainmentsDbIds,$attainmentsImportIds);
            foreach ($diffIds as $id){
                $attainment = Attainment::findOrFail($id);
                $attainment->delete();
            }
        } catch (\Exception $e) {
            DB::rollback();
            logger($e);
            logger($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        DB::commit();
        return response()->json(['data' => $deleted.' attainments soft deleted'], 200);

    }

    public function importForUpdateOrCreate(Requests\AttainmentImportRequest $request)
    {
        $excelFile = $request->file('attainments');
        $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($excelFile);
        $added = 0;
        $updated = 0;
        DB::beginTransaction();
        try {
            $this->checkDeleteRoutineHasAlreadyBeenExecuted();
            $this->attainmentsCollection = collect($attainmentManifest->getAttainmentResources());
            $this->checkBaseSubjectsIntegrity();
            $this->checkEducationLevelIntegrity();
            $this->attainmentsCollection->each(function ($resource) use (&$added,&$updated) {
                if(is_null($resource->id)){
                    $parentId = $this->findParent($resource);
                    $this->createAttainment($resource,$parentId);
                    $added++;
                    return true;
                }
                $this->updateAttainment($resource);
                $updated++;
            });
        } catch (\Exception $e) {
            DB::rollback();
            logger($e);
            logger($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        DB::commit();
        return response()->json(['data' => $added . ' new attainments are created, '.$updated.' updated'], 200);
    }

    protected function checkBaseSubjectsIntegrity()
    {
        $baseSubjects = BaseSubject::all()->pluck(['id', 'name'])->toArray();
        foreach ($this->attainmentsCollection as $attainmentResource) {
            if (is_null($attainmentResource->id)) {
                continue;
            }
            if (!array_key_exists($attainmentResource->base_subject_id, $baseSubjects)) {
                throw new \Exception(
                    'unknown base_subject_id:' . $attainmentResource->base_subject_id . ' attainment:' . json_encode($attainmentResource)
                );
            }
            if (strtolower($baseSubjects[$attainmentResource->id]) != strtolower($attainmentResource->base_subject_name)) {
                throw new \Exception(
                    'base_subject_is with wrong name base_subject_id:' . $attainmentResource->base_subject_id . ' base_subject_name:' . $attainmentResource->base_subject_name . ' attainment:' . json_encode($attainmentResource)
                );
            }
        }

    }

    protected function checkEducationLevelIntegrity()
    {
        $educationLevels = EducationLevel::all()->pluck(['id', 'name'])->toArray();
        foreach ($this->attainmentsCollection as $attainmentResource) {
            if (is_null($attainmentResource->id)) {
                continue;
            }
            if (!array_key_exists($attainmentResource->eduction_level_id, $educationLevels)) {
                throw new \Exception(
                    'unknown eduction_level_id:' . $attainmentResource->eduction_level_id . ' attainment:' . json_encode($attainmentResource)
            );
            }
            if (strtolower($educationLevels[$attainmentResource->id]) != strtolower($attainmentResource->education_level_name)) {
                throw new \Exception(
                    'base_subject_is with wrong name eduction_level_id:' . $attainmentResource->eduction_level_id . ' education_level_name:' . $attainmentResource->education_level_name . ' attainment:' . json_encode($attainmentResource)
                );
            }
        }
    }

    protected function checkDeleteRoutineHasAlreadyBeenExecuted()
    {
        $attainmentsDbIds = Attainment::onlyTrashed()->get()->pluck('id');
        if(count($attainmentsDbIds)==0){
            throw new \Exception('deleteAttainmentsNotPresentInImport has not yet run, no soft deleted records in db');
        }
    }

    protected function checkDeleteRoutineHasNotYetBeenExecuted()
    {
        $attainmentsDbIds = Attainment::onlyTrashed()->get()->pluck('id');
        if(count($attainmentsDbIds)>0){
            throw new \Exception('deleteAttainmentsNotPresentInImport has already run, soft deleted records in db');
        }
    }

    protected function createAttainment(&$resource,$parentId)
    {
        $data = collect($resource)->toArray();
        $data['attainment_id'] = $parentId;
        $attainment = new Attainment();
        $attainment->fill($data);
        $attainment->save();
        $resource->id = $attainment->getKey();
        $resource->attainment_id = $parentId;
    }

    protected function updateAttainment($resource)
    {
        $data = collect($resource)->toArray();
        $attainment = Attainment::findOrFail($resource->id);
        $attainment->fill($data);
        $attainment->save();
    }

    protected function findParent($resource)
    {
        if(!is_null($resource->id)){
            return $resource->attainment_id;
        }
        if(!is_null($resource->attainment_id)){
            return $resource->attainment_id;
        }
        if(is_null($resource->subcode)){
            return null;
        }
        if(is_null($resource->parent_temp_id)){
            throw new \Exception('cannot identify attainment_id for entry with a subcode. parent_temp_id is empty. attainment:' . json_encode($resource));
        }
        $parent = $this->attainmentsCollection->where('temp_id',$resource->parent_temp_id)->first();
        if(is_null($parent)){
            throw new \Exception('cannot identify attainment_id for entry with a subcode. cannot find entry with temp_id:'.$resource->parent_temp_id.'. attainment:' . json_encode($resource));
        }
        if(is_null($parent->id)){
            throw new \Exception('cannot identify attainment_id for entry with a subcode. entry with temp_id:'.$resource->parent_temp_id.' has not yet an id. attainment:' . json_encode($resource));
        }
        return $parent->id;
    }

}
