<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use tcCore\SearchFilter;
use tcCore\Http\Requests\CreateSearchFilterRequest;
use Illuminate\Support\Facades\Auth;


class SearchFiltersController extends Controller
{
    public function store(CreateSearchFilterRequest $request){
    	return SearchFilter::create($request->validated());
    }

    public function update(CreateSearchFilterRequest $request,$uuid){
	   	$searchFilter = SearchFilter::whereUuid($uuid)->first();
    	if(is_null($searchFilter)){
    		return response()->json('[]');
    	}
    	return $searchFilter->update($request->validated());
    }

    public function show($key=false){
    	$searchFilters = [];
    	if($key){
			$userId = Auth::user()->id;
    		$searchFilters = SearchFilter::where('user_id',$userId)->where('key',$key)->get();
    	}
    	return response()->json($searchFilters);
    }
}
