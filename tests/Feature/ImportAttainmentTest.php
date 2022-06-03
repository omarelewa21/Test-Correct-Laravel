<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\ExcelAttainmentUpdateOrCreateManifest;
use tcCore\Http\Controllers\AttainmentImportController;
use tcCore\Http\Controllers\LearningGoalImportController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Question;
use tcCore\TestQuestion;
use tcCore\User;
use Tests\TestCase;
use tcCore\Traits\Dev\OpenQuestionTrait;
use tcCore\Traits\Dev\TestTrait;


class ImportAttainmentTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use OpenQuestionTrait;

    private  $aardrijskundeSubjectId = 36;
    private  $aardrijskundeBaseSubjectId = 16;

    /** @test */
    public function it_should_import_attainments_without_errors()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_default.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(200,$response->getStatusCode());
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_fail_when_no_attainments_are_trashed()
    {
        $this->loginAdmin();
        $testXslx = __DIR__.'/../files/import_attainments_default.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $pass = true;
        try{
            $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        }catch (\Exception $e){
            $pass = false;
            $this->assertEquals('setAttainmentsInactiveNotPresentInImport has not yet run, no inactive records in db',$e->getMessage());
        }
        $this->assertFalse($pass);
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_do_complete_import_without_errors()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_existing_attainments.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        dump($response->getContent());
        $this->assertEquals(200,$response->getStatusCode());

        $this->logoutAdmin();
    }

    /** @test */

    public function showAttainmentsNotPresentInImportTest()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_existing_attainments_08nov21.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->showAttainmentsNotPresentInImport($request);
        dump($response->getContent());
        $this->assertEquals(200,$response->getStatusCode());
    }



    /** @test */

    public function setAttainmentsInactiveNotPresentInImport_file_08_11_21Test()
    {
        $attainment = new Attainment();
        $attainment->base_subject_id = 94;
        $attainment->education_level_id = 1;
        $attainment->attainment_id = null;
        $attainment->code = 'A';
        $attainment->subcode = null;
        $attainment->subsubcode = null;
        $attainment->description = 'CITO attainment';
        $attainment->status = 'ACTIVE';
        $attainment->save();
        $oldAttainmentsCount = Attainment::onlyTrashed()->count();
        $this->assertEquals(0,$oldAttainmentsCount);
        $this->loginAdmin();
        $testXslx = __DIR__.'/../files/import_existing_attainments_08nov21.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->setAttainmentsInactiveNotPresentInImport($request);
        dump($response->getContent());
        $this->assertEquals(200,$response->getStatusCode());
        $oldAttainmentsCount = Attainment::onlyTrashed()->count();
        $this->assertGreaterThan(0,$oldAttainmentsCount);
        $baseSubjects = BaseSubject::where('name','like','%CITO%')->pluck('id')->toArray();
        $oldCitoAttainments = Attainment::onlyTrashed()->whereIn('base_subject_id',$baseSubjects)->count();
        $this->assertEquals(0,$oldCitoAttainments);
    }

    /** @test */
    public function attainments_file_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_existing_attainments_revised.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        dump($response->getContent());
        $this->assertEquals(200,$response->getStatusCode());

        $this->logoutAdmin();
    }

    /** @test */
    public function attainments_file_08_11_21_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_existing_attainments_08nov21.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        dump($response->getContent());
        $this->assertEquals(200,$response->getStatusCode());

        $this->logoutAdmin();
    }

    /** @test */
    public function new_attainments_revised_file_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_new_attainments_revised.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        dump($response->getContent());
        $this->assertEquals(200,$response->getStatusCode());

        $this->logoutAdmin();
    }

    /** @test */
    public function new_attainments_file_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_new_attainments_revised.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        dump($response->getContent());
        $this->assertEquals(200,$response->getStatusCode());

        $this->logoutAdmin();
    }

    /** @test */
    public function new_learning_goals_file_06_04_22_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_new_learning_goals_06_04_22.xlsx';
        $this->learningGoalsXslxIntegrityTest($testXslx);
    }

    /** @test */
    public function new_learning_goals_file_20_04_22_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_new_learning_goals_20_04_22.xlsx';
        $this->learningGoalsXslxIntegrityTest($testXslx);
    }

    /** @test */
    public function new_learning_goals_file_03_06_22_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_new_learning_goals_03_06_22.xlsx';
        $this->learningGoalsXslxIntegrityTest($testXslx);
    }

    /** @test */
    public function new_attainments_file_08_11_21_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_new_attainments_08nov21_v2.xlsx';
        $this->importXslxIntegrityTest($testXslx);
    }

    /** @test */
    public function existing_attainments_file_08_11_21_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_existing_attainments_08nov21.xlsx';
        $this->importXslxIntegrityTest($testXslx);
    }

    /** @test */
    public function existing_attainments_file_08_11_21_vmbo_tl_bio_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_existing_attainments_08nov21.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(200,$response->getStatusCode());
        $attainments = Attainment::all();
        $faultArr = [];
        foreach ($attainments as $attainment){
            $parent = $attainment->attainment;
            if(is_null($parent)){
                continue;
            }
            if($attainment->education_level_id!=$parent->education_level_id){
                $faultArr[] = [ 'id'=>$attainment->getKey(),
                                'base_subject_id'=>$attainment->base_subject_id,
                                'attainment'=>$attainment->attainment_id,
                                'education_level_id'=>$attainment->education_level_id,
                                'code'=>$attainment->code,
                                'subcode'=>$attainment->subcode,
                                'subsubcode'=>$attainment->subsubcode];
            }
            //$this->assertEquals($attainment->education_level_id,$parent->education_level_id);

        }
        dump($faultArr);
        dump(count($faultArr));
        $this->logoutAdmin();
    }

    /** @test */
    public function attainments_08_11_21_parent_integrity_test()
    {
        $this->loginAdmin();
        $attainmenMissingParents = [];
        $testXslx = __DIR__.'/../files/import_existing_attainments_08nov21.xlsx';
        $this->findMissingParentsInFile($testXslx,$attainmenMissingParents);
        $testXslx = __DIR__.'/../files/import_new_attainments_08nov21_v2.xlsx';
        $this->findMissingParentsInFile($testXslx,$attainmenMissingParents);
        //dump($attainmenMissingParents);
        $missingParentsUnique = [];
        foreach ($attainmenMissingParents as $attainmenMissingParent){
                $missingParentsUnique[] = $this->getMissingParent($attainmenMissingParent);
        }
        $missingParentsUnique = array_map("unserialize", array_unique(array_map("serialize", $missingParentsUnique)));
        dump($missingParentsUnique);

        $this->assertCount(0,$attainmenMissingParents);
        $this->logoutAdmin();
    }

    /** @test */
    public function new_attainments_file_08_11_21_vmbo_tl_bio_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_new_attainments_08nov21_v2.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(200,$response->getStatusCode());
        $attainments = Attainment::all();
        $faultArr = [];
        foreach ($attainments as $attainment){
            $parent = $attainment->attainment;
            if(is_null($parent)){
                continue;
            }
            if($attainment->education_level_id!=$parent->education_level_id||$attainment->base_subject_id!=$parent->base_subject_id){
                $faultArr[] = [ 'id'=>$attainment->getKey(),
                    'base_subject_id'=>$attainment->base_subject_id,
                    'attainment'=>$attainment->attainment_id,
                    'education_level_id'=>$attainment->education_level_id,
                    'code'=>$attainment->code,
                    'subcode'=>$attainment->subcode,
                    'subsubcode'=>$attainment->subsubcode];
            }
            //$this->assertEquals($attainment->education_level_id,$parent->education_level_id);

        }
        dump($faultArr);
        dump(count($faultArr));
        $this->assertCount(0,$faultArr);
        $this->logoutAdmin();
    }

    /** @test */
    public function missingActiveFieldTest()
    {
        $this->loginAdmin();
        $missingActives = [];
        $testXslx = __DIR__.'/../files/import_existing_attainments_08nov21.xlsx';
        $this->assertFileExists($testXslx);
        $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($testXslx);
        $this->attainmentsCollection = collect($attainmentManifest->getAttainmentResources());

        foreach ($this->attainmentsCollection as $attainmentResource) {
            if(strtoupper($attainmentResource->status)!='ACTIVE'){
                $missingActives[] = [
                    'base_subject_id'=>$attainmentResource->base_subject_id,
                    'education_level_id'=>$attainmentResource->education_level_id,
                    'code'=>$attainmentResource->code,
                    'subcode'=>$attainmentResource->subcode,
                    'subsubcode'=>$attainmentResource->subsubcode];
            }
        }
        $testXslx = __DIR__.'/../files/import_new_attainments_08nov21_v2.xlsx';
        $this->assertFileExists($testXslx);
        $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($testXslx);
        $this->attainmentsCollection = collect($attainmentManifest->getAttainmentResources());

        foreach ($this->attainmentsCollection as $attainmentResource) {
            if(strtoupper($attainmentResource->status)!='ACTIVE'){
                $missingActives[] = [
                    'base_subject_id'=>$attainmentResource->base_subject_id,
                    'education_level_id'=>$attainmentResource->education_level_id,
                    'code'=>$attainmentResource->code,
                    'subcode'=>$attainmentResource->subcode,
                    'subsubcode'=>$attainmentResource->subsubcode];
            }
        }
        dump($missingActives);
        $this->assertCount(0,$missingActives);
        $this->logoutAdmin();
    }

    /** @test */
    public function database_attainments_integrity_test()
    {
        $this->loginAdmin();
        $attainments = Attainment::all();
        $faultArr = [];
        foreach ($attainments as $attainment){
            $parent = $attainment->attainment;
            if(is_null($parent)){
                continue;
            }
            if($attainment->education_level_id!=$parent->education_level_id||$attainment->base_subject_id!=$parent->base_subject_id){
                $faultArr[] = [ 'id'=>$attainment->getKey(),
                    'base_subject_id'=>$attainment->base_subject_id,
                    'attainment'=>$attainment->attainment_id,
                    'education_level_id'=>$attainment->education_level_id,
                    'code'=>$attainment->code,
                    'subcode'=>$attainment->subcode,
                    'subsubcode'=>$attainment->subsubcode];
            }
            //$this->assertEquals($attainment->education_level_id,$parent->education_level_id);

        }
        dump($faultArr);
        dump(count($faultArr));
        $this->assertCount(0,$faultArr);
        $this->logoutAdmin();
    }

