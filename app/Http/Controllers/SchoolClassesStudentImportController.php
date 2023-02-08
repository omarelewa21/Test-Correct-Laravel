<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Lib\Repositories\AverageRatingRepository;
use tcCore\Lib\Repositories\SchoolClassRepository;
use tcCore\Lib\User\Factory;
use tcCore\SchoolClass;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\SchoolClassesStudentImportRequest;
use tcCore\SchoolLocation;
use tcCore\User;

class SchoolClassesStudentImportController extends Controller {

    /**
     * Import students.
     * @param SchoolClassesStudentImportRequest $request
     * @return
     */
    public function store(SchoolClassesStudentImportRequest $request, SchoolLocation $schoolLocation, SchoolClass $schoolClass) {

        $defaultData = [
            'user_roles' => [3],
            'school_location_id' => $schoolLocation->getKey(),
            'student_school_classes' => [$schoolClass->getKey()],
        ];

        $data = $request->validated();
        $manager = Auth::user();
        DB::beginTransaction();

        try {

            collect($data['data'])->each(function($u) use ($defaultData, $schoolClass,$manager) {

                $merged = array_merge($u, $defaultData);
                $user = User::where('username', $merged['username'])->first();
                if (isset($merged['school_class_name'])) {

                    $school_class_name = $merged['school_class_name'];

                    $schoolClass = SchoolClass::where('name', trim($school_class_name))->where('school_location_id',$manager->school_location_id)->first();
                    if(is_null($schoolClass)){
                        throw new \Exception("School class id not found for class " . $school_class_name);
                    }
                    $school_class_id = $schoolClass->getKey();
                } else {
                    $school_class_id = $schoolClass->getKey();
                }

                if ($school_class_id == NULL) {
                    throw new \Exception("School class id not found for class " . $school_class_name, 422);
                }
                $merged['student_school_classes'] = [$school_class_id];
                if ($user) {
                    if ($user->isA('student')) {

                        $_deletedClass = $user->students()->withTrashed()->Where('class_id', $school_class_id)->first();

                        if ((bool) $_deletedClass) {
                            $_deletedClass->restore();                      
                        } else {
                            $user->students()->create([
                                'class_id' => $school_class_id
                            ]);
                        }
                    }
                } else {
                    $userFactory = new Factory(new User());
                    $user = $userFactory->generate($merged);
                }
            });
        } catch (\Exception $e) {
            DB::rollback();
            logger('Error importing students ' . $e->getMessage());
            $errors = json_encode(['errors'=>['error'=>$e->getMessage()]]);
            return Response::make($errors, 422);
        }
        DB::commit();
        return Response::make(count($data['data']) . ' studenten zijn toegevoegd', 200);

    }
}
