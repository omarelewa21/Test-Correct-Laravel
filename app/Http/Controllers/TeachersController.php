<?php namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\TeachersImportRequest;
use tcCore\Http\Requests\UpdateWithSubjectsForClusterClassesRequest;
use tcCore\Http\Traits\UwlrImportHandlingForController;
use tcCore\Lib\User\Factory;
use tcCore\SchoolClass;
use tcCore\SchoolClassImportLog;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Http\Requests\CreateTeacherRequest;
use tcCore\Http\Requests\UpdateTeacherRequest;
use tcCore\TeacherImportLog;
use tcCore\TestTake;
use tcCore\User;

class TeachersController extends Controller
{

    use UwlrImportHandlingForController;

    /**
     * Display a listing of the teachers.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $teachers = Teacher::filtered($request->get('filter', []), $request->get('order', []));

        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($teachers->get(['teachers.*']), 200);
                break;
            case 'import_data':
                $user = Auth::user();
                return Response::make(
                    $teachers
                        ->leftJoin('teacher_import_logs as log', 'teachers.id', 'teacher_id')
                        ->join('school_classes','school_classes.id','class_id')
                        ->select(
                            'teachers.*',
                            'log.checked_by_teacher as checked_by_teacher'
                        )
                        ->where('teachers.user_id', $user->getKey())
                        ->where('school_classes.school_location_id',$user->school_location_id)
                        ->get()
                        ->toArray()
                );
                break;
            case 'paginate':
            default:
                return Response::make($teachers->paginate(15, ['teachers.*']), 200);
                break;
        }
    }

    /**
     * Store a newly created teacher in storage.
     *
     * @param  CreateTeacherRequest  $request
     * @return Response
     */
    public function store(CreateTeacherRequest $request)
    {
        /**
         * @var Teacher $teacher
         */
        $request->merge([
            'user_id'  => User::whereUuid($request->get('user_id'))->first()->getKey(),
            'class_id' => SchoolClass::whereUuid($request->get('class_id'))->first()->getKey()
        ]);

        $teacher = Teacher::withTrashed()
            ->firstOrNew($request->only(['user_id', 'class_id', 'subject_id']));
        $teacher->fill($request->all());
        if ($teacher->trashed()) {
            if ($teacher->restore()) {
                return Response::make($teacher, 200);
            } else {
                return Response::make('Failed to restore teacher', 500);
            }
        } elseif ($teacher->save()) {
            return Response::make($teacher, 200);
        } else {
            return Response::make('Failed to create teacher', 500);
        }
    }

    /**
     * Display the specified school class.
     * @param  Teacher  $teacher
     * @return
     */
    public function show(Teacher $teacher)
    {
        $teacher->load('Subject', 'User');

        return Response::make($teacher, 200);
    }

    /**
     * Update the specified teacher in storage.
     *
     * @param  Teacher  $teacher
     * @param  UpdateTeacherRequest  $request
     * @return Response
     */
    public function update(Teacher $teacher, UpdateTeacherRequest $request)
    {
        //
        if ($teacher->update($request->all())) {
            return Response::make($teacher, 200);
        } else {
            return Response::make('Failed to update teacher', 500);
        }
    }

    /**
     * Remove the specified teacher from storage.
     *
     * @param  Teacher  $teacher
     * @return Response
     */
    public function destroy(Teacher $teacher)
    {
        //
        if ($teacher->delete()) {
            return Response::make($teacher, 200);
        } else {
            return Response::make('Failed to delete teacher', 500);
        }
    }

    public function import(TeachersImportRequest $request)
    {
        $defaultData = [
            'user_roles'         => [1],
            'school_location_id' => auth()->user()->school_location_id,
        ];

        DB::beginTransaction();
        try {
            $teachers = collect($request->all()['data'])->map(function ($row) use ($defaultData) {
                $attributes = array_merge($row, $defaultData);

                $user = User::where('username', $attributes['username'])->first();
                if ($user) {
                    if ($user->isA('teacher')) {
                        $this->handleExternalId($user, $attributes);
                    } else {
                        throw new \Exception('conflict: exists but not teacher');
                    }
                } else {
                    $userFactory = new Factory(new User());
                    $user = $userFactory->generate(
                        array_merge(
                            $attributes,
                            ['account_verified' => Carbon::now()]
                        )
                    );
                }
                $user->save();

                $teacher = Teacher::withTrashed()
                    ->firstOrNew(([
                        'user_id'    => $user->getKey(),
                        'class_id'   => $attributes['class_id'],
                        'subject_id' => $attributes['subject_id'],
                    ]));
                $teacher->trashed() ? $teacher->restore() : $teacher->save();

                return $user;
            });
        } catch (\Exception $e) {
            DB::rollBack();
            logger('Failed to import teachers'.$e);
            return Response::make('Failed to import teachers'.print_r($e->getMessage(), true), 500);
        }
        DB::commit();

        return Response::make($teachers, 200);
    }

