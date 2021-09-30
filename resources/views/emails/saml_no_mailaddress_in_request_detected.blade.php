@extends('emails.layout')

@section('content')
    <tr>
        <td colspan="999" style="padding:20px;">
            Beste Systeembeheerder,<br/>
            <br/>
            <p>Iemand van school {{ $schoolName }} heeft om {{ $timeDetected }} proberen in te loggen via een
                Entreekoppeling zonder emailadres. Deze school staat niet gemarkeerd als lvs_active_no_mail_allowed en
                is daarom geblocked.</p>
            <p>Als je niet weet wat je moet doen neem dan contact op met Martin, Erik of Carlo. </p>

            Met vriendelijke groet,<BR>
            Tech,<BR>
            PS, deze mail wordt maar 1 keer per dag per school verstuurd.<BR>
        </td>
    </tr>
@stop
