@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("welcome.Welkom bij Test-correct") }}, {{ $user->nameFull }}.<br/>
<br/>
{{__('welcome.Je gebruikersnaam is')}} {{ $user->username }}<br/>
    {{__('welcome.Je kunt je wachtwoord instellen op')}}:<br />
    <a href="{{ sprintf($url, $token) }}">{{ sprintf($url, $token) }}</a><br/>
    <br/>
    {{__('welcome.Deze link verloopt vier uur nadat dit e-mailbericht werd verstuurd. Je kunt een nieuwe link laten opsturen via de wachtwoord vergeten functionaliteit.')}}
    <BR/> <a href="{{ \tcCore\Http\Helpers\BaseHelper::getLoginUrl() }}">{{__('welcome.Nieuwe verzoek opsturen')}}</a><br/>
    <br/>
</td>
</tr>
@stop
