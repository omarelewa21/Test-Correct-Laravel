<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use tcCore\SearchFilter;
use tcCore\Http\Requests\CreateSearchFilterRequest;
use Illuminate\Support\Facades\Auth;


class SearchFiltersController extends Controller
{
    public function store(CreateSearchFilterRequest $request){
        logger($request->validated());
    	$searchFilter = SearchFilter::create($request->validated());
    	return $searchFilter->activate();
    }

    public function update(CreateSearchFilterRequest $request,$uuid){
	   	$searchFilter = SearchFilter::whereUuid($uuid)->first();
    	if(is_null($searchFilter)){
    		return response()->json('[]');
    	}
    	$searchFilter->update($request->validated());
        return $searchFilter->activate();
    }

    public function show($key=false){
    	$searchFilters = [];
    	if($key){
			$userId = Auth::user()->id;
    		$searchFilters = SearchFilter::where('user_id',$userId)->where('key',$key)->get();
    	}
    	return response()->json($searchFilters);
    }

    public function delete($uuid=false){
    	if(!$uuid){
			return response()->json(['result'=>'fail','msg'=>'no uuid']);
    	}
		$userId = Auth::user()->id;
		$searchFilter = SearchFilter::where('user_id',$userId)->whereUuid($uuid)->first();
		if(is_null($searchFilter)){
			return response()->json(['result'=>'fail','msg'=>'search filter not found']);
		}
		$searchFilter->delete();
		return response()->json(['result'=>'success']);
    }

    public function setActive($uuid=false){
    	if(!$uuid){
			return response()->json(['result'=>'fail','msg'=>'no uuid']);
    	}
    	$userId = Auth::user()->id;
		$searchFilter = SearchFilter::where('user_id',$userId)->whereUuid($uuid)->first();
		$key = $searchFilter->key;
		SearchFilter::where('user_id',$userId)->where('key',$key)->update(['active'=>false]);
		$searchFilter->active = true;
		$searchFilter->save();
		return response()->json($searchFilter);
    }

    public function deactive($uuid=false){
        if(!$uuid){
            return response()->json(['result'=>'fail','msg'=>'no uuid']);
        }
        $userId = Auth::user()->id;
        $searchFilter = SearchFilter::where('user_id',$userId)->whereUuid($uuid)->first();
        SearchFilter::where('key', $searchFilter->key)->where('user_id', $userId)->update(['active'=> false]);
        return response()->json($searchFilter->refresh());
    }
}
