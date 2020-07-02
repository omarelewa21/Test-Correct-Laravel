<?php namespace tcCore\Http\Controllers\Testing;

use Illuminate\Support\Facades\Response;
use tcCore\Http\Controllers\Controller;
use tcCore\Commands\DatabaseImport;

class TestingController extends Controller {

    public function store() {
        $file =  database_path(sprintf('seeds/testing/db_dump_%s.sql', request('flag')));
        
        if (file_exists($file)) {

            DatabaseImport::importSql(database_path('seeds/dropAllTablesAndViews.sql'));

            DatabaseImport::importSql($file);

            DatabaseImport::migrate();

            return Response::make("ok");
        }


        return Response::make("error");
    }

}