//    /** @test */
//    public function database_vs_excel_test()
//    {
//        $this->loginAdmin();
//        $this->inactivateAttainmentToMakeImportPossible();
//        $testXslx = __DIR__.'/../files/import_existing_attainments_08nov21.xlsx';
//        $this->assertFileExists($testXslx);
//        $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($testXslx);
//        $this->attainmentsCollection = collect($attainmentManifest->getAttainmentResources());
//        $ids = [];
//        foreach ($this->attainmentsCollection as $attainmentResource) {
//            if(is_null($attainmentResource->id)){
//                continue;
//            }
//            $attainment = Attainment::where('base_subject_id', $attainmentResource->base_subject_id)
//                ->where('education_level_id', $attainmentResource->education_level_id)
//                ->where('code', $attainmentResource->code)
//                ->where('description', $attainmentResource->description)->first();
//            if(is_null($attainment)){
//                continue;
//            }
//            if($attainment->getKey()==$attainmentResource->id){
//                continue;
//            }
//            $ids[] = $attainmentResource->id;
//        }
//        dump($ids);
//        $this->assertCount(0,$ids);
//        $this->logoutAdmin();
//    }

    /** @test */
    public function existing_attainments_file_08_11_21_double_ids_integrity_test()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_existing_attainments_08nov21.xlsx';
        $this->assertFileExists($testXslx);
        $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($testXslx);
        $this->attainmentsCollection = collect($attainmentManifest->getAttainmentResources());
        $duplicateIds = [];
        $ids = [];
        foreach ($this->attainmentsCollection as $attainmentResource) {
            if(is_null($attainmentResource->id)){
                continue;
            }
            if(in_array($attainmentResource->id,$ids)){
                $duplicateIds[] = $attainmentResource->id;
            }
            $ids[] = $attainmentResource->id;
        }
        dump($duplicateIds);
        $this->assertCount(0,$duplicateIds);
        $this->logoutAdmin();
    }



    /** @test */
    public function it_should_import_attainments_and_store_them_in_db()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $attainment = Attainment::where('description','like','%concepten DNA en eiwitsynthese%')->first();
        $this->assertNull($attainment);
        $attainment = Attainment::where('description','Basisvaardigheden')->where('code','AK/K/2')->where('education_level_id','7')->first();
        $this->assertNull($attainment);
        $attainment3 = Attainment::where('description','like','%communiceren, samenwerken en informatie verwerven en verwerken%')->where('code','AK/K/2')->first();
        $this->assertNull($attainment3);
        $testXslx = __DIR__.'/../files/import_attainments_default.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(200,$response->getStatusCode());
        $attainment = Attainment::where('description','like','%concepten DNA en eiwitsynthese%')->first();
        $this->assertNotNull($attainment);
        $attainment2 = Attainment::where('description','Basisvaardigheden')->where('code','AK/K/2')->where('education_level_id','7')->first();
        $this->assertNotNull($attainment2);
        $attainment3 = Attainment::where('description','like','%communiceren, samenwerken en informatie verwerven en verwerken%')->where('code','AK/K/2')->where('education_level_id','7')->first();
        $this->assertNotNull($attainment3);
        $this->assertEquals($attainment2->id,$attainment3->attainment_id);
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_fail_on_corrupt_base_subject_id()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_corrupt_base_subject.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(500,$response->getStatusCode());
        $this->assertStringContainsString('base_subject_is with wrong name. base_subject_id',$response->getContent());
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_fail_on_corrupt_education_level_id()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_corrupt_eduction_level.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(500,$response->getStatusCode());
        $this->assertStringContainsString('education_level_id with wrong name',$response->getContent());
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_fail_on_missing_code()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_missing_code.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(500,$response->getStatusCode());
        $this->assertStringContainsString('missing code attainment',$response->getContent());
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_fail_on_corrupt_file()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_corrupt_sheet.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(500,$response->getStatusCode());
        $this->assertStringContainsString('integrity violation sheet index',$response->getContent());
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_fail_on_corrupt_file_header()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_corrupt_sheet_header.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(500,$response->getStatusCode());
        $this->assertStringContainsString('integrity violation sheet index',$response->getContent());
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_fail_on_corrupt_inheritance()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_corrupt_inheritance.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(500,$response->getStatusCode());
        $this->assertStringContainsString('duplicate education_level_id',$response->getContent());
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_fail_add_attainment_id_on_subsubcode_attainments()
    {
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_existing_attainments.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(200,$response->getStatusCode());
        $attainments = Attainment::whereNotNull('subsubcode')->get();
        foreach ($attainments as $attainment){
            $this->assertNotNull($attainment->attainment_id);
        }

        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_import_attainments_and_repair_questions()
    {
        $questionId = $this->createAardrijkskundeQuestion();
        $this->addAttainmentsToQuestion($questionId,[95,700]);
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_for_questions_integrity.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(200,$response->getStatusCode());
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_import_attainments_and_repair_questions_fill_missing()
    {
        $questionId = $this->createAardrijkskundeQuestion();
        $this->addAttainmentsToQuestion($questionId,[3042,3047]);
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_for_questions_integrity.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(200,$response->getStatusCode());
        $question = Question::find($questionId);
        $this->assertCount(3,$question->getQuestionInstance()->attainments);
        $this->logoutAdmin();
    }

    /** @test */
    public function it_should_import_attainments_and_repair_questions_remove_rogue()
    {
        $questionId = $this->createAardrijkskundeQuestion();
        $this->addAttainmentsToQuestion($questionId,[1401,1402]);
        $this->loginAdmin();
        $this->inactivateAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_for_questions_integrity_with_rogue.xlsx';
        $this->assertFileExists($testXslx);
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        $this->assertEquals(200,$response->getStatusCode());
        $question = Question::find($questionId);
        $this->assertCount(3,$question->getQuestionInstance()->attainments);
        $this->logoutAdmin();
    }


    private function inactivateAttainmentToMakeImportPossible()
    {
        $attainment = Attainment::create([  'base_subject_id' => 1,
                                            'education_level_id' => 1,
                                            'code' => 'A',
                                            'subcode' => 1,
                                            'description' => 'Dummy',
                                            'status' => 'ACTIVE'
                                        ]);
        $attainment->delete();
    }

    private function loginAdmin()
    {
        $user = User::whereHas(
            'roles', function($q){
            $q->where('name', 'Administrator');
        }
        )->first();
        $this->assertNotNull($user);
        Auth::login($user);
    }

    private function logoutAdmin()
    {
        Auth::logout();
    }

    private function createAardrijkskundeQuestion()
    {
        $test = $this->createNewTest();
        $response = $this->post(
            '/api-c/test_question',
            static::getTeacherOneAuthRequestData([
                'question'               => '<p>aa</p>',
                'answer'                 => '<p>bb</p>',
                'type'                   => 'OpenQuestion',
                'score'                  => 5,
                'order'                  => 0,
                'subtype'                => 'short',
                'maintain_position'      => 0,
                'discuss'                => 1,
                'decimal_score'          => 0,
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => 'R',
                'test_id'                => $test['id'],
                'closeable'              => 0,
                'subject_id'             => $this->aardrijskundeSubjectId,
            ])
        );
        $response->assertStatus(200);
        $testQuestion = TestQuestion::find($response->decodeResponseJson()['id']);
        return $testQuestion->question->getKey();
    }

    private function createNewTest($overrides = [])
    {
        $attributes = array_merge([
            'name'                   => 'Test Title 1abc',
            'abbreviation'           => 'TT',
            'test_kind_id'           => '3',
            'subject_id'             =>  $this->aardrijskundeSubjectId,
            'education_level_id'     => '1',
            'education_level_year'   => '1',
            'period_id'              => '1',
            'shuffle'                => '0',
            'is_open_source_content' => '1',
            'introduction'           => 'Hello this is the intro txt',
        ], $overrides);

        $response = $this->post(
            'api-c/test',
            static::getTeacherOneAuthRequestData($attributes)
        );

        return $response->decodeResponseJson();
    }

    private function addAttainmentsToQuestion($questionId,$attainmentIds):void
    {
        $question = Question::find($questionId);
        foreach ($attainmentIds as $attainmentId){
            $attainment = Attainment::find($attainmentId);
            $question->getQuestionInstance()->attainments()->attach($attainment);
        }
    }

    private function checkParentofSub(&$attainmentResource,$key)
    {
        $handled = false;

        while(!$handled){
            $prevAttainment = $this->attainmentsCollection[$key];
            if(is_null($prevAttainment->subcode)&&($prevAttainment->base_subject_id==$attainmentResource->base_subject_id)&&($prevAttainment->education_level_id==$attainmentResource->education_level_id)&&(trim($prevAttainment->code)==trim($attainmentResource->code))){
                $attainmentResource->parent_temp_id = $key;
                $handled = true;
                break;
            }
            $key--;
            if($key<0){
                return false;
            }
        }
        return true;
    }

    private function checkParentofSubsub(&$attainmentResource,$key)
    {
        $handled = false;
        while(!$handled){
            $prevAttainment = $this->attainmentsCollection[$key];
            if(is_null($prevAttainment->subsubcode)&&($prevAttainment->base_subject_id==$attainmentResource->base_subject_id)&&($prevAttainment->education_level_id==$attainmentResource->education_level_id)&&(trim($prevAttainment->subcode)==trim($attainmentResource->subcode))){
                $attainmentResource->parent_temp_id = $key;
                $handled = true;
            }
            $key--;
            if($key<0){
                return false;
            }
        }
        return true;
    }

    private function findMissingParentsInFile($testXslx,&$missingParents)
    {
        $this->assertFileExists($testXslx);
        $attainmentManifest = new ExcelAttainmentUpdateOrCreateManifest($testXslx);
        $this->attainmentsCollection = collect($attainmentManifest->getAttainmentResources());

        foreach ($this->attainmentsCollection as $key => $attainmentResource) {
            $attainmentResource->temp_id = $key;
        }
        foreach ($this->attainmentsCollection as $key => $attainmentResource) {
            if(!is_null($attainmentResource->attainment_id)){
                continue;
            }
            if(!is_null($attainmentResource->subcode)&&is_null($attainmentResource->subsubcode)&&!$this->checkParentofSub($attainmentResource,$key)){
                $missingParents[] = $attainmentResource;
            }
            if(!is_null($attainmentResource->subcode)&&!is_null($attainmentResource->subsubcode)&&!$this->checkParentofSubsub($attainmentResource,$key)){
                $missingParents[] = $attainmentResource;
            }
        }
    }

    private function getMissingParent($attainmenMissingParent)
    {
        $subcode = null;
        if(!is_null($attainmenMissingParent->subsubcode)) {
            $subcode = $attainmenMissingParent->subcode;
        }
        $missingParent = [ 'base_subject_id'=>$attainmenMissingParent->base_subject_id,
            'base_subject_name'=> $attainmenMissingParent->base_subject_name,
            'education_level_id'=> $attainmenMissingParent->education_level_id,
            'education_level_name'=> $attainmenMissingParent->education_level_name,
            'code'=> $attainmenMissingParent->code,
            'subcode'=> $subcode,
            'subsubcode'=> null,
        ];
        return $missingParent;
    }

    /**
     * @param string $testXslx
     */
    private function importXslxIntegrityTest(string $testXslx): void
    {
        $this->assertFileExists($testXslx);
        $request = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user' => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new AttainmentImportController())->importForUpdateOrCreate($request);
        dump($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $attainments = Attainment::whereNotNull('subsubcode')->get();
        foreach ($attainments as $attainment) {
            $this->assertNotNull($attainment->attainment_id);
        }
        $this->logoutAdmin();
    }

    /**
     * @param string $testXslx
     */
    private function learningGoalsXslxIntegrityTest(string $testXslx): void
    {
        $this->assertFileExists($testXslx);
        $request = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user' => Auth::user()->username,
            'attainments' => $testXslx,
        ];
        $request->merge($params);
        $response = (new LearningGoalImportController())->importForUpdateOrCreate($request);
        dump($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());

        $this->logoutAdmin();
    }
}


