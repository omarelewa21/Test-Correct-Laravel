@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            {{ __("teacher_registered.Geacht supportteam") }},<br/>
            <br/>
            {{ __("teacher_registered.Een nieuwe docent heeft zich met onderstaande gegevens aangemeld voor Test-Correct.") }}
            <br/>
            @if($withDuplicateEmailAddress)
                <h3>“{{ __("teacher_registered.Let op! De docent heeft een e-mailadres opgegeven dat al bestaat in de database!") }}"</h3>
                {{ __("teacher_registered.Er is daarom ook geen nieuw account voor deze docent aangemaakt") }}.<br/>
            @endif
            @if($invitedByColleagueWithSameDomain)
                <h3>{{ __("teacher_registered.Uitgenodigd door een collega van hetzelfde domein") }}.</h3>
            @endif
            <table>
                <tr>
                    <td>{{ __("teacher_registered.School Locatie") }}</td>
                    <td>{{ $demo->school_location }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Website Url") }}</td>
                    <td>{{ $demo->website_url }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Adres") }}</td>
                    <td>{{ $demo->address }} {{ $demo->house_number }}</td>

                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Postcode") }}</td>
                    <td>{{ $demo->postcode }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Plaatsnaam") }}</td>
                    <td>{{ $demo->city }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Aanhef") }}</td>
                    @if($demo->gender == 'male')
                        <td>{{ __("teacher_registered.Meneer") }}</td>
                    @elseif($demo->gender == 'female')
                        <td>{{ __("teacher_registered.Mevrouw") }}</td>
                    @elseif($demo->gender == 'different')
                        <td>{{$demo->gender_different}}</td>
                    @endif
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Naam") }}</td>
                    <td>{{ $demo->name_first }} {{ $demo->name_suffix }} {{ $demo->name }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Email") }}</td>
                    <td>{{ $demo->username }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Afkorting") }}</td>
                    <td>{{ $demo->abbreviation }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Mobiel nummer") }}</td>
                    <td>{{ $demo->mobile }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Level") }}</td>
                    <td>{{ $demo->level }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Vakken (niveau)") }}</td>
                    <td>{{ $demo->subjects }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Hoe kent u ons") }}</td>
                    <td>{{ $demo->how_did_you_hear_about_test_correct }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Opmerkingen") }}</td>
                    <td>{{ $demo->remarks }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_registered.Tijdstip van aanmaken") }}</td>
                    <td>{{ $demo->created_at }}</td>
                </tr>
            </table>

            {{ __("teacher_registered.Met vriendelijke groet") }},<br/>
            {{ __("teacher_registered.Test-Correct supportteam") }}
        </td>
    </tr>
@stop
