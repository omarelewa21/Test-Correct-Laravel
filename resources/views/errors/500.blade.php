@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', $exception?->getPrevious() instanceof tcCore\Exceptions\UserFriendlyException ? $exception->getMessage() :__('Server Error'))


