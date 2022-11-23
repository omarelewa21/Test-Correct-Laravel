@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            {{ __("teacher_registered_entree.Geacht supportteam") }},<br/>
            <br/>
            {{ __("teacher_registered_entree.Een nieuwe docent heeft zich via Entree aangemeld voor Test-Correct.") }}
            <br/>
            <table>
                <tr>
                    <td>{{ __("teacher_registered_entree.School naam") }}</td>
                    <td>{{ $schoolLocation->name }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered_entree.Aanhef") }}</td>
                    @if($user->gender == 'Male')
                        <td>{{ __("teacher_registered_entree.Meneer") }}</td>
                    @elseif($user->gender == 'Female')
                        <td>{{ __("teacher_registered_entree.Mevrouw") }}</td>
                    @elseif($user->gender == '')
                        <td>{{$user->gender_different}}</td>
                    @endif
                </tr>
                <tr>
                    <td>{{ __("teacher_registered_entree.Naam") }}</td>
                    <td>{{ $user->name_first }} {{ $user->name_suffix }} {{ $user->name }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered_entree.Email") }}</td>
                    <td>{{ $user->username }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered_entree.Vakken (niveau)") }}</td>
                    <td>
                        <ul>
                        @foreach($subjects as $subject)
                                <li>{{ $subject->name }}</li>
                        @endforeach
                        </ul>
                    </td>
                </tr>
            </table>

            {{ __("teacher_registered_entree.Met vriendelijke groet") }},<br/>
            {{ __("teacher_registered_entree.Test-Correct supportteam") }}
        </td>
    </tr>
@stop
