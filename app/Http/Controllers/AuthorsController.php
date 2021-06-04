<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\User;

class AuthorsController extends Controller
{
    public function index(Request $request)
    {
        $builder = User::whereIn('id', // find all users part of this selection
            Teacher::with('user')->whereIn('subject_id', // find the teachers with these subjects
                Subject::whereIn('section_id', // get all subjects belonging to the section memberships
                    Auth::user()->sections()->select('sections.id')->where('demo',0) // get section memberships of this teacher where section is not part of the demo environment
                )->select('subjects.id')
            )->select('teachers.user_id')
        )->groupBy('users.id');
//
//        $tests = Test::filtered($request->get('filter', []), $request->get('order', []))->with('author')->get();
//        $authors = [];
//        foreach ($tests as $test){
//            $authors[] = $test->author;
//        }
//        $authors = array_unique($authors);
        return Response::make($builder->get(), 200);
    }
}
