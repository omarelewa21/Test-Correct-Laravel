@extends('emails.onboarding-layout')

@section('content')
    @php
        $name = $user->name_first;
        if(strlen($name) == 1
            || (strlen($name) == 2 && $name{1} === '.')){
                $name = sprintf('%s %s %s',$name,$user->name_suffix,$user->name);
        }


    @endphp
    <tr>
        <td colspan="999" class="pl-40 pr-40 pb-40" style="">
            <h5 class="mb-4">Hallo {{$name}}</h5>
            <p>Je hebt je aangemeld met het e-mailadres <span class="text-bold">{{$user->username}}</span></p>
            <p>Verifieer je e-mailadres</p>

            <br/>
            <a href="{{ config('app.url_login')}}" class="button cta-button stretched">Login en start demo</a><br/>
            <br/>
            Met vriendelijke groet,<br/>
            <br/>
            Alex<br />
            Test-Correct Mentor
        </td>
    </tr>
    <tr class="footer rounded-b-10">
        <td class="p-40 rounded-b-10">
            <h5>Footer</h5>
        </td>
    </tr>
@stop