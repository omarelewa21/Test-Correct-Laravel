<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\CreateSchoolRequest;
use tcCore\Http\Requests\UpdateSchoolRequest;
use tcCore\School;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class SchoolsController extends Controller {
    /**
     * Display a listing of the schools.

     */
    public function index(Request $request)
    {
        $schools = School::filtered($request->get('filter', []), $request->get('order', []))->with('umbrellaOrganization');

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($schools->get(), 200);
                break;
            case 'list':
                return Response::make($schools->select(['id', 'name', 'uuid'])->get()->keyBy('id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($schools->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created school in storage.
     * @param UmbrellaOrganization $umbrellaOrganization
     * @param CreateSchoolRequest $request
     * @return
     */
    public function store(CreateSchoolRequest $request)
    {
        $school = new School();

        $school->fill($request->all());
        if (!$request->filled('user_id')) {
            $school->setAttribute('user_id', Auth::user()->getKey());
        }

        if ($school->save() !== false) {
            return Response::make($school, 200);
        } else {
            return Response::make('Failed to create school', 500);
        }
    }

    /**
     * Display the specified school.
     * @param School $school
     * @return
     */
    public function show(School $school)
    {
        $school->load('user', 'umbrellaOrganization', 'schoolAddresses', 'schoolAddresses.address', 'schoolContacts', 'schoolContacts.contact', 'schoolLocations');
        return Response::make($school, 200);
    }

    /**
     * Update the specified school in storage.
     * @param School $school
     * @param UpdateSchoolRequest $request
     * @return
     */
    public function update(School $school, UpdateSchoolRequest $request)
    {
        $school->fill($request->all());
        if ($school->save() !== false) {
            return Response::make($school, 200);
        } else {
            return Response::make('Failed to update school', 500);
        }
    }

    /**
     * Remove the specified school from storage.
     * @param School $school
     * @throws \Exception
     * @return
     */
    public function destroy(School $school)
    {
        if ($school->delete()) {
            return Response::make($school, 200);
        } else {
            return Response::make('Failed to delete school', 500);
        }
    }
}