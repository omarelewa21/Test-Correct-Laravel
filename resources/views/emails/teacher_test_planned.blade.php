@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
    {{ __("test_planned.Beste") }} {{ $user->getNameFullAttribute() }},<br/>
<br/>
@if ($is_invigilator)
    {{ __("test_planned.test_has_scheduled_for_invigilator") }}.
@else
    {{ __("test_planned.test_has_scheduled_for_teacher") }}.
@endif
{{ __("test_planned.Op") }} {{ DateTime::createFromFormat('Y-m-d H:i:s', $testTake->time_start)->format('d-m-Y') }}  {{__('test_planned.wordt de toets')}}  "{{ $testTake->test->name }}" {{ __('test_planned.van het vak') }} {{ $testTake->test->subject->name }} {{ __("test_planned.afgenomen") }}.<br/>
<br/>

{{ $is_assignment ? __("test_planned.assignment_text_access_link") : __("test_planned.text_access_link") }}
<a href="{{$directlink}}">
    <strong>{{__("test_planned.this_link")}}</strong>
</a>
@if ($takeCode)
    {{$is_assignment ? __('test_planned.assignment_take_code') : __('test_planned.test_take_code')}}: <strong>{{$takeCode}}</strong>
@endif
<br/>
<br/>

{{ __("test_planned.Test-Correct supportteam") }}
</td>
</tr>
@stop