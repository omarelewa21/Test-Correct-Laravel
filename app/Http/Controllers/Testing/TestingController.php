<?php namespace tcCore\Http\Controllers\Testing;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Controllers\Controller;
use tcCore\Commands\DatabaseImport;
use tcCore\Commands\SeleniumTest;

class TestingController extends Controller {

    public function store() {
        if (request('flag') !== 'testdb') {
            $file =  database_path(sprintf('seeds/testing/db_dump_%s.sql', request('flag')));
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


        return Response::make("error");
    }

    public function seleniumToggle() {
        $toggle = request('toggle');

        if ($toggle == 'true') {
            SeleniumTest::applySeleniumEnvFile();
        } else {
            SeleniumTest::restoreEnvFile();
        }

        return Response::make("ok");
    }

    public function seleniumState() {
        $env = env('SELENIUM_TEST', false);

        if ($env != 1) {
            return Response::make("false");
        } else {
            return Response::make("true");
        }

    }

}