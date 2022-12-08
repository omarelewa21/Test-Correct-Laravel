<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use tcCore\FileManagement;
use tcCore\FileManagementStatus;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateClassUploadRequest;
use tcCore\Http\Requests\CreateTestUploadRequest;
use tcCore\Http\Requests\ShowFileManagementRequest;
use tcCore\Http\Requests\UpdateFileManagementRequest;
use tcCore\Jobs\SendToetsenbakkerInviteMail;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\Period;
use tcCore\Question;
use tcCore\QuestionAuthor;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\Http\Requests\CreateTeacherRequest;
use tcCore\Http\Requests\UpdateTeacherRequest;
use tcCore\Http\Requests\DuplicateFileManagementTestRequest;
use tcCore\Test;
use tcCore\TestAuthor;
use tcCore\TestQuestion;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class FileManagementController extends Controller
{
    const STATUS_ID_APPROVED = 7;

    protected function getBasePath()
    {
        return storage_path('app/files');
    }

    public function getStatuses()
    {
        return Response(FileManagementStatus::all(), 200);
    }

    public function getFormId()
    {
        $formId = Str::uuid();
        $fileManagement = FileManagement::where('form_id',$formId)->first();
        if(is_null($fileManagement)){
            return Response($formId, 200);
        }
        return $this->getFormId();
    }

    protected function sendInvite(FileManagement $fileManagement)
    {
        Queue::push(new SendToetsenbakkerInviteMail($fileManagement->getKey()));
//        dispatch_now(new SendToetsenbakkerInviteMail($fileManagement->getKey()));
    }

    public function update(UpdateFileManagementRequest $request, FileManagement $fileManagement)
    {
        $fileManagement->fill($request->validated());
        $typeDetails = $fileManagement->typedetails;
        $originalEmail = property_exists($typeDetails, 'invite') ? $typeDetails->invite : '';
        $typeDetails->colorcode = $request->get('colorcode');
        $typeDetails->invite = $request->get('invite');
        $typeDetails->test_upload_additional_option = $request->get('test_upload_additional_option');
        $fileManagement->typedetails = $typeDetails;
        if ($fileManagement->save() !== false) {
            $email = $request->get('invite');
            if ($originalEmail != $email && strlen($email) > 3) {
                $this->sendInvite($fileManagement);
            }

            return Response::make($fileManagement, 200);
        } else {
            return Response::make('Het is helaas niet gelukt om de status aan te passen.', 500);
        }
    }

    /**
     * Offers a download to the specified file from storage.
     *
     * @param file
     * @return Response
     */
    public function download(Requests\DownloadFileManagementRequest $request, FileManagement $fileManagement)
    {
        return Response::download(sprintf('%s/%s/%s', $this->getBasePath(), $fileManagement->schoolLocation->getKey(), $fileManagement->name));
    }

    public function storeTestUpload(CreateTestUploadRequest $request, SchoolLocation $schoolLocation)
    {

        $form_id = $request['form_id'];

        DB::beginTransaction();
        try {
            if ($request->isForm()) {
                $result = $this->handleFormSubmission($schoolLocation, $request, $form_id);
            } else {
                $result = $this->handleFileUpload($request, $schoolLocation, $form_id);
            }
        } catch (\Exception $e) {

            DB::rollback();
            $errorMsg = 'Het is helaas niet gelukt om de formulier gegevens te verwerken, probeer het nogmaals.';
            Bugsnag::notifyException($e);
            return Response::make($errorMsg, 500);

        }
        DB::commit();
        return Response::make($result, 200);
    }

    public function storeClassUpload(CreateClassUploadRequest $request, SchoolLocation $schoolLocation)
    {

        $file = $request->file('file');

        $origfileName = $file->getClientOriginalName();

        $fileName = sprintf('%s-%s.%s', date('YmdHis'), Str::slug($request->get('class')), pathinfo($origfileName, PATHINFO_EXTENSION));

        $file->move(sprintf('%s/%s', $this->getBasePath(), $schoolLocation->getKey()), $fileName);

        $fileManagement = new FileManagement();

        $data = [
            'id'                 => Str::uuid(),
            'origname'           => $origfileName,
            'name'               => $fileName,
            'user_id'            => Auth::user()->getKey(),
            'school_location_id' => $schoolLocation->getKey(),
            'type'               => 'classupload',
            'typedetails'        => [
                'class'                => $request->get('class'),
                'education_level_year' => $request->get('education_level_year'),
                'education_level_id'   => $request->get('education_level_id'),
                'is_main_school_class' => $request->get('is_main_school_class'),
                'subject'              => $request->get('subject'),
            ],
        ];

        $fileManagement->fill($data);

        if ($fileManagement->save() !== false) {
            return Response::make($fileManagement, 200);
        } else {
            return Response::make('Het is helaas niet gelukt om de upload te verwerken, probeer het nogmaals.', 500);
        }
    }

    /**
     * Display a listing of files.
     *
     * @return Response
     */
    public function index(Requests\IndexFileManagementRequest $request)
    {

        $builder = FileManagement::filtered(Auth::user(),$request->get('filter', []), $request->get('order', []));

        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($builder->get(), 200);
                break;
            case 'paginate':
            default:
                return Response::make($builder->paginate(15), 200);
                break;
        }
    }

    /**
     * Display the specified file.
     * @return
     */
    public function show(ShowFileManagementRequest $request, FileManagement $fileManagement)
    {
        $fileManagement->load(['user', 'handler', 'status', 'children', 'schoolLocation']);

        $user = Auth::user();
        if ($user->hasRole('Account manager') || $user->isToetsenbakker()) {
            $fileManagement->append('test_upload_additional_options');
            $fileManagement->statuses = FileManagementStatus::all();
        } else if ($user->hasRole('Teacher')) {
            if ($user->school_location_id != $fileManagement->school_location_id) {
                return Response::make('not allowed', 403);
            }
        }

        return Response::make($fileManagement, 200);
    }

    /**
     * @param SchoolLocation $schoolLocation
     * @param CreateTestUploadRequest $request
     * @param $form_id
     * @return FileManagement
     */
    private function handleFormSubmission(SchoolLocation $schoolLocation, CreateTestUploadRequest $request, $form_id): FileManagement
    {
        $data = [
            'id'                 => $form_id,
            'origname'           => $request->get('name'),
            'subject'            => $request->get('subject'),
            'name'               => $request->get('name'),
            'user_id'            => Auth::user()->getKey(),
            'school_location_id' => $schoolLocation->getKey(),
            'test_name'          => $request->get('name'),
            'type'               => 'testupload',
            'typedetails'        => [// request data?
                'test_kind_id'         => $request->get('test_kind_id'),
                'education_level_year' => $request->get('education_level_year'),
                'education_level_id'   => $request->get('education_level_id'),
                'subject'              => $request->get('subject'),
                'name'                 => $request->get('name'),
                'correctiemodel'       => $request->get('correctiemodel'),
                'multiple'             => $request->get('multiple'),
                'form_id'              => $request->get('form_id')
            ],
            'form_id'           => $form_id,
        ];

        $main = new FileManagement();

        $main->fill($data);

        $main->save();

        $parent_id = $main->getKey();

        FileManagement::where('parent_id', $form_id)->update(['parent_id' => $parent_id, 'typedetails' => $data['typedetails']]);

        $stored_files = FileManagement::where('parent_id', $parent_id)->get();

        $storage_path = sprintf('%s/%s', $this->getBasePath(), $schoolLocation->getKey());

        // add subject to filename

        foreach ($stored_files as $file) {

            $new_name = sprintf('%s-%s-%s-%s.%s', date('YmdHis'), Str::random(5), Str::slug($request->get('name')), $request->get('subject'), pathinfo($file->origname, PATHINFO_EXTENSION));

            rename($storage_path . '/' . $file->name, $storage_path . '/' . $new_name);

            FileManagement::where('name', $file->name)->update(['name' => $new_name]);
        }
        return $main;
    }

    /**
     * @param CreateTestUploadRequest $request
     * @param SchoolLocation $schoolLocation
     * @param $form_id
     * @return FileManagement
     */
    private function handleFileUpload(CreateTestUploadRequest $request, SchoolLocation $schoolLocation, $form_id): FileManagement
    {
        // there is only one file at a time
        $file = $request->file('files')[0];

        // file data is temporary placeholder

        $data = [
            'id'                 => Str::uuid(),
            'origname'           => '',
            'name'               => '',
            'user_id'            => Auth::user()->getKey(),
            'school_location_id' => $schoolLocation->getKey(),
            'type'               => 'testupload',
            'typedetails'        => [],
            'form_id'            => $form_id
        ];

        $parent = FileManagement::where('form_id',$form_id)->whereNull('parent_id')->first();
        $child = new FileManagement();

        $data['id'] = Str::uuid();

        $data['parent_id'] = $form_id;

        $origfileName = $file->getClientOriginalName();
        $nameOnly = explode('.', $origfileName)[0];

        if(!is_null($parent)){
            $fileName = sprintf('%s-%s-%s-%s.%s', date('YmdHis'), Str::random(5), Str::slug($parent->name), $parent->subject, pathinfo($origfileName, PATHINFO_EXTENSION));
            $data['typedetails'] = $parent->typedetails;
        }else{
            $fileName = sprintf('%s-%s-%s.%s', date('YmdHis'), Str::random(5), $nameOnly, pathinfo($origfileName, PATHINFO_EXTENSION));
        }
        $file->move(sprintf('%s/%s', $this->getBasePath(), $schoolLocation->getKey()), $fileName);

        $data['origname'] = $origfileName;
        $data['name'] = $fileName;

        $child->fill($data);
        $child->save();

        return $child;
    }

    public function duplicateTestToSchool(DuplicateFileManagementTestRequest $request, FileManagement $fileManagement)
    {
        if($fileManagement->file_management_status_id !== self::STATUS_ID_APPROVED) {
            return response()->json(['errors' => ['not allowed']])->setStatusCode(403);
        }

        $test_id = $fileManagement->test_id ?? 238;
        $test = Test::find($test_id);
        //todo duplicate test from $fileManagement->test_id;

        ActingAsHelper::getInstance()->setUser($fileManagement->user);

        $duplicateTest = $test->duplicate([]);
        $duplicateTest->refresh();
        $duplicateTest->subject_id = $fileManagement->subject_id;
        $duplicateTest->period_id = PeriodRepository::getCurrentPeriod()->getKey();
        $duplicateTest->author_id = $fileManagement->user_id;
        $duplicateTest->testAuthors()->forceDelete();
        TestAuthor::addAuthorToTest($duplicateTest, $fileManagement->user_id);
        $duplicateTest->owner_id = $fileManagement->school_location_id;

        if(!$duplicateTest->save()) {
            return response()->json(['errors' => 'Failed to save duplicated test'])->setStatusCode(500);
        }

        $this->disableAddToDatabaseSettingForAllTestQuestions($duplicateTest);
        $this->createQuestionAuthorRecordsForAllQuestions($duplicateTest, $fileManagement);

        return response()->json(['status' => 'success'])->setStatusCode(200);
    }

    private function createAllQuestionsOfTestQueryBuilder(Test $test)
    {
        $testQuestionsQuery = TestQuestion::select('question_id as id')->whereTestId($test->getKey());
        $groupQuestionQuestionsQuery = GroupQuestionQuestion::select('question_id as id')->whereIn('group_question_id', $testQuestionsQuery);

        return $testQuestionsQuery->unionAll($groupQuestionQuestionsQuery);
    }

    private function disableAddToDatabaseSettingForAllTestQuestions(Test $test) : bool
    {
        $allQuestionIdsQuery = $this->createAllQuestionsOfTestQueryBuilder($test);

        $queryResult = Question::whereIn('id', $allQuestionIdsQuery)
            ->update(['add_to_database_disabled' => 1]);

        return $queryResult > 0;
    }

    private function createQuestionAuthorRecordsForAllQuestions(Test $test, FileManagement $fileManagement)
    {
        $allQuestionIds = $this->createAllQuestionsOfTestQueryBuilder($test)->get();

        //force delete wrongfully duplicated records
        $questionAuthors = QuestionAuthor::whereIn('question_id', $allQuestionIds)->forceDelete();

        //create new records for the recipient teacher
        QuestionAuthor::insert($allQuestionIds->map(function ($id) use ($fileManagement) {
            return [
                'user_id' => $fileManagement->user_id,
                'question_id' => $id->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray());
    }
}
