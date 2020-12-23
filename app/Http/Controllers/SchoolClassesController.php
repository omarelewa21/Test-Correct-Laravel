<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\EducationLevel;
use tcCore\Http\Requests;
use tcCore\Lib\Repositories\AverageRatingRepository;
use tcCore\Lib\Repositories\SchoolClassRepository;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateSchoolClassRequest;
use tcCore\Http\Requests\UpdateSchoolClassRequest;
use tcCore\Http\Helpers\SchoolHelper;

class SchoolClassesController extends Controller {

    /**
     * Display a listing of the school classes.
     * @param SchoolLocation $schoolLocation
     * @param Request $request
     * @return
     */
    public function index(Requests\IndexSchoolClassRequest $request) {
        SchoolHelper::denyIfTempTeacher();

        $schoolClasses = SchoolClass::filtered($request->get('filter', []), $request->get('order', []))->with('schoolLocation', 'educationLevel', 'mentorUsers', 'managerUsers', 'studentUsers', 'educationLevel', 'schoolYear');
        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                $schoolClasses = $schoolClasses->get();
                if (is_array($request->get('with')) && in_array('schoolClassStats', $request->get('with'))) {
                    AverageRatingRepository::getCountAndAveragesForSchoolClasses($schoolClasses);
                }
                return Response::make($schoolClasses, 200);
                break;
            case 'list':
                return Response::make($schoolClasses->pluck('name', 'id'), 200);
                break;
            case 'uuidlist':
                $classes = SchoolClass::filtered($request->get('filter', []), $request->get('order', []))->get();
                return Response::make($classes, 200);
                break;
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
    public function store(CreateSchoolClassRequest $request) {

        $name_check = SchoolClass::select('name')
                ->where('school_location_id',$request->school_location_id)
                ->where( 'name', $request->name )
                ->where( 'school_year_id', $request->school_year_id )
                ->first();
        
        if ($name_check != NULL) {
            
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
    public function show(SchoolClass $schoolClass, Request $request) {
        SchoolHelper::denyIfTempTeacher();

        $schoolClass->load('schoolLocation', 'educationLevel', 'mentorUsers', 'managerUsers', 'studentUsers', 'educationLevel', 'schoolYear', 'teacher', 'teacher.user', 'teacher.subject');
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
    public function update(SchoolClass $schoolClass, UpdateSchoolClassRequest $request) {
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
     * @throws \Exception
     * @return
     */
    public function destroy(SchoolClass $schoolClass) {
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
    public function lists() {
        return Response::make(Auth::user()->teacherSchoolClasses()->orderBy('name', 'asc')->pluck('name', 'id'));
    }

}
