@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            {{ __("teacher_in_test_school_tries_to_upload.blade.Geacht supportteam") }},<br/>
            <br/>
            {{ __("teacher_in_test_school_tries_to_upload.blade.docent in demo-school wilt toets/klas uploaden") }},
            <br/>
                <tr>
                    <td>school_location</td>
                    <td>{{ $demo->school_location }}</td>
                </tr>
                <tr>
                    <td>website_url</td>
                    <td>{{ $demo->website_url }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.adres") }}</td>
                    <td>{{ $demo->address }} {{ $demo->house_number }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.postcode") }}</td>
                    <td>{{ $demo->postcode }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.Plaatsnaam") }}</td>
                    <td>{{ $demo->city }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.Aanhef") }}</td>
                    <td>{{ $demo->gender }}</td>
                </tr>
                @if($demo->gender == 'Other')
                    <tr>
                        <td>{{ __("teacher_in_test_school_tries_to_upload.blade.Aanhef anders") }}</td>
                        <td>{{ $demo->gender_different }}</td>
                    </tr>
                @endif
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.name") }}</td>
                    <td>{{ $demo->name_first }} {{ $demo->name_suffix }} {{ $demo->name }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.Email") }}</td>
                    <td>{{ $demo->username }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.Mobiel nummer") }}</td>
                    <td>{{ $demo->mobile }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.Vakken (niveau)") }}</td>
                    <td>{{ $demo->subjects }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.Hoe kent u ons") }}</td>
                    <td>{{ $demo->how_did_you_hear_about_test_correct }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.Opmerkingen") }}</td>
                    <td>{{ $demo->remarks }}</td>
                </tr>
                <tr>
                    <td>{{ __("teacher_in_test_school_tries_to_upload.blade.Tijdstip van aanmaken") }}</td>
                    <td>{{ $demo->created_at }}</td>
                </tr>
            </table>

            {{ __("teacher_in_test_school_tries_to_upload.blade.Met vriendelijke groet") }},<br/>
            {{ __("teacher_in_test_school_tries_to_upload.blade.Test-Correct supportteam") }}
        </td>
    </tr>
@stop
