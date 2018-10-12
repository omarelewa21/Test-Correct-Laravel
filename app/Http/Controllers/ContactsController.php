<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Contact;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateContactRequest;
use tcCore\Http\Requests\UpdateContactRequest;

class ContactsController extends Controller {

    /**
     * Display a listing of the contacts.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $contacts = Contact::filtered($request->get('filter', []), $request->get('order', []));

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($contacts->get(), 200);
                break;
            case 'list':
                return Response::make($contacts->lists('name', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($contacts->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created contact in storage.
     *
     * @param CreateContactRequest $request
     * @return Response
     */
    public function store(CreateContactRequest $request)
    {
        //
        $contact = new Contact($request->all());
        if ($contact->save()) {
            return Response::make($contact, 200);
        } else {
            return Response::make('Failed to create contact', 500);
        }
    }

    /**
     * Display the specified contact.
     *
     * @param  Contact  $contact
     * @return Response
     */
    public function show(Contact $contact)
    {
        //
        return Response::make($contact, 200);
    }

    /**
     * Update the specified contact in storage.
     *
     * @param  Contact $contact
     * @param UpdateContactRequest $request
     * @return Response
     */
    public function update(Contact $contact, UpdateContactRequest $request)
    {
        //
        if ($contact->update($request->all())) {
            return Response::make($contact, 200);
        } else {
            return Response::make('Failed to update contact', 500);
        }
    }

    /**
     * Remove the specified contact from storage.
     *
     * @param  Contact  $contact
     * @return Response
     */
    public function destroy(Contact $contact)
    {
        //
        if ($contact->delete()) {
            return Response::make($contact, 200);
        } else {
            return Response::make('Failed to delete contact', 500);
        }
    }

}
