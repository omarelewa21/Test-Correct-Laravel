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

    public static function getNationalItemBankAuthor()
    {
        return User::where('username', config('custom.national_item_bank_school_author'))->first();
    }

    public static function getPublishableAuthorByCustomerCode($customerCode)
    {
        $lookupTable = (new self)->getPublishableAuthorCustomerCodesAndUsernames();

        $username = $lookupTable[$customerCode] ?? false;

        if(!$username){
            return false;
        }
        return User::where('username', $username)->first();
    }

    private function getPublishableAuthorCustomerCodesAndUsernames()
    {
        return [
            config('custom.examschool_customercode')                => config('custom.examschool_author'),
            config('custom.national_item_bank_school_customercode') => config('custom.national_item_bank_school_author'),
            config('custom.creathlon_school_customercode')          => config('custom.creathlon_school_author'),
        ];
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
