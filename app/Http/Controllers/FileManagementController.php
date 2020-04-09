<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use tcCore\FileManagement;
use tcCore\FileManagementStatus;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateClassUploadRequest;
use tcCore\Http\Requests\ShowFileManagementRequest;
use tcCore\Http\Requests\UpdateFileManagementRequest;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\Http\Requests\CreateTeacherRequest;
use tcCore\Http\Requests\UpdateTeacherRequest;
use tcCore\UmbrellaOrganization;

class FileManagementController extends Controller {

    protected function getBasePath(){
        return storage_path('app/files');

    }

    public function getStatuses(){
        return Response(FileManagementStatus::all(),200);
    }

    public function update(UpdateFileManagementRequest $request, FileManagement $fileManagement){
        $fileManagement->fill($request->validated());

        if ($fileManagement->save() !== false) {
            return Response::make($fileManagement, 200);
        } else {
            return Response::make('Het is helaas niet gelukt om de status aan te passen.', 500);
        }
    }

    /**
     * Offers a download to the specified file from storage.
     *
     * @param  file
     * @return Response
     */
    public function download(Requests\DownloadFileManagementRequest $request, FileManagement $fileManagement)
    {
        return Response::download(sprintf('%s/%s/%s',$this->getBasePath(),$fileManagement->schoolLocation->getKey(),$fileManagement->name));
    }

    public function storeClassUpload(CreateClassUploadRequest $request, SchoolLocation $schoolLocation){
        $file = $request->file('file');
        $origfileName = $file->getClientOriginalName();

        $fileName = sprintf('%s-%s.%s',date('YmdHis'),Str::slug(request('class')),pathinfo($origfileName,PATHINFO_EXTENSION));

        $file->move(sprintf('%s/%s',$this->getBasePath(),$schoolLocation->getKey()), $fileName);

        $fileManagement = new FileManagement();

        $data = [
                'id' => Str::uuid(),
                'origname' => $origfileName,
                'name' => $fileName,
                'user_id' => Auth::user()->getKey(),
                'school_location_id' => $schoolLocation->getKey(),
                'type' => 'classupload',
                'typedetails' => json_encode([
                    'class' => request('class'),
                    'education_level_year' => request('education_level_year'),
                    'education_level_id' => request('education_level_id'),
                    'is_main_school_class' => request('is_main_school_class'),
                    'subject' => request('subject'),
                ]),
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
            ->orderBy('created_at','asc')
            ->with(['user','handler','status']);

        $user = Auth::user();

	    if($user->hasRole('Teacher')){
	        $files->where('school_location_id',$user->school_location_id)
                ->whereIN('file_management_status_id',[1,2,4]);
        }
        else if ($user->hasRole('Account manager')){
            // borowed from User.php
            $userId = Auth::user()->getKey();
            $schoolIds = School::where(function ($query) use ($userId) {
                $query->whereIn('umbrella_organization_id', function ($query) use ($userId) {
                    $query->select('id')
                        ->from(with(new UmbrellaOrganization())->getTable())
                        ->where('user_id', $userId)
                        ->whereNull('deleted_at');
                })->orWhere('user_id', $userId);
            })->pluck('id')->all();

            $schoolLocationIds = SchoolLocation::where(function ($query) use ($schoolIds, $userId) {
                $query->whereIn('school_id', $schoolIds)
                    ->orWhere('user_id', $userId);
            })->pluck('id')->all();
            $files->whereIn('school_location_id',$schoolLocationIds);

            $files->with(['schoolLocation']);
        }

		if($request->get('type')){
		    $files->where('type',$request->get('type'));
        }

		switch(strtolower($request->get('mode', 'paginate'))) {
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
        $fileManagement->load(['user','handler','status','children', 'schoolLocation']);

        $user = Auth::user();
        if($user->hasRole('Teacher')){
            if($user->school_location_id != $fileManagement->school_location_id){
                return Response::make('not allowed', 403);
            }
        }else{
            $fileManagement->statuses = FileManagementStatus::all();
        }


        return Response::make($fileManagement, 200);
	}
}
