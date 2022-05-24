<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\ExcelAttainmentManifest;
use tcCore\ExcelAttainmentUpdateOrCreateManifest;
use tcCore\Http\Helpers\DefaultSubjectsAndSectionsImportHelper;
use tcCore\Http\Helpers\SchoolImportHelper;
use tcCore\Http\Requests;
use tcCore\Lib\Repositories\AverageRatingRepository;
use tcCore\Lib\Repositories\SchoolClassRepository;
use tcCore\Lib\User\Factory;
use tcCore\QuestionAttainment;
use tcCore\SchoolClass;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\SchoolClassesStudentImportRequest;
use tcCore\SchoolLocation;
use tcCore\User;
use Maatwebsite\Excel\Facades\Excel;

class SchoolAndSchoolLocationsImportController extends Controller
{
    protected $attainmentsCollection;

    /**
     * Import attainments.
     * @param SchoolClassesStudentImportRequest $request
     * @return
     */
    public function import(Requests\SchoolAndSchoolLocationsImportRequest $request)
    {
        $excelFile = $request->file('file');
        $basePath = 'app/school_location_import';

        $filePath = storage_path(sprintf('%s/%s.%s',$basePath, date("YmdHis"),$excelFile->extension()));
        copy($excelFile->path(),$filePath);


        $helper = new SchoolImportHelper();
        try {
            $helper->setFilePath($filePath);
            $excelFile = null;
            $helper->handleImport();
        } catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
        return response()->json(['data' => 'Scholen zijn toegevoegd en school locaties klaargezet in de queue om verwerkt te worden'], 200);
    }


}
