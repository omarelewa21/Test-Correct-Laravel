<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Http\Requests\CreateTellATeacherRequest;
use tcCore\Http\Requests\CreateUserRequest;

class TellATeacherController extends Controller
{
    public function store(CreateTellATeacherRequest $request)
    {
        $r = $request->validated();
        DB::beginTransaction();
        try {
            foreach ($r['data'] as $i => $data) {
                $data['shouldRegisterUser'] = true;
                $data = array_merge($data,$r);
                unset($data['data']);
                if(!(new UserHelper())->createUserFromData($data)){
                    logger(sprintf('Error while inviting other teachers %s',json_encode($data)));
                    throw new \Exception(sprintf('Could not create user %s',$data['username']));
                }
            }
        } catch(\Exception $e){
            DB::rollback();
            logger($e->getMessage());
            return Response::make('Failed to create users', 500);
        }

        DB::commit();
        return Response::make(sprintf('%d',count($r['data'])), 200);
    }
}