    protected function handleExternalId($user, $attributes)
    {
        if (!array_key_exists('external_id', $attributes)||empty($attributes['external_id'])) {
            $attributes['external_id'] = '';
        }
        if (!array_key_exists('school_location_id', $attributes)) {
            return;
        }
        $schoolLocations = $user->allowedSchoolLocations;
        foreach ($schoolLocations as $schoolLocation) {
            if ($schoolLocation->pivot->external_id == $attributes['external_id'] && $attributes['school_location_id'] == $schoolLocation->id) {
                return;
            }
        }
        $user->allowedSchoolLocations()->attach([$attributes['school_location_id'] => ['external_id' => $attributes['external_id']]]);
    }

    public function updateWithSubjectsForClusterClasses(UpdateWithSubjectsForClusterClassesRequest $request)
    {
        $updateCounter = 0;
        if (is_array($request->get('teacher'))) {
            collect($request->get('teacher'))->each(function ($subjectValue, $schoolClassId) use (&$updateCounter) {
                if ($schoolClass = SchoolClass::withoutGlobalScope('visibleOnly')->find($schoolClassId)) {
                    if ($schoolClass->is_main_school_class == 1) {
                        $allTeacherRecordsForThisTeacherAndClass = Teacher::where([
                            'class_id' => $schoolClassId, 'user_id' => Auth::id()
                        ])->get();
                        //@ask Carlo is it a problem that different teachers give a different subject to a non main_school_class?
                        // Wel toestaan. maar alwel voorstellen dat, en een melding geven dat een andere docent een andere keuze heeft gemaakt.
                        $allTeacherRecordsForThisTeacherAndClass->each->forceDelete();

                        foreach ($subjectValue as $subjectId => $checkboxValue) {
                            $oldTeacher = Teacher::where([
                                'class_id' => $schoolClassId,
                                'subject_id' => $subjectId,
                                'user_id' => Auth::id(),
                            ])->withTrashed()->first();

                            if(null !== $oldTeacher){
                                if($oldTeacher->trashed()){
                                    $oldTeacher->restore();
                                }
                                $teacher = $oldTeacher;
                            } else {
                                $teacher = Teacher::create([
                                    'class_id' => $schoolClassId,
                                    'subject_id' => $subjectId,
                                    'user_id' => Auth::id(),
                                ]);
                            }
                            $this->updateImportLog(['checked' => 'on'], $teacher);
                            $updateCounter++;
                        }
                    } else {
                        $teacher = Teacher::where([
                            'class_id' => $schoolClassId,
                            'user_id' => Auth::id()
                        ])->first();

                        $oldTeacher = Teacher::where([
                            'class_id' => $schoolClassId,
                            'user_id'  => Auth::id(),
                            'subject_id' => $subjectValue
                        ])->withTrashed()->first();
                        if(null !== $oldTeacher){
                            if($oldTeacher->trashed()){
                                $oldTeacher->restore();
                            }
                            if($oldTeacher->getKey() !== $teacher->getKey()) {
                                $teacher->delete();
                            }
                            $this->updateImportLog(['checked' => 'on'], $oldTeacher);
                        } else {
                            $teacher->subject_id = $subjectValue;
                            $teacher->save();
                            $this->updateImportLog(['checked' => 'on'], $teacher);
                        }
                        $updateCounter++;
                    }
                }
            });
        }

        if(!Auth::user()->hasIncompleteImport(false)){
            $this->setClassesVisibleAndFinalizeImport(Auth::user());
        }

        return JsonResource::make(['count' => $updateCounter], 200);
    }

    /**
     * @param $value
     * @param $schoolClass
     */
    private function updateImportLog($value, Teacher $teacher): void
    {
        if (array_key_exists('checked', $value) && $value['checked']) {

            $importLog = $teacher->importLog;

            if ($importLog == null) {
                $importLog = new TeacherImportLog;
            }

            if (Auth::user()->isA('teacher') && is_null($importLog->checked_by_teacher)) {
                $importLog->checked_by_teacher = now();
            }

            $teacher->importLog()->save($importLog);
        }
    }

    public function hasIncompleteImport(Request $request)
    {
        return Auth::user()->hasIncompleteImport();

    }

    public function getSchoolLocationTeacherUser(Request $request, SchoolLocation $schoolLocation)
    {
        $test = $this->getTestFromTestTakeWithSubjectAndScope($request->get('testTakeUuid'));

        if (filled($test->scope)) {
            $query = Teacher::getTeacherUsersForSchoolLocationByBaseSubjectInCurrentYear(Auth::user()->schoolLocation, $test->subject->base_subject_id);
        } else {
            $query = Teacher::getTeacherUsersForSchoolLocationBySubjectInCurrentYear(Auth::user()->schoolLocation, $test->subject_id);
        }

        $teacherUsers = $query->get(['id','uuid', 'name', 'name_suffix', 'name_first'])->each(fn($user) => $user->append('name_full'));

        return Response::make($teacherUsers, 200);
    }

    private function getTestFromTestTakeWithSubjectAndScope($testTakeUuid) {
        return TestTake::whereUuid($testTakeUuid)
            ->select(['id', 'test_id'])
            ->with(['test:id,subject_id,scope', 'test.subject:id,base_subject_id'])
            ->firstOrFail()
            ->test;
    }
}
