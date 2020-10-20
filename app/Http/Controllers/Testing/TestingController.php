<?php namespace tcCore\Http\Controllers\Testing;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Controllers\Controller;
use tcCore\Commands\DatabaseImport;
use tcCore\Commands\SeleniumTest;

class TestingController extends Controller {

    public function store() {
        if (!app()->environment('production') && $this->isSeleniumMode()) {
            if (request('flag') !== 'testdb') {
                $file = database_path(sprintf('seeds/testing/db_dump_%s.sql', request('flag')));
            } else {
                $file = database_path('seeds/testdb.sql');
            }

            if (file_exists($file)) {

                DatabaseImport::importSql(database_path('seeds/dropAllTablesAndViews.sql'));

                DatabaseImport::importSql($file);

                DatabaseImport::migrate();

                DatabaseImport::addRequiredDatabaseData();

                return Response::make("ok");
            }
        }

        return Response::make("error");
    }

    public function seleniumToggle() {
        if (!app()->environment('production')) {
            $toggle = request('toggle');

            if ($toggle == 'true') {
                if(!$this->isSeleniumMode()) { // only do this if not already in selenium mode
                    SeleniumTest::applySeleniumEnvFile();
                }
            } else {
                if($this->isSeleniumMode()) { // only do this if in selenium mode
                    SeleniumTest::restoreEnvFile();
                }
            }

            return Response::make("ok");
        }

        return Response::make('error');
    }

    public function seleniumState() {
        if (!app()->environment('production')) {

            if($this->isSeleniumEnv()){
                return Response::make("true");
            }

            return Response::make("false");
        }

        return Response::make('error');
    }

    protected function isSeleniumMode()
    {
        return (bool) env('SELENIUM_TEST', false) == 1;
    }

}
