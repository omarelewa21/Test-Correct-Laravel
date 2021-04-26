<?php namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\TeachersImportRequest;
use tcCore\Lib\User\Factory;
use tcCore\SchoolClass;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Http\Requests\CreateTeacherRequest;
use tcCore\Http\Requests\UpdateTeacherRequest;
use tcCore\User;

class TeachersController extends Controller
{

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
            case 'paginate':
            default:
                return Response::make($teachers->paginate(15, ['teachers.*']), 200);
                break;
        }
    }

    /**
     * Store a newly created teacher in storage.
     *
     * @param CreateTeacherRequest $request
     * @return Response
     */
    public function store(CreateTeacherRequest $request)
    {
        /**
         * @var Teacher $teacher
         */
        $request->merge([
            'user_id' => User::whereUuid($request->get('user_id'))->first()->getKey(),
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
     * @param Teacher $teacher
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
     * @param Teacher $teacher
     * @param UpdateTeacherRequest $request
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
     * @param Teacher $teacher
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
                logger($attributes);

                $user = User::where('username', $attributes['username'])->first();
                if ($user) {
                    if ($user->isA('teacher')) {
                        $this->handleExternalId($user,$attributes);
                        return $user;
                    }else {
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
            logger('Failed to import teachers' . $e);
            return Response::make('Failed to import teachers' . print_r($e->getMessage(), true), 500);
        }
        DB::commit();

        return Response::make($teachers, 200);
    }

    protected function handleExternalId($user,$attributes)
    {
        if(!array_key_exists('external_id',$attributes)){
            return;
        }
        if(!array_key_exists('school_location_id',$attributes)){
            return;
        }
        $schoolLocations = $user->schoolLocations;
        if(is_null($schoolLocations)){
            $user->schoolLocations()->attach([$attributes['school_location_id'] => ['external_id' => $attributes['external_id']]]);
            return;
        }
        foreach ($schoolLocations as $schoolLocation){
            if($schoolLocation->pivot->external_id == $attributes['external_id']&&$attributes['school_location_id']==$schoolLocation->id){
                return;
            }
        }
        $user->schoolLocations()->attach([$attributes['school_location_id'] => ['external_id' => $attributes['external_id']]]);
    }

}
