<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use tcCore\Attainment;
use tcCore\ExcelAttainmentUpdateOrCreateManifest;
use tcCore\Http\Requests;
use tcCore\LearningGoal;


class LearningGoalImportController extends AttainmentImportController
{
    protected $attainmentsCollection;


    public function upload(Requests\AttainmentUploadRequest $request)
    {
        $excelFile = $request->file('attainments')->storeAs(
            'learning_goals_upload', 'attainments.xlsx'
        );
        return response()->json(['data' => 'Bestand staat op de server. Voer php artisan import:learning_goals uit om te importeren'], 200);
    }



    public function importForUpdateOrCreate(Request $request)
    {
        $excelFile = $request->attainments;
        $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($excelFile);
        $added = 0;
        $updated = 0;
        $updatedIds = [];
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
            $this->checkNoAttainments();
            $this->attainmentsCollection->each(function ($resource) use (&$added,&$updated,&$updatedIds) {
                $parentId = $this->findParent($resource);
                $resource->attainment_id = $parentId;
                if(is_null($resource->id)){
                    $this->createLearningGoal($resource);
                    $added++;
                    return true;
                }
                $this->updateLearningGoal($resource);
                $updatedIds[] = $resource->id;
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



    protected function createLearningGoal(&$resource)
    {
        $data = collect($resource)->toArray();
        $attainment = new LearningGoal();
        $attainment->fill($data);
        $attainment->save();
        $resource->id = $attainment->getKey();
    }

    protected function updateLearningGoal($resource)
    {
        $data = collect($resource)->toArray();

        $attainment = LearningGoal::findOrFail($resource->id);

        $attainment->fill($data);
        $attainment->save();
    }

    protected function checkNoAttainments()
    {
        foreach ($this->attainmentsCollection as $key => $attainmentResource) {
            if(is_null($attainmentResource->attainment_id)){
                continue;
            }
            $learningGoal = LearningGoal::find($attainmentResource->id);
            $attainment = Attainment::find($attainmentResource->id);
            if(!is_null($attainment)){
                throw new \Exception('error trying to import learning goal with attainment parent. id:'.$attainmentResource->id);
            }
            if(is_null($learningGoal)){
                throw new \Exception('error trying to import learning goal with unkown parent. id:'.$attainmentResource->id);
            }
        }
    }
}
