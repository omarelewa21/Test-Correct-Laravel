<?php namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use tcCore\FileManagement;
use tcCore\FileManagementStatus;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateClassUploadRequest;
use tcCore\Http\Requests\CreateTestUploadRequest;
use tcCore\Http\Requests\ShowFileManagementRequest;
use tcCore\Http\Requests\UpdateFileManagementRequest;
use tcCore\Jobs\SendToetsenbakkerInviteMail;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\Http\Requests\CreateTeacherRequest;
use tcCore\Http\Requests\UpdateTeacherRequest;
use tcCore\UmbrellaOrganization;

class FileManagementController extends Controller
{

    protected function getBasePath()
    {
        return storage_path('app/files');

    }

    public function getStatuses()
    {
        return Response(FileManagementStatus::all(), 200);
    }

    protected function sendInvite(FileManagement $fileManagement){
        dispatch(new SendToetsenbakkerInviteMail($fileManagement->getKey()));

    }

    public function update(UpdateFileManagementRequest $request, FileManagement $fileManagement)
    {
        $fileManagement->fill($request->validated());
        $typeDetails = $fileManagement->typedetails;
        $originalEmail = property_exists($typeDetails,'invite') ? $typeDetails->invite : '';
        $typeDetails->colorcode = request('colorcode');
        $typeDetails->invite = request('invite');
        $fileManagement->typedetails = $typeDetails;
        if ($fileManagement->save() !== false) {
            $email = $request->get('invite');
            if($originalEmail != $email && strlen($email) > 3){
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

        DB::beginTransaction();
        try {


            $main = new FileManagement();

            $data = [
                'id' => Str::uuid(),
                'origname' => '',
                'name' => '',
                'user_id' => Auth::user()->getKey(),
                'school_location_id' => $schoolLocation->getKey(),
                'type' => 'testupload',
                'typedetails' => [
                    'test_kind_id' => request('test_kind_id'),
                    'education_level_year' => request('education_level_year'),
                    'education_level_id' => request('education_level_id'),
                    'subject' => request('subject'),
                    'name' => request('name'),
                ],
            ];

            $main->fill($data);

            $main->save();

            foreach (request('files') as $file) {

                $child = new FileManagement();
                $data['id'] = Str::uuid();
                $data['parent_id'] = $main->getKey();

                $origfileName = $file->getClientOriginalName();

                $fileName = sprintf('%s-%s-%s.%s', date('YmdHis'), Str::random(5),Str::slug(request('name')), pathinfo($origfileName, PATHINFO_EXTENSION));

                $file->move(sprintf('%s/%s', $this->getBasePath(), $schoolLocation->getKey()), $fileName);

                $data['origname'] = $origfileName;
                $data['name'] = $fileName;

                $child->fill($data);

                $child->save();
            }
        } catch (\Exception $e) {
            DB::rollback();
            logger('===== error ' . $e->getMessage());
            Response::make('Het is helaas niet gelukt om de upload te verwerken, probeer het nogmaals.', 500);
        }
        DB::commit();
        Response::make($main, 200);
    }


    public function storeClassUpload(CreateClassUploadRequest $request, SchoolLocation $schoolLocation)
    {
        $file = $request->file('file');
        $origfileName = $file->getClientOriginalName();

        $fileName = sprintf('%s-%s.%s', date('YmdHis'), Str::slug(request('class')), pathinfo($origfileName, PATHINFO_EXTENSION));

        $file->move(sprintf('%s/%s', $this->getBasePath(), $schoolLocation->getKey()), $fileName);

        $fileManagement = new FileManagement();

        $data = [
            'id' => Str::uuid(),
            'origname' => $origfileName,
            'name' => $fileName,
            'user_id' => Auth::user()->getKey(),
            'school_location_id' => $schoolLocation->getKey(),
            'type' => 'classupload',
            'typedetails' => [
                'class' => request('class'),
                'education_level_year' => request('education_level_year'),
                'education_level_id' => request('education_level_id'),
                'is_main_school_class' => request('is_main_school_class'),
                'subject' => request('subject'),
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

        $files = FileManagement::whereNull('parent_id')
            ->orderby('file_management_status_id')
            ->orderBy('created_at', 'asc')
            ->with(['user', 'handler', 'status','status.parent']);

        $user = Auth::user();

        if ($user->hasRole('Teacher')) {
            $files->where('school_location_id', $user->school_location_id)
                ->whereIN('file_management_status_id', [1, 2, 3,4,5,6,8])
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->getKey())
                        ->orWhere('handledby', $user->getKey());
                });
        } else if ($user->hasRole('Account manager')) {
            $files->whereIn('school_location_id', (new SchoolHelper())->getRelatedSchoolLocationIds($user));

            $files->with(['schoolLocation']);
        }

        if ($request->get('type')) {
            $files->where('type', $request->get('type'));
        }


        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($files->get(), 200);
                break;
            case 'paginate':
            default:
                return Response::make($files->paginate(15), 200);
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
            $fileManagement->statuses = FileManagementStatus::all();
        } else if ($user->hasRole('Teacher')) {
            if ($user->school_location_id != $fileManagement->school_location_id) {
                return Response::make('not allowed', 403);
            }
        }


        return Response::make($fileManagement, 200);
    }
}
