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
use Maatwebsite\Excel\Facades\Excel;

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

    public function upload(Requests\AttainmentUploadRequest $request)
    {
        $excelFile = $request->file('attainments')->storeAs(
            'attainments_upload', 'attainments.xlsx'
        );
        return response()->json(['data' => 'Bestand staat op de server. Voer php artisan import:attainments uit om te importeren'], 200);
    }

    public function showAttainmentsNotPresentInImport(Request $request)
    {
        $deleted = 0;
        $attainments = [];
        try{
            $attainmentsDbIds = Attainment::withTrashed()->get()->pluck('id')->toArray();
            dump(count($attainmentsDbIds));
            $excelFile = $request->attainments;
            $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($excelFile);
            $attainmentsImportIds = collect($attainmentManifest->getAttainmentResources())->pluck('id');
            $attainmentsImportIds = $attainmentsImportIds->filter(function ($value, $key) {
                return !is_null($value);
            })->toArray();
            dump(count($attainmentsImportIds));
            $diffIds = array_diff($attainmentsDbIds,$attainmentsImportIds);
            foreach ($diffIds as $id){
                $attainment = Attainment::findOrFail($id);
                $attainments[] = $attainment;
                dump($attainment->toArray());
                DB::table('attainments_old')->insert($attainment->toArray());
            }
        } catch (\Exception $e) {
            DB::rollback();
            logger($e);
            logger($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        return response()->json(['data' => $attainments], 200);
    }

    public function setAttainmentsInactiveNotPresentInImport(Request $request)
    {
        $this->checkInactivateRoutineHasNotYetBeenExecuted();
        $updated = 0;
        $attainments = [];
        try{
            $attainmentsDbIds = Attainment::withTrashed()->get()->pluck('id')->toArray();
            $excelFile = $request->attainments;
            $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($excelFile);
            $attainmentsImportIds = collect($attainmentManifest->getAttainmentResources())->pluck('id');
            $attainmentsImportIds = $attainmentsImportIds->filter(function ($value, $key) {
                return !is_null($value);
            })->toArray();
            $diffIds = array_diff($attainmentsDbIds,$attainmentsImportIds);
            foreach ($diffIds as $id){
                $attainment = Attainment::findOrFail($id);
                $attainment->status = 'OLD';
                $attainment->save();
                $attainments[] = $attainment;
                $updated++;
            }
        } catch (\Exception $e) {
            DB::rollback();
            logger($e);
            logger($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
        return response()->json(['data' => $updated.' set inactive'], 200);
    }

    public function importForUpdateOrCreate(Request $request)
    {
        $this->checkInactivateRoutineHasAlreadyBeenExecuted();
        $excelFile = $request->attainments;
        $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($excelFile);
        $added = 0;
        $updated = 0;
        DB::beginTransaction();
        try {
            $this->checkSheetsIntegrity($attainmentManifest);
            $this->attainmentsCollection = collect($attainmentManifest->getAttainmentResources());
            $this->checkAttainmentCollection();
            $this->checkBaseSubjectsIntegrity();
            $this->checkEducationLevelIntegrity();
            $this->checkCodeIntegrity();
            $this->checkLevelCodeSubcodeSubsubcodeCombination();
            $this->fillInParents();
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
        $baseSubjects = BaseSubject::all()->pluck('name','id')->toArray();
        foreach ($this->attainmentsCollection as $attainmentResource) {
            if (is_null($attainmentResource->base_subject_id)) {
                throw new \Exception(
                    'missing base_subject_id attainment:' . json_encode($attainmentResource)
                );
            }
            if (!array_key_exists($attainmentResource->base_subject_id, $baseSubjects)) {
                throw new \Exception(
                    'unknown base_subject_id:' . $attainmentResource->base_subject_id . ' attainment:' . json_encode($attainmentResource)
                );
            }
            if (strtolower($baseSubjects[$attainmentResource->base_subject_id]) != strtolower($attainmentResource->base_subject_name)) {
                throw new \Exception(
                    'base_subject_is with wrong name. base_subject_id:' . $attainmentResource->base_subject_id . ' base_subject_name:' . $attainmentResource->base_subject_name . ' attainment:' . json_encode($attainmentResource)
                );
            }
        }

    }

    protected function checkEducationLevelIntegrity()
    {
        $educationLevels = EducationLevel::all()->pluck('name','id')->toArray();
        foreach ($this->attainmentsCollection as $attainmentResource) {
            if (is_null($attainmentResource->education_level_id)) {
                throw new \Exception(
                    'missing education_level_id attainment:' . json_encode($attainmentResource)
                );
            }
            if (!array_key_exists($attainmentResource->education_level_id, $educationLevels)) {
                throw new \Exception(
                    'unknown education_level_id:' . $attainmentResource->education_level_id . ' attainment:' . json_encode($attainmentResource)
            );
            }
            if (strtolower($educationLevels[$attainmentResource->education_level_id]) != strtolower($attainmentResource->education_level_name)) {
                throw new \Exception(
                    'education_level_id with wrong name. education_level_id:' . $attainmentResource->education_level_id . ' education_level_name:' . $attainmentResource->education_level_name . ' attainment:' . json_encode($attainmentResource)
                );
            }
        }
    }

    protected function checkCodeIntegrity()
    {
        foreach ($this->attainmentsCollection as $attainmentResource) {
            if (is_null($attainmentResource->code)) {
                throw new \Exception(
                    'missing code attainment:' . json_encode($attainmentResource)
                );
            }

        }
    }

    protected function checkAttainmentCollection()
    {
        if($this->attainmentsCollection->count()==0){
            throw new \Exception(
                'attainmentsCollection is empty. There is probably a problem with your excel file. Make sure that every sheet starts with headers'
            );
        }
    }

    protected function checkInactivateRoutineHasAlreadyBeenExecuted()
    {
        $attainmentsDbIds = Attainment::where('status','OLD')->get()->pluck('id');
        if(count($attainmentsDbIds)==0){
            throw new \Exception('setAttainmentsInactiveNotPresentInImport has not yet run, no inactive records in db');
        }
    }

    protected function checkInactivateRoutineHasNotYetBeenExecuted()
    {
        $attainmentsDbIds = Attainment::where('status','OLD')->get()->pluck('id');
        if(count($attainmentsDbIds)>0){
            throw new \Exception('setAttainmentsInactiveNotPresentInImport has already run, inactive records in db');
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

    protected function checkLevelCodeSubcodeSubsubcodeCombination()
    {
        $checkArr = $this->attainmentsCollection->map(function ($item, $key) {
            return $item->base_subject_name.';'.$item->education_level_id.';'.$item->code.';'.$item->subcode.';'.$item->subsubcode;
        })->toArray();
        if(count($checkArr)!=count(array_unique($checkArr))){
            $diff = array_unique( array_diff_assoc($checkArr,array_unique($checkArr)));
            throw new \Exception('duplicate education_level_id/code/subcode/subsubcode' . json_encode($diff));
        }
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

    protected function fillInParents()
    {
        $this->assignTempIds();
        foreach ($this->attainmentsCollection as $key => $attainmentResource) {
            if(!is_null($attainmentResource->attainment_id)){
                continue;
            }
            if(!is_null($attainmentResource->subcode)&&is_null($attainmentResource->subsubcode)){
                $this->assignParentTempIdToSub($attainmentResource,$key);
            }
            if(!is_null($attainmentResource->subcode)&&!is_null($attainmentResource->subsubcode)){
                $this->assignParentTempIdToSubsub($attainmentResource,$key);
            }
        }
    }

    protected function assignTempIds()
    {
        foreach ($this->attainmentsCollection as $key => $attainmentResource) {
            $attainmentResource->temp_id = $key;
        }
    }

    protected function assignParentTempIdToSub(&$attainmentResource,$key)
    {
        $handled = false;

        while(!$handled){
            $prevAttainment = $this->attainmentsCollection[$key];
            if(is_null($prevAttainment->subcode)){
                $attainmentResource->parent_temp_id = $key;
                $handled = true;
                break;
            }
            $key--;
            if($key<0){
                throw new \Exception('cannot identify parent_temp_id for entry with a subcode.  attainment:' . json_encode($attainmentResource));
            }
        }
    }

    protected function assignParentTempIdToSubsub(&$attainmentResource,$key)
    {
        $handled = false;
        while(!$handled){
            $prevAttainment = $this->attainmentsCollection[$key];
            if(is_null($prevAttainment->subsubcode)){
                $attainmentResource->parent_temp_id = $key;
                $handled = true;
            }
            $key--;
            if($key<0){
                throw new \Exception('cannot identify parent_temp_id for entry with a subsubcode.  attainment:' . json_encode($attainmentResource));
            }
        }
    }

    protected function checkSheetsIntegrity($attainmentManifest)
    {
        $data = $attainmentManifest->getData();
        foreach ($data as $key => $sheet) {
            if(count($sheet)==0){
                break;
            }
            $row = $sheet[0];
            $expectedHeaders = collect($this->expectedHeaders());
            $expectedHeaders->each(function($item,$key2) use($row,$key){
                if(!array_key_exists($item,$row)){
                    throw new \Exception('integrity violation sheet index:'.$key.'; header:' . $item);
                }
            });
        }
    }

    protected function expectedHeaders()
    {
        return [
            'id',
            'base_subject_id',
            'base_subject_name',
            'education_level_name',
            'education_level_id',
            'attainment_id',
            'code',
            'subcode',
            'subsubcode',
            'description',
            'status',
        ];
    }

}
