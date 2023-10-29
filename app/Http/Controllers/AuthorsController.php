<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\TestAuthor;
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





    private function getBuilderForOwnSubjects($user)
    {
        return TestAuthor::schoolLocationAuthorUsers($user);
    }

    private function getBuilderForOwnSubjectsAndSharedSections($user)
    {
        return TestAuthor::schoolLocationAndSharedSectionsAuthorUsers($user);
    }

}
