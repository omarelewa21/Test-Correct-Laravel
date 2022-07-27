<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\User;

class AuthorsController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($this->getBuilderWithAuthors());
    }

    public function getBuilderWithAuthors()
    {
        $user = Auth::user();
        if ($user->isPartOfSharedSection()) {
            $builder = $this->getBuilderForOwnSubjectsAndSharedSections($user);
        } else {
            $builder = $this->getBuilderForOwnSubjects($user);
        }
        return $builder->toBase()->get();
    }

    public static function getCentraalExamenAuthor()
    {
         return User::where('username', config('custom.examschool_author'))->first();
    }

    public static function getNationalItemBankAuthor()
    {
         return User::where('username', config('custom.national_item_bank_school_author'))->first();
    }

    private function getBuilderForOwnSubjects($user)
    {
        return User::withTrashed()->whereIn('id', // find all users part of this selection
            Teacher::withTrashed()->whereIn('subject_id', // find the teachers with these subjects
                Subject::withTrashed()->whereIn('section_id', // get all subjects belonging to the section memberships
                    $user->sections()->select('sections.id')->where('demo',0) // get section memberships of this teacher where section is not part of the demo environment
                )->select('subjects.id')
            )->select('teachers.user_id')
        )->select('id','name_first','name_suffix','name')->groupBy('users.id');
    }

    private function getBuilderForOwnSubjectsAndSharedSections($user)
    {
        return User::withTrashed()->whereIn('id', // find all users part of this selection
            Teacher::withTrashed()->whereIn('subject_id', // find the teachers with these subjects
                $user->subjectsIncludingShared()->where('demo',0)->pluck('id')
            )->select('teachers.user_id')
        )->select('id','name_first','name_suffix','name')->groupBy('users.id');
    }

}
