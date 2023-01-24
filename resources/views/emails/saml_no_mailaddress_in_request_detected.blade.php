@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            {{ __('saml_no_mailaddress_in_request_detected.Beste Systeembeheerder') }},<br/>
            <br/>
            <p>
                {{ __('saml_no_mailaddress_in_request_detected.Iemand van school :schoolName heeft om :timeDetected geprobeerd om in te loggen via Entree zonder emailadres. Deze school staat niet gemarkeerd als lvs_active_no_mail_allowed en is daarom geblokkeerd',['schoolName' => $schoolName, 'timeDetected' => $timeDetected->format('H:i d-m-Y')]) }}
            </p>
            <p>{{ __('saml_no_mailaddress_in_request_detected.Als je niet weet wat je moet doen neem dan contact op met Martin, Erik of Robert.') }} </p>
            <p>
                @foreach($attr as $key => $value)
                    {{ $key }} => @if(is_array($value)) {{ $value[0] }} @else {{ $value }} @endif<br/>
                @endforeach
            </p>

            {{ __('saml_no_mailaddress_in_request_detected.Met vriendelijke groet,') }}<BR>
            {{ __('saml_no_mailaddress_in_request_detected.Tech') }}<BR>
        </td>
    </tr>
@stop
