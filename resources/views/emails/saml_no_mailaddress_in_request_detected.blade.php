@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            {{ __('saml_no_mailaddress_in_request_detected.Beste Systeembeheerder') }},<br/>
            <br/>
            <p>{{ __('saml_no_mailaddress_in_request_detected.Iemand van school') }} {{ $schoolName }} {{ __('saml_no_mailaddress_in_request_detected.heeft om') }} {{ $timeDetected }} {{ __('saml_no_mailaddress_in_request_detected.proberen in te loggen via een') }}
                {{ __('saml_no_mailaddress_in_request_detected.Entreekoppeling zonder emailadres. Deze school staat niet gemarkeerd als lvs_active_no_mail_allowed en') }}
                {{ __('saml_no_mailaddress_in_request_detected.is daarom geblocked.') }}</p>
            <p>{{ __('saml_no_mailaddress_in_request_detected.Als je niet weet wat je moet doen neem dan contact op met Martin, Erik of Carlo.') }} </p>
            <p>
                <pre>
                {!! var_export($attr,true) !!}
                </pre>
            </p>

            {{ __('saml_no_mailaddress_in_request_detected.Met vriendelijke groet,') }}<BR>
            {{ __('saml_no_mailaddress_in_request_detected.Tech,') }}<BR>
            {{ __('saml_no_mailaddress_in_request_detected.PS, deze mail wordt maar 1 keer per dag per school verstuurd.') }}<BR>
        </td>
    </tr>
@stop
