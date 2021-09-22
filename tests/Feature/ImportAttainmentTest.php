<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Attainment;
use tcCore\Http\Controllers\AttainmentImportController;
use Tests\TestCase;


class ImportAttainmentTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_should_import_attainments_without_errors()
    {
        $this->removeOnAttainmentToMakeImportPossible();
        $testXslx = __DIR__.'/../files/import_attainments_default.xlsx';
        $this->assertFileExists($testXslx);

        $attainmentImportController = new AttainmentImportController();
    }

    private function removeOnAttainmentToMakeImportPossible()
    {
        $attainment = Attainment::first();
        $attainment->delete();
    }

}


