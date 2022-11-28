<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests\CreateSalesOrganizationRequest;
use tcCore\Http\Requests\UpdateSalesOrganizationRequest;
use tcCore\SalesOrganization;

class SalesOrganizationsController extends Controller
{

    /**
     * Display a listing of the sales organizations.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $salesOrganizations = SalesOrganization::filtered($request->get('filter', []), $request->get('order', []));

        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($salesOrganizations->get(), 200);
                break;
            case 'list':
                return Response::make($salesOrganizations->pluck('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($salesOrganizations->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created sales organization in storage.
     *
     * @param CreateSalesOrganizationRequest $request
     * @return Response
     */
    public function store(CreateSalesOrganizationRequest $request)
    {
        $salesOrganization = new SalesOrganization($request->all());
        if ($salesOrganization->save()) {
            return Response::make($salesOrganization, 200);
        } else {
            return Response::make('Failed to create sales organization', 500);
        }
    }

    /**
     * Display the specified sales organization.
     *
     * @param  SalesOrganization $salesOrganization
     * @return Response
     */
    public function show(SalesOrganization $salesOrganization)
    {
        return Response::make($salesOrganization, 200);
    }

    /**
     * Update the specified sales organization in storage.
     *
     * @param  SalesOrganization $salesOrganization
     * @param UpdateSalesOrganizationRequest $request
     * @return Response
     */
    public function update(SalesOrganization $salesOrganization, UpdateSalesOrganizationRequest $request)
    {
        //
        if ($salesOrganization->update($request->all())) {
            return Response::make($salesOrganization, 200);
        } else {
            return Response::make('Failed to update sales organization', 500);
        }
    }

    /**
     * Remove the specified sales organization from storage.
     *
     * @param  SalesOrganization $salesOrganization
     * @return Response
     */
    public function destroy(SalesOrganization $salesOrganization)
    {
        //
        if ($salesOrganization->delete()) {
            return Response::make($salesOrganization, 200);
        } else {
            return Response::make('Failed to delete sales organization', 500);
        }
    }

}
