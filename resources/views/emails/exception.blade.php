@extends('emails.layout')

@section('content')
<tr>
<td colspan="999" style="padding:20px;">
Error: {{ $errMessage }}<br/>
@if(isset($file) && isset($lineNr))
<br/>
{{ $file }}:{{ $lineNr }}
<br/>
@endif
@if(isset($details) && count($details))
<b>Details</b>
<ul>
@foreach($details as $key => $value)
    <li>
        {{ $key }}<br />
        {!! (is_string($value) ? $value : json_encode($value)) !!} }}
    </li>
@endforeach
</ul>
@endif
@if(isset($server) && count($server))
<br />
    <b>{{ __("exception.Server gegevens") }}</b><br />
    <ul>
        @foreach($server as $key => $value)
            <li>
                <b>{{ $key }}</b><br />
                {!! (is_string($value) ? $value : json_encode($value)) !!}
            </li>
        @endforeach
    </ul>
@endif
</td>
</tr>
@endsection