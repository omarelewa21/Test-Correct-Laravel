<?php

namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Composer\Package\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\EducationLevel;
use tcCore\Http\Requests;
use tcCore\Http\Requests\UpdateWithEducationLevelsForClusterClassesRequest;
use tcCore\Http\Requests\UpdateWithEducationLevelsForMainClassesRequest;
use tcCore\Http\Traits\UwlrImportHandlingForController;
use tcCore\Lib\Repositories\AverageRatingRepository;
use tcCore\Lib\Repositories\SchoolClassRepository;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateSchoolClassRequest;
use tcCore\Http\Requests\UpdateSchoolClassRequest;
use tcCore\Http\Helpers\SchoolHelper;
use tcCore\SchoolClassImportLog;
use tcCore\User;

class SchoolClassesController extends Controller
{
    use UwlrImportHandlingForController;
    /**
     * Display a listing of the school classes.
     * @param SchoolLocation $schoolLocation
     * @param Request $request
     * @return
     */
    public function index(Requests\IndexSchoolClassRequest $request)
    {
        SchoolHelper::denyIfTempTeacher();

        $schoolClasses = SchoolClass::filtered(
            $request->get('filter', []),
            $request->get('order', [])
        )->withCount('studentUsers');

        if($request->has('for_classes_overview') && $request->for_classes_overview){
            $schoolClasses->with(['mentorUsers' => function($query){
                $query->limit(1);
            }]);
        }else{
            $schoolClasses->with(
                'schoolLocation',
                'educationLevel',
                'mentorUsers',
                'managerUsers',
                'studentUsers',
                'educationLevel',
                'schoolYear'
            );
        }
        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                $schoolClasses = $schoolClasses->get();
                if (is_array($request->get('with')) && in_array('schoolClassStats', $request->get('with'))) {
                    AverageRatingRepository::getCountAndAveragesForSchoolClasses($schoolClasses);
                }
                return Response::make($schoolClasses, 200);
                break;
            case 'import_data':
                $schoolClasses = SchoolClass::filtered($request->get('filter', []),
                    $request->get('order', []))
                    ->withoutGlobalScope('visibleOnly');

                $schoolClasses
                    ->leftJoin('school_class_import_logs as log', 'school_classes.id', 'class_id')
                    ->select(
                        DB::raw(
                            'school_classes.id as id,
                        education_level_id,
                        school_year_id,
                        education_level_year,
                        name,
                        visible,
                        is_main_school_class,
                        log.finalized as finalized,
                        log.checked_by_teacher as checked_by_teacher,
                        log.checked_by_teacher_id as checked_by_teacher_id,
                        log.checked_by_admin as checked_by_admin'
                        )
                    );


                return Response::make(['data' => $schoolClasses->get()->toArray()], 200);
                break;
            case 'list':
                return Response::make($schoolClasses->pluck('name', 'id'), 200);
                break;
            case 'uuidlist':
                $classes = SchoolClass::filtered($request->get('filter', []), $request->get('order', []))->get();
                return Response::make($classes, 200);
                break;
            case 'all_classes_for_location' :
                $classes = SchoolClass::getAllClassesForSchoolLocation(Auth::user()->school_location_id, $request->get('order', []));
                return Response::make($classes, 200 );
            case 'paginate':
            default:
                $schoolClasses = $schoolClasses->paginate(15);
                if (is_array($request->get('with')) && in_array('schoolClassStats', $request->get('with'))) {
                    AverageRatingRepository::getCountAndAveragesForSchoolClasses($schoolClasses->all());
                }
                return Response::make($schoolClasses, 200);
                break;
        }
    }

    /**
     * Store a newly created school class in storage.
     * @param CreateSchoolClassRequest $request
     * @return
     */
    public function store(CreateSchoolClassRequest $request)
    {

        $name_check = SchoolClass::select('name')
            ->where('school_location_id', $request->school_location_id)
            ->where('name', $request->name)
            ->where('school_year_id', $request->school_year_id)
            ->first();

        if ($name_check != null) {

            return Response::make('This classname is already in use in this schoolyear', 500);

        }

        $schoolClass = new SchoolClass();

        $schoolClass->fill($request->all());

        if ($schoolClass->save() !== false) {
            return Response::make($schoolClass, 200);
        } else {
            return Response::make('Failed to create school class', 500);
        }
    }

    /**
     * Display the specified school class.
     * @param SchoolClass $schoolClass
     * @param Request $request
     * @return
     */
    public function show(SchoolClass $schoolClass, Request $request)
    {
        SchoolHelper::denyIfTempTeacher();
        $schoolClass->load( [       'schoolLocation',
                                    'educationLevel',
                                    'mentorUsers' ,
                                    'managerUsers',
                                    'studentUsers',
                                    'educationLevel',
                                    'schoolYear',
                                    'teacher',
                                    'teacher.user',
                                    'teacher.subject'
                                ]);
        if (is_array($request->get('with')) && in_array('schoolClassStats', $request->get('with'))) {
            AverageRatingRepository::getCountAndAveragesForSchoolClasses([$schoolClass]);
            SchoolClassRepository::getCompareSchoolClassToParallelSchoolClasses($schoolClass);
        }
        return Response::make($schoolClass, 200);
    }

    /**
     * Update the specified school class in storage.
     * @param SchoolClass $schoolClass
     * @param UpdateSchoolClassRequest $request
     * @return
     */
    public function update(SchoolClass $schoolClass, UpdateSchoolClassRequest $request)
    {
        $schoolClass->fill($request->all());

        if ($schoolClass->save() !== false) {
            return Response::make($schoolClass, 200);
        } else {
            return Response::make('Failed to update school class', 500);
        }
    }

    /**
     * Remove the specified school class from storage.
     * @param SchoolClass $schoolClass
     * @return
     * @throws \Exception
     */
    public function destroy(SchoolClass $schoolClass)
    {
        if ($schoolClass->delete()) {
            return Response::make($schoolClass, 200);
        } else {
            return Response::make('Failed to delete school class', 500);
        }
    }

    /**
     * Returns an id and name-array for a select-box.
     *
     * @return Response
     */
    public function lists()
    {
        return Response::make(Auth::user()->teacherSchoolClasses()->orderBy('name', 'asc')->pluck('name', 'id'));
    }

    public function updateWithEducationLevelsForMainClasses(UpdateWithEducationLevelsForMainClassesRequest $request)
    {
        $updateCounter = 0;
        if (is_array($request->get('class'))) {
            collect($request->get('class'))->each(function ($value, $schoolClassId) use (&$updateCounter) {
                if(array_key_exists('education_level',$value)) {
                    $schoolClass = SchoolClass::where('id', $schoolClassId)
                        ->withoutGlobalScope('visibleOnly')
                        ->where('is_main_school_class', 1)
                        ->where('school_location_id', Auth::user()->school_location_id)
                        ->first();
                    $schoolClass->education_level_id = $value['education_level'];
                    $schoolClass->save();

                    $this->updateImportLog($value, $schoolClass);

                    $updateCounter++;
                }
            });
        }

        if (!Auth::user()->hasIncompleteImport(false)) {
            $this->setClassesVisibleAndFinalizeImport(Auth::user());
        }

        return JsonResource::make(['count' => $updateCounter], 200);
    }

    public function updateWithEducationLevelsForClusterClasses(UpdateWithEducationLevelsForClusterClassesRequest $request)
    {
        $updateCounter = 0;
        if (is_array($request->get('class'))) {
            collect($request->get('class'))->each(function ($value, $schoolClassId) use (&$updateCounter) {
                if(array_key_exists('education_level',$value) && array_key_exists('education_level_year',$value)) {
                    $schoolClass = SchoolClass::where('id', $schoolClassId)
                        ->withoutGlobalScope('visibleOnly')
                        ->where('is_main_school_class', 0)
                        ->where('school_location_id', Auth::user()->school_location_id)
                        ->first();

                    $schoolClass->education_level_id = $value['education_level'];
                    $schoolClass->education_level_year = $value['education_level_year'];
                    $schoolClass->save();
                    $this->updateImportLog($value, $schoolClass);

                    $updateCounter++;
                }
            });

        }
        if (!Auth::user()->hasIncompleteImport(false)) {
            $this->setClassesVisibleAndFinalizeImport(Auth::user());
        }
        return JsonResource::make(['count' => $updateCounter], 200);
    }

    public function deleteMentor(SchoolClass $schoolClass, $userUuid)
    {
        try{
            $user = User::withTrashed()->whereUuid($userUuid)->first();
        }catch (\Exception $e){
            return Response::make('Failed to remove mentor, user not found', 500);
        }

        if ($schoolClass->mentors()->withTrashed()->where('user_id',$user->id)->delete() !== false) {
            return Response::make($schoolClass, 200);
        } else {
            return Response::make('Failed to remove mentor', 500);
        }

    }

    /**
     * @param $value
     * @param $schoolClass
     */
    private function updateImportLog($value, $schoolClass): void
    {
        if (array_key_exists('checked', $value) && $value['checked']) {

            $importLog = $schoolClass->importLog;

            if ($importLog == null) {
                $importLog = new SchoolClassImportLog();
            }

            if (Auth::user()->isA('teacher') && is_null($importLog->checked_by_teacher)) {
                $importLog->checked_by_teacher = now();
                $importLog->checked_by_teacher_id = Auth::id();
            }

            if (Auth::user()->isA('school manager') && is_null($importLog->checked_by_admin)) {
                $importLog->checked_by_admin = now();
            }
            $schoolClass->importLog()->save($importLog);
        }
    }

    public function showForUser(User $user, Request $request)
    {
        if ($user->isA('Teacher')) {
            return Response::make($user->teacherSchoolClasses()->get());
        }

        if ($user->isA('Student')) {
            return Response::make($user->studentSchoolClasses()->get());
        }

        return Response::make('No classes for user.', 404);
    }
}
