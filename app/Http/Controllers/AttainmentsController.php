<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Attainment;

class AttainmentsController extends Controller {

    /**
     * Display a listing of the education levels.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $attainments = Attainment::filtered($request->get('filter', []), $request->get('order', []));

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($attainments->get(), 200);
                break;
            case 'list':
                return Response::make($attainments->get(['attainments.id', 'attainments.attainment_id', 'attainments.code', 'attainments.subcode', 'attainments.description'])->keyBy('id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($attainments->paginate(15), 200);
                break;
        }
    }

    /**
     * Display the specified attainment.
     *
     * @param  Attainment  $attainment
     * @return Response
     */
    public function show(Attainment $attainment)
    {
        return Response::make($attainment, 200);
    }
}
