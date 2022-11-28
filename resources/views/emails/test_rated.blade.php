@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("test_rated.Beste") }} {{ $testParticipant->user->getNameFullAttribute() }},<br/>
<br/>
{{ __("test_rated.Jouw docent heeft jouw toets beoordeeld. Voor de toets") }} "{{ $testParticipant->testTake->test->name }}" {{ __("test_rated.van het vak") }} {{ $testParticipant->testTake->test->subject->name }} {{ __("test_rated.heb jij een") }} {{ $testParticipant->rating }} {{ __("test_rated.gehaald") }}.<br/>
<br/>
{{ __("test_rated.Voor een overzicht en analyse ga je naar") }} <a href="{{$urlLogin}}">Test-Correct</a><br/>
<br/>
{{ __("test_rated.Test-Correct supportteam") }}
</td>
</tr>
@stop