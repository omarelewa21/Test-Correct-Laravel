<div
    x-data="{deviceId: {{ $deviceId }}, sessionId: {{ $sessionId }}, code: '{{ md5('1.1') }}'}"
    x-init="(() => {
           Intense = new IntenseWrapper({
                api_key: 'api_key', // This is a public key which will be provided by Intense.
                app: 'name of the app that implements Intense. example: TC@1.0.0',
                debug: true // If true, all debug data will be written to console.log().
            })
            .onCallibrated(function(type) {
{{--                document.getElementById('typecalibration_complete_button').classList.add('primary-button');--}}
            })
            .onError(function(e, msg) {

                // So far, the only available value for 'msg' is 'unavailable', meaning that the given interface/method cannot be used.
                // If no error handler is registered, all errors will be written to console.log.

                switch(e) {
                    case 'start':
                        console.log('Intense: Could not start recording because it was '+msg);
                        break;
                    case 'pause':
                        console.log('Intense: Could not pause recording because it was '+msg);
                        break;
                    case 'resume':
                        console.log('Intense: Could not resume recording because it was '+msg);
                        break;
                    case 'end':
                        console.log('Intense: Could not end recording because it was '+msg);
                        break;
                    case 'network':
                        console.log('Intense: Could not send data over network because it was '+msg);
                        break;
                    default:
                        console.log('Intense: Unknown error occured!');
                }

            }).onData(function(data) {
                // This function is called when data is sent to the Intense server. data contains the data that is being sent.
                console.log('Data sent to Intense', data);
            }).onStart(function() {
                console.log('Intense started recording');
            }).onPause(function() {
                console.log('Intense paused recording');
            }).onResume(function() {
                console.log('Intense resumed recording');
            }).onEnd(function() {
                console.log('Intense ended recording');
            });


            /** devivceId = userId, sessionId = $testParticipantId; **/
            console.log([{{ $deviceId }},{{ $sessionId }}, '{{ md5('1.1') }}']);
            console.log([deviceId,sessionId, code]);
            //Intense.resetDefaults();
            Intense.start(deviceId.toString(), sessionId.toString(), code);
            console.dir(Intense);
        })();"
>
</div>

@push('scripts')
    <script src="https://education.intense.solutions/collector/latest.uncompressed.js"></script>
@endpush
