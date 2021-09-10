
<div>
@push('scripts')
        <script>

            // Enable pusher logging - don't include this in production
            // Pusher.logToConsole = true;
            //
            // var pusher = new Pusher('fc18ed69b446aeb8c8a5', {
            //     cluster: 'eu'
            // });
            //
            // var channel = pusher.subscribe('my-channel');
            // channel.bind('my-event', function(data) {
            //     alert(JSON.stringify(data));
            // });
            // Echo.channel('my-channel')
            //     .listen('.my-event', (e) => {
            //         console.log(e);
            //     });

            // var channel = window.Echo.channel('my-channel');
            // channel.listen('.my-event', function(data) {
            //     alert(JSON.stringify(data));
            // });
        </script>
@endpush
    <h1>Pusher Test</h1>
    <p>
        Try publishing an event to channel <code>my-channel</code>
        with event name <code>my-event</code>.

        <button x-data="{}" wire:click="doeiets">hans</button>
    </p>
</div>
