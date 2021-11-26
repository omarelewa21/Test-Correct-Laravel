<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Message;
use tcCore\Http\Requests\CreateMessageRequest;
use tcCore\Http\Requests\UpdateMessageRequest;
use tcCore\User;

class MessageController extends Controller {

    /**
     * Display a listing of the messages.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $messages = Message::filtered($request->get('filter', []), $request->get('order', []))->with('messageReceivers', 'user', 'messageReceivers.user');

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'count':
                return Response::make($messages->count(), 200);
                break;
            case 'all':
                return Response::make($messages->get(), 200);
                break;
            case 'list':
                return Response::make($messages->pluck('subject', 'id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($messages->paginate(15), 200);
                break;
        }
    }

    /**
     * Store a newly created message in storage.
     *
     * @param CreateMessageRequest $request
     * @return Response
     */
    public function store(CreateMessageRequest $request)
    {
        //
        $message = new Message($request->all());
        $message->setAttribute('user_id', Auth::user()->getKey());
        if ($message->save()) {
            return Response::make($message, 200);
        } else {
            return Response::make('Failed to create message', 500);
        }
    }

    /**
     * Display the specified message.
     *
     * @param  Message  $message
     * @return Response
     */
    public function show(Message $message)
    {
        $message['user_uuid'] = User::withTrashed()->find($message['user_id'])->uuid;
        return Response::make($message, 200);
    }

    /**
     * Update the specified message in storage.
     *
     * @param  Message $message
     * @param UpdateMessageRequest $request
     * @return Response
     */
    public function update(Message $message, UpdateMessageRequest $request)
    {
        $message->fill($request->all());
        if ($message->save()) {
            return Response::make($message, 200);
        } else {
            return Response::make('Failed to update message', 500);
        }
    }

    /**
     * Mark the message as read
     *
     * @param  Message $message
     * @return Response
     */
    public function markRead(Message $message)
    {
        $message->markRead();
        return Response::make("ok");
    }

    /**
     * Remove the specified message from storage.
     *
     * @param  Message  $message
     * @return Response
     */
    public function destroy(Message $message)
    {
        //
        if ($message->delete()) {
            return Response::make($message, 200);
        } else {
            return Response::make('Failed to delete test kind', 500);
        }
    }
}
