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

        DB::beginTransaction();

        try {

            collect($data['data'])->each(function($u) use ($defaultData, $schoolClass) {

                $merged = array_merge($u, $defaultData);

                $user = User::where('username', $merged['username'])->first();

                if (isset($merged['school_class_name'])) {

                    $school_class_name = $merged['school_class_name'];

                    $school_class_id = SchoolClass::where('name', trim($school_class_name))->first()->getkey();
                    
                } else {
                    $school_class_id = $schoolClass->getKey();
                }

                if ($school_class_id == NULL) {

                    return Response::make("School class id not fond for class " . $school_class_name, 422);
                }

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
            return Response::make($e->getMessage(), 500);
        }
        DB::commit();
        return Response::make(json_encode(count($data['data']) . ' studenten zijn toegevoegd'), 200);

    }

}
