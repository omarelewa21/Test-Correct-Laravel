<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\Http\Controllers\AttainmentImportController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\User;
use Tests\TestCase;


class ImportAttainmentTest extends TestCase
{
    use DatabaseTransactions;

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
        $oldAttainmentsCount = Attainment::where('status','OLD')->count();
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
        $oldAttainmentsCount = Attainment::where('status','OLD')->count();
        $this->assertGreaterThan(0,$oldAttainmentsCount);
        $baseSubjects = BaseSubject::where('name','like','%CITO%')->pluck('id')->toArray();
        $oldCitoAttainments = Attainment::where('status','OLD')->whereIn('base_subject_id',$baseSubjects)->count();
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
    public function new_attainments_file_08_11_21_integrity_test()
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
        dump($response->getContent());
        $this->assertEquals(200,$response->getStatusCode());

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

    private function inactivateAttainmentToMakeImportPossible()
    {
        $attainment = Attainment::create([  'base_subject_id' => 1,
                                            'education_level_id' => 1,
                                            'code' => 'A',
                                            'subcode' => 1,
                                            'description' => 'Dummy',
                                            'status' => 'OLD'
                                        ]);
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

}


