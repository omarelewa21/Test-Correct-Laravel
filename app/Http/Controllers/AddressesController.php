<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Address;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateAddressRequest;
use tcCore\Http\Requests\UpdateAddressRequest;

class AddressesController extends Controller {

    /**
     * Display a listing of the addresses.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $addresses = Address::filtered($request->get('filter', []), $request->get('order', []));

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($addresses->get(), 200);
                break;
            case 'list':
                return Response::make($addresses->lists('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($addresses->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created address in storage.
     *
     * @param CreateAddressRequest $request
     * @return Response
     */
    public function store(CreateAddressRequest $request)
    {
        //
        $address = new Address($request->all());
        if ($address->save()) {
            return Response::make($address, 200);
        } else {
            return Response::make('Failed to create address', 500);
        }
    }

    /**
     * Display the specified address.
     *
     * @param  Address  $address
     * @return Response
     */
    public function show(Address $address)
    {
        //
        return Response::make($address, 200);
    }

    /**
     * Update the specified address in storage.
     *
     * @param  Address $address
     * @param UpdateAddressRequest $request
     * @return Response
     */
    public function update(Address $address, UpdateAddressRequest $request)
    {
        //
        if ($address->update($request->all())) {
            return Response::make($address, 200);
        } else {
            return Response::make('Failed to update address', 500);
        }
    }

    /**
     * Remove the specified address from storage.
     *
     * @param  Address  $address
     * @return Response
     */
    public function destroy(Address $address)
    {
        //
        if ($address->delete()) {
            return Response::make($address, 200);
        } else {
            return Response::make('Failed to delete address', 500);
        }
    }

}
