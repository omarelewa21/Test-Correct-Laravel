<?php

namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
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

class FileManagementController extends Controller {

    protected function getBasePath() {
        return storage_path('app/files');
    }

    public function getStatuses() {
        return Response(FileManagementStatus::all(), 200);
    }

    protected function sendInvite(FileManagement $fileManagement) {
        Queue::push(new SendToetsenbakkerInviteMail($fileManagement->getKey()));
//        dispatch_now(new SendToetsenbakkerInviteMail($fileManagement->getKey()));
    }

    public function update(UpdateFileManagementRequest $request, FileManagement $fileManagement) {
        $fileManagement->fill($request->validated());
        $typeDetails = $fileManagement->typedetails;
        $originalEmail = property_exists($typeDetails, 'invite') ? $typeDetails->invite : '';
        $typeDetails->colorcode = $request->get('colorcode');
        $typeDetails->invite = $request->get('invite');
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
    public function download(Requests\DownloadFileManagementRequest $request, FileManagement $fileManagement) {
        return Response::download(sprintf('%s/%s/%s', $this->getBasePath(), $fileManagement->schoolLocation->getKey(), $fileManagement->name));
    }

    public function storeTestUpload(CreateTestUploadRequest $request, SchoolLocation $schoolLocation) {

        $form_id = $request['form_id'];

        if (!$request->isFile()) {

            $data = [
                'id' => Str::uuid(),
                'origname' => '',
                'name' => '',
                'user_id' => Auth::user()->getKey(),
                'school_location_id' => $schoolLocation->getKey(),
                'type' => 'testupload',
                'typedetails' => [// request data?
                    'test_kind_id' => $request->get('test_kind_id'),
                    'education_level_year' => $request->get('education_level_year'),
                    'education_level_id' => $request->get('education_level_id'),
                    'subject' => $request->get('subject'),
                    'name' => $request->get('name'),
                    'correctiemodel' => $request->get('correctiemodel'),
                    'multiple' => $request->get('multiple'),
                    'form_id' => $request->get('form_id')
                ],
            ];

            $main = new FileManagement();

            $main->fill($data);

            $main->save();

            $parent_id = $main->getKey();

            FileManagement::where('parent_id', $form_id)->update(['parent_id' => $parent_id, 'typedetails' => $data['typedetails']]);

            // rename all files so the name includes the subject name

            $stored_files = FileManagement::where('parent_id', $parent_id)->get();

            $storage_path = sprintf('%s/%s', $this->getBasePath(), $schoolLocation->getKey());

            // add subject to filename
            foreach ($stored_files as $file) {

                $new_name = sprintf('%s-%s-%s-%s.%s', date('YmdHis'), Str::random(5), Str::slug($request->get('name')), $request->get('subject'), pathinfo($file->origname, PATHINFO_EXTENSION));

                rename($storage_path . '/' . $file->name, $storage_path . '/' . $new_name);

                FileManagement::where('name', $file->name)->update(['name' => $new_name]);
                
            }

            Response::make($main, 200);
            
        } else {

            // there is only one file at a time
            $file = $request->file('files')[0];

            // file data is temporary placeholder

            $data = [
                'id' => Str::uuid(),
                'origname' => '',
                'name' => '',
                'user_id' => Auth::user()->getKey(),
                'school_location_id' => $schoolLocation->getKey(),
                'type' => 'testupload',
                'typedetails' => []
            ];

            DB::beginTransaction();

            try {

                $child = new FileManagement();

                $data['id'] = Str::uuid();

                $data['parent_id'] = $form_id;

                $origfileName = $file->getClientOriginalName();

                $fileName = sprintf('%s-%s-%s.%s', date('YmdHis'), Str::random(5), Str::slug($request->get('name')), pathinfo($origfileName, PATHINFO_EXTENSION));

                $file->move(sprintf('%s/%s', $this->getBasePath(), $schoolLocation->getKey()), $fileName);

                $data['origname'] = $origfileName;
                $data['name'] = $fileName;

                $child->fill($data);

                $child->save();
            } catch (\Exception $e) {
                DB::rollback();
                logger('===== error ' . $e->getMessage());
                Response::make('Het is helaas niet gelukt om de upload te verwerken, probeer het nogmaals.', 500);
            }

            DB::commit();
            Response::make($child, 200);
        }
    }

    public function storeClassUpload(CreateClassUploadRequest $request, SchoolLocation $schoolLocation) {

        $file = $request->file('file');

        $origfileName = $file->getClientOriginalName();

        $fileName = sprintf('%s-%s.%s', date('YmdHis'), Str::slug($request->get('class')), pathinfo($origfileName, PATHINFO_EXTENSION));

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
                'class' => $request->get('class'),
                'education_level_year' => $request->get('education_level_year'),
                'education_level_id' => $request->get('education_level_id'),
                'is_main_school_class' => $request->get('is_main_school_class'),
                'subject' => $request->get('subject'),
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
    public function index(Requests\IndexFileManagementRequest $request) {

        $files = FileManagement::whereNull('parent_id')
                ->orderby('file_management_status_id')
                ->with(['user', 'handler', 'status', 'status.parent']);

        $user = Auth::user();

        if ($user->hasRole('Teacher')) {
            $files->where(function ($query) use ($user) {
                $query->where('user_id', $user->getKey())
                        ->orWhere('handledby', $user->getKey());
            });
            if ($user->isToetsenbakker()) {
                $files->where('archived', false);
            } else {
//                $files->where('school_location_id', $user->school_location_id)
//                    ->whereIN('file_management_status_id', [1, 2, 3, 4, 5, 6, 8]);
                $files->where('school_location_id', $user->school_location_id);
            }
        } else if ($user->hasRole('Account manager')) {
            $files->whereIn('school_location_id', (new SchoolHelper())->getRelatedSchoolLocationIds($user))
                    ->with(['schoolLocation']);
            // we want to order by filemanagementstatus displayorder, but as it has the same fieldnames as file_managements table
            // we can't use a join. Therefor we first get all the statusIds in the correct order and then order by them
            $statusIds = FileManagementStatus::orderBy('displayorder')->pluck('id')->toArray();
            $files->orderByRaw('FIELD(file_management_status_id,' . implode(',', $statusIds) . ')', 'asc');
        }

        if ($request->get('type')) {
            $files->where('type', $request->get('type'));
        }

        $files->orderBy('file_managements.created_at', 'asc');


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
    public function show(ShowFileManagementRequest $request, FileManagement $fileManagement) {
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
