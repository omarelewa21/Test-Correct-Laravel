<x-layouts.pdf-test-print :$titleForPdfPage>
    <center>
        <div style="width: 80%">
            <x-logos.test-correct-small/>

            <div style="padding: 40px 0; font-weight: 900; font-size: 24px">{{ $titleForPdfPage }}</div>

            <table width="100%"
                   cellpadding="10"
                   cellspacing="0"
                   style="page-break-inside: avoid;">
                <tr>
                    <th style="border: 1px solid  #c3d0ed;" align="left">@lang('test-take.Student')</th>
                    <th style="border: 1px solid  #c3d0ed;" align="left">@lang('student.grade')</th>
                </tr>
                @foreach($testParticipants as $participant)
                    <tr>
                        <td style="border: 1px solid  #c3d0ed;">{{ $participant->user->fullName }}</td>
                        <td style="border: 1px solid  #c3d0ed;">{{ $participant->rating ?? '-' }}</td>
                    </tr>
                @endforeach

            </table>
        </div>
    </center>

</x-layouts.pdf-test-print>
