@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Beste {{ $testParticipant->user->getNameFullAttribute() }},<br/>
<br/>
Jouw docent heeft jouw toets beoordeeld. Voor de toets "{{ $testParticipant->testTake->test->name }}" van het vak {{ $testParticipant->testTake->test->subject->name }} heb jij een {{ $testParticipant->rating }} gehaald.<br/>
<br/>
Voor een overzicht en analyse ga je naar <a href="{{$urlLogin}}">Test-Correct</a><br/>
<br/>
Test-Correct supportteam
</td>
</tr>
@stop