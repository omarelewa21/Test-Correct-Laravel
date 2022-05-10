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
use tcCore\Http\Helpers\ExcelDefaultSubjectsAndSectionsImportHelper;
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

class DefaultSubjectsAndSectionsController extends Controller
{
    protected $attainmentsCollection;

    /**
     * Import attainments.
     * @param SchoolClassesStudentImportRequest $request
     * @return
     */
    public function import(Requests\DefaultSubjectsAndSectionsImportRequest $request)
    {
        $excelFile = $request->file('file');
        $basePath = 'app/default_subjects_and_sections';
        $storagePath = storage_path($basePath);

        $filename = sprintf('%s.%s',date("YmdHis"), $excelFile->extension());
        $excelFile->move($storagePath, $filename);

        $helper = new ExcelDefaultSubjectsAndSectionsImportHelper();
        try {
            $helper->setFilePath(sprintf('%s/%s', $basePath, $filename))->handleImport();
        } catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
        return response()->json(['data' => 'De default subjects en vakken zijn toegevoegd'], 200);
    }


}
