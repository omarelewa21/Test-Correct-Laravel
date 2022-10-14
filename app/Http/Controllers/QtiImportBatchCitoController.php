<?php

namespace tcCore\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero\QtiResource;
use tcCore\Http\Middleware\RedirectIfAuthenticated;
use tcCore\Http\Requests\QtiImportCitoDataRequest;
use tcCore\Jobs\SendExceptionMail;
use tcCore\Period;
use tcCore\QtiModels\ExcelManifest;
use tcCore\QtiModels\QtiManifest;
use tcCore\QtiModels\QtiResource as Resource;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\TestKind;
use ZanySoft\Zip\Zip;
use ZipArchive;


use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Test;
use tcCore\User;

class QtiImportBatchCitoController extends Controller
{
    protected $dateStamp;
    protected $tests = [];
    protected $basePath;
    protected $currentTest;
    protected $hasErrors = false;
    protected $hasMultiple = false;
    protected $responseAr = [];
    protected $logRef;
    protected $logAr = [];
    protected $hasWerkbladen = false;
    protected $werkbladen = [];
    protected $manifest;
    protected $packageDir;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function data(QtiImportCitoDataRequest $request)
    {
        $teachers = (new Teacher())->getUserObjectsForDistinctTeachers();

        return response()->json([
            'schoolLocations' => SchoolLocation::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'educationLevels' => EducationLevel::orderBy('name')->get(),
            'testKinds' => TestKind::orderBy('name')->get(),
            'periods' => Period::with('schoolYear')->orderBy('name')->get(),
            'teachers' => $teachers,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        set_time_limit(5 * 60);
        ini_set('memory_limit','-1');
        $return = "";

        $errors = [];
        try {
            $this->validate($request, [
//                'zip_file' => 'required|mimes:zip',
            ]);

            //UUID update
            //convert UUIDs to IDs
            $data = $request->all();
            $data['school_location_id'] = SchoolLocation::whereUuid($data['school_location_id'])->first()->getKey();
            $data['author_id'] = User::whereUuid($data['author_id'])->first()->getKey();
            $data['education_level_id'] = EducationLevel::whereUuid($data['education_level_id'])->first()->getKey();
            $data['subject_id'] = Subject::whereUuid($data['subject_id'])->first()->getKey();

            $schoolLocationId = $data['school_location_id'];
            $schoolLocation = SchoolLocation::find($schoolLocationId);
            $schoolYears = $schoolLocation->schoolLocationSchoolYears->map(function ($l) {
                return $l->school_year_id;
            });
            $periods = Period::where('start_date', '<=', Carbon::today())->where('end_date', '>=', Carbon::today())->whereIn('school_year_id', $schoolYears->toArray())->get();
            if ($periods->count() != 1) {
                Log::error(sprintf('no valid period found for school location %s met id %d', $schoolLocation->name, $schoolLocation->id));
                return response()->json(['error' => sprintf('no valid period found for school location %s', $schoolLocation->name)]);
            }
            $period = $periods->first();
            $request->request->add(['period_id' => $period->id]);
            $data['period_id'] = $period->id;

            $file = $request->file('zip_file');


            $fileName = $file->getClientOriginalName();
            $this->basePath = storage_path('app/qti_import');

            $this->requestData = $data;
            $request->merge($data);

            $startDir = $this->dateStamp = date('YmdHis');
            $this->packageDir = sprintf('%s/%s', $this->basePath, $startDir);
            $file->move($this->packageDir, $fileName);

            /**
             * txt file content should look like {"startDir":"chemie","fileName":"scheikunde-havo-vwo.zip"}
             * chemie being the directory which is created in the app/qti_import dir and fileName the zip file to look for
             * make sure to take an earlier created dir as the dir needs to be of the www_data user
             */
            if(pathinfo($fileName,PATHINFO_EXTENSION) === 'txt') {
                $fileData = json_decode(file_get_contents(sprintf('%s/%s',$this->packageDir,$fileName)));
                if(!$fileData){
                    throw new \Exception(sprintf('no json in %s',$fileName));
                }
                $startDir = $fileData->startDir;
                $fileName = $fileData->fileName;
//            $startDir = 'chemie';
//            $fileName = 'scheikunde-havo-vwo.zip';
              $this->packageDir = sprintf('%s/%s', $this->basePath, $startDir);
            }
            //        $storageDir = $dir = sprintf('%s/%s/uploads', $this->basePath, $this->dateStamp);

            $this->checkZipFile(sprintf('%s/%s/%s', $this->basePath, $startDir, $fileName), $startDir, true);
            $return .= implode('', $this->responseAr);
        } catch (\Exception $e) {
            logger($e);
            $logEnd = sprintf('----END %s END----', $this->logRef);
            $this->addToLog($logEnd);
            $this->logAr[] = $logEnd;

            $return .= $e->getMessage();
            $return = sprintf('We hebben helaas een aantal fouten geconstateerd waardoor we de import niet goed konden afronden<br />Je kunt hiervoor contact opnemen met de Teach & Learn Company en daarbij als referentie \'%s\' mee geven<br /><br />%s',
                $this->logRef,
                $return
            );
            dispatch(new SendExceptionMail(implode('\n', $this->logAr), __FILE__, 0, []));
            return response()->json(['error' => $return], 500);
            exit;
        }
        $werkbladenString = '';
        if (count($this->werkbladen) > 0) {
            $werkbladenString = sprintf('<span style="color:red"><strong>Let op:</strong> We hebben de volgende werkbladen gevonden die we niet automatisch konden verwerken:<br />%s</span>', implode('<br />', $this->werkbladen->toArray()));
        }
        $return = sprintf('<h2>De import is succesvol verlopen! <small style="color:red">Vergeet niet om alle toetsen zelf nog eens te controleren!</small></h2>%s%s', $werkbladenString, $return);
        return response()->json(['data' => $return], 200);
    }

    protected function addToLog($data)
    {
        if (!$this->logRef) {
            $this->logRef = sprintf('%s::%s', $this->dateStamp, Str::random(5));
            $logStart = sprintf('----START %s START----', $this->logRef);
            Log::error($logStart);
            $this->logAr[] = $logStart;

        }
        Log::error($data);
        $this->logAr[] = $data;
    }

    protected function handleZipFile()
    {
        foreach ($this->tests as $currentTest) {
            $this->handleTest($currentTest);
        }
    }

    protected function addToResponse($data)
    {
        $this->response[] = $data;
    }

    protected function checkZipFile($file, $startDir, $first = false)
    {
        $zipDir = sprintf('%s/zipdir', $startDir);
        $dir = sprintf('%s/%s', $this->basePath, $zipDir);
        $this->currentTest = (object)[
            'startDir' => $startDir,
            'file' => $file,
            'zipDir' => $zipDir,
            'fullDir' => $dir,
            'requestData' => request()->all(),
            'questions' => [],
            'errorCount' => 0,
        ];


//        \Zipper::make($file)->extractTo($dir);
        Zip::open($file)->extract($dir);
        $dirs = collect(scandir($dir))->filter(function ($file) {
            return $file != '.' && $file !== '..';
        });

        if ($dirs->count() == 1) {
            $newDir = $dirs->first();
            $dir .= '/' . $newDir;
            $this->currentTest->startDir = $dir;
            $this->currentTest->zipDir = $this->currentTest->zipDir . '/' . $newDir;
            $this->currentTest->fullDir = $dir;
            $zipDir .= '/' . $newDir;
        }

        if ($first) {
            $werkBladDir = collect(scandir($dir))->first(function ($file) {
                return (bool)substr_count(strtolower($file), strtolower('werkbladen voor Wintoets en Quayn'));
            });

            if ($werkBladDir) {
                $werkbladenDir = sprintf('%s/%s', $dir, $werkBladDir);
                $this->werkbladen = collect(scandir($werkbladenDir))->filter(function ($file) {
                    return $file != '.' && $file !== '..';
                });
            }

        }
        $excelFile = collect(scandir($dir))->first(function ($file) use ($dir) {
            return strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'xlsx'
                && pathinfo($file, PATHINFO_FILENAME) === 'assessments';
        });
        if ($excelFile) {
            $this->manifest = new ExcelManifest($dir . '/' . $excelFile);
        }
//        logger($this->manifest->getTestListWithResources());


        // check for extra test zip files or is this a test itself
        $zipFiles = collect(scandir($dir))->filter(function ($file) use ($dir) {
            return strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'zip';
        });

        if ($zipFiles->count() > 0) {
            $this->hasMultiple = true;
            $nr = 0;
            $zipFiles->each(function ($zipFile, $i) use ($zipDir, $nr) {
                $nr++;
                $zipFile = sprintf('%s/%s/%s', $this->basePath, $zipDir, $zipFile);
                $newZipDir = sprintf('%s/%s', $zipDir, $i);
                mkdir(sprintf('%s/%s', $this->basePath, $newZipDir));

                $this->checkZipFile($zipFile, $newZipDir);
            });
            $this->afterCheck();
        } else {
            // we may have found a testdir, let's check for a wintoets.xml file
            // check for extra test zip files or is this a test itself
            $testXmlFile = collect(scandir($dir))->first(function ($file) {
                return strtolower(basename($file)) == 'imsmanifest.xml';
            });

            if ($testXmlFile) {
                // found a toets file
                $this->checkTest($testXmlFile);
                // add this test to the list of tests
                $this->tests[] = $this->currentTest;
                $this->currentTest = null;

                if (!$this->hasMultiple) {
                    $this->afterCheck();
                }
            } else {
                $this->addToLog(sprintf('No valid imsmanifest.xml file found'));
                throw new \Exception('No imsmanifest.xml file found');
            }
        }


    }

    protected function sendBufferData($data)
    {
        $this->addToResponse($data);
    }

    protected function afterCheck()
    {
        if ($this->hasErrors) {
            // there are some errors we need to pay attention to
            $errorText = '';
            foreach ($this->tests as $test) {
                if ($test->errorCount > 0) {
                    foreach ($test->questions as $question) {
                        if (count($question->errors)) {
                            $questionBody = (string)$question->question->question_content->question_body;
                            $errorText .= sprintf('Fout(en) in toets: %s<br />Vraag: %s<br />',
                                $test->name,
                                $questionBody);
                            $errorText .= implode('<br />', $question->errors);
                            $errorText .= '<br /><Br />';
                            $this->addToLog(sprintf('file: %s\ntest:%s\nquestion:%s\nerrors:\n%s', $test->file, $test->name, $questionBody, implode('\n', $question->errors)));
                        }
                    }
                }
            }
            throw new \Exception($errorText);
        } else {
            Auth::loginUsingId($this->requestData['author_id']);
        }


    }

    protected function getAbbr($nr)
    {
        if ($nr < 10) {
            $nr = sprintf('0%d', (int)$nr);
        }
        return sprintf('%s%s', $this->getRequestData('abbr'), $nr);
    }

    protected function handleTest($currentTest)
    {
        $this->currentTest = $currentTest;
        $xml_file = sprintf('%s/%s/%s', $this->basePath, $this->currentTest->zipDir, $this->currentTest->xmlFile);
        $xml = simplexml_load_file($xml_file, 'SimpleXMLElement', LIBXML_NOCDATA);


        // add the test
        $test = $this->addTest($xml);

        $questions = $xml->questions;
        // no do it for real as we won't expect any error anymore
        $nr = 0;
        foreach ($questions as $question) {
            $nr++;
            $parts = explode('_', $question['type']);
            $helperName = "";
            foreach ($parts as $part) {
                $helperName .= ucfirst(strtolower($part));
            }
            $helperName .= 'Helper';
            $fullHelper = sprintf('tcCore\Http\Helpers\QtiImporter\\%s', $helperName);
            if (class_exists($fullHelper)) {
                $helper = new $fullHelper;
                try {
                    $helper->handle($question, false, $test, $nr, $this->currentTest->storageDir, $this->basePath);
                } catch (\Exception $e) {
//                    dd($e->getMessage());
                    throw $e;
                    break;
                }
//                echo 'Question added ' . PHP_EOL;
            } else {
                $this->addToLog(sprintf('A helper for %s does not exist', $question['type']));
                throw new \Exception(sprintf('Op dit moment wordt het vraagtype %s nog niet ondersteund, neem contact op met de Teach & Learn Company', $question['type']) . PHP_EOL);
            }

        }
    }

    protected function checkTest($testXmlFile)
    {
        $this->currentTest->xmlFile = $testXmlFile;
        $xml_file = sprintf('%s/%s/%s', $this->basePath, $this->currentTest->zipDir, $this->currentTest->xmlFile);
        $this->currentTest->fullXmlFilePath = $xml_file;
        //    $xml = simplexml_load_file($xml_file, 'SimpleXMLElement', LIBXML_NOCDATA);
        $xml = '';


        // we need to set the auth user to the user we want to import the
        // test for as the rest of the system is depending on this
        Auth::loginUsingId($this->requestData['author_id']);

        foreach ($this->manifest->getTestListWithResources() as $key => $testFromIterator) {

            $test = $this->addTest($xml, [
                'name' => $testFromIterator['test'],
                'scope' => 'cito',
                'level' => $testFromIterator['highest_level'],
            ]);
            $this->currentTest->name = $test->name;


            $dirNumber = false;

            foreach (scandir($this->packageDir . '/zipdir') as $fileOrDir) {
                if ($fileOrDir !== '.' && $fileOrDir !== '..' && is_dir($this->packageDir . '/zipdir/' . $fileOrDir)) {
                    $dirNumber = $fileOrDir;
                }
            }

            if ($dirNumber === false) {
                throw new \Exception('Couldnt find correct zipdir for test: ' . $testFromIterator['test']);
            }


            // first test all
            foreach ($testFromIterator['items'] as $resource) {

                $resource = new Resource(
                    $resource,
                    'imsqti_item_xmlv2p2',
                    sprintf('%s/%s/%s/zipdir/depitems/%s.xml', $this->packageDir, 'zipdir', $dirNumber, str_replace(['ITM-', '_'], ['', ' '], $resource)),
                    '1',
                    'some guid',//$resource['guid'],
                    $test
                );
                $this->instance = (new QtiResource($resource))->handle();
            }
        }
    }

    protected function getSafeQuestionId($question)
    {
        return str_replace('-', '', $question['id']);
    }

    protected function getRequestData($fields)
    {
        if (is_string($fields)) {
            if (array_key_exists($fields, $this->requestData)) {
                return $this->requestData[$fields];
            }
            return '';
        }

        $fillableData = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $this->requestData)) {
                $fillableData[$field] = $this->getRequestData($field);
            }
        }
        return $fillableData;
    }

    protected function getEducationLevelIdFromLevel($level)
    {
        $ar = [
            'vwo' => ['id' => 1, 'year' => 6],
            'havo' => ['id' => 3, 'year' => 5],
            'kb' => ['id' => 6, 'year' => 4],
            'gl/tl' => ['id' => 4, 'year' => 4],
        ];
        if (!array_key_exists($level, $ar)) {
            throw new \Exception(sprintf('Expected level %s unknown in class %s', $level, __CLASS__));
        }
        return $ar[$level];
    }

    protected function addTest($xml, $overrides = [])
    {
        if (isset($overrides['level'])) {
            $details = $this->getEducationLevelIdFromLevel($overrides['level']);
            $overrides['education_level_id'] = $details['id'];
            $overrides['education_level_year'] = $details['year'];

        }

        $fillableData = $this->getRequestData((new Test())->getFillable());

        $shuffle = 0;


        try {

            $test = new Test(array_merge(
                    $fillableData,
                    [
                        'is_open_source_content' => 0,
                        'shuffle' => $shuffle,
                        'introduction' => '',
                        // todo needs change when manifest harbors multiple tests;
                        'external_id' => 'some_id',//$this->manifest->getId(),
                        'published' => false
                    ],
                    $overrides
                )
            );
            $test->setAttribute('author_id', $this->getRequestData('author_id'));
            if ($test->save()) {
                // ok
                return $test;
            } else {
                // error
                throw new \Exception('Fout bij het importeren van toets ' . $overrides['name']);
//                dd('could not add test to the system');
            }
        } catch (\Exception $e) {
            throw $e;
//            dd($e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
