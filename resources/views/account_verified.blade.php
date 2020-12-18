@extends('app')
@section('content')
    <div class="bg-white w-1/2 mx-auto p-8 my-8 rounded-10 base shadow">
        <h1 class="mb-4">E-mailadres geverifieerd</h1>
        @if($already_verified)
            <p class="mb-4">Je account met e-mailadres <span class="bold">{{$username}}</span> is al geverifieerd.</p>
        @else
            <p class="mb-4">Je account met e-mailadres <span class="bold">{{$username}}</span> is nu geverifieerd.</p>
        @endif
        <button class="button primary-button button-sm"><a href="{{config('app.url_login') }}">Log in</a></button>
    </div>
@endsection
