<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\CreateSchoolLocationRequest;
use tcCore\Http\Requests\ListSchoolLocationEducationlevelRequest;
use tcCore\Http\Requests\UpdateSchoolLocationRequest;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationEducationLevel;

class SchoolLocationEducationLevelsController extends Controller {
    /**
     * Display a listing of the school locations.
     *
     * @return Response
     */
    public function index(ListSchoolLocationEducationLevelRequest $request, SchoolLocation $schoolLocation)
    {
        $schoolLocationlEducationLevels = SchoolLocationEducationLevel::where('school_location_id',$schoolLocation->getKey())->with('educationLevel');

        if ($request->get('without_demo') == true) {
            $schoolLocationlEducationLevels = SchoolLocationEducationLevel::where('school_location_id', $schoolLocation->getKey())
                ->with([
                    'educationLevel' => function ($query) {
                        $query->where('education_levels.name', '<>', 'Demo');
                    }
                ]);
        }

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($schoolLocationlEducationLevels->get(), 200);
                break;
            case 'list':
                return Response::make($schoolLocationlEducationLevels->pluck('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($schoolLocationlEducationLevels->paginate(15), 200);
                break;
        }
    }
}
