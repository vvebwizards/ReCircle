<!DOCTYPE html>
<html>
<head>
    <title>Pusher Test with Authentication</title>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Log CSRF token for debugging
            console.log("CSRF Token:", document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            // Set up axios with CSRF token
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Enable pusher logging for debugging
            Pusher.logToConsole = true;

            // Connect to pusher
            const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                forceTLS: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }
            });

            // Test connection
            pusher.connection.bind('connected', function() {
                document.getElementById('connection-status').textContent = 'Connected to Pusher!';
                document.getElementById('connection-status').style.color = 'green';
            });

            pusher.connection.bind('error', function(err) {
                document.getElementById('connection-status').textContent = 'Connection Error: ' + err.message;
                document.getElementById('connection-status').style.color = 'red';
                console.error('Connection error:', err);
            });

            // Subscribe to public channel
            const publicChannel = pusher.subscribe('waste-item.1.bids');
            publicChannel.bind('pusher:subscription_succeeded', function() {
                document.getElementById('public-channel-status').textContent = 'Subscribed to public channel!';
                document.getElementById('public-channel-status').style.color = 'green';
            });

            publicChannel.bind('bid-submitted', function(data) {
                addMessage('Public Channel Event:', data);
            });

            // Subscribe to private channel (will require auth)
            const privateChannel = pusher.subscribe('private-user.1.bids');
            privateChannel.bind('pusher:subscription_succeeded', function() {
                document.getElementById('private-channel-status').textContent = 'Subscribed to private channel!';
                document.getElementById('private-channel-status').style.color = 'green';
            });

            privateChannel.bind('pusher:subscription_error', function(status) {
                document.getElementById('private-channel-status').textContent = 'Private channel subscription failed: ' + status;
                document.getElementById('private-channel-status').style.color = 'red';
                console.error('Private channel subscription error:', status);
            });

            privateChannel.bind('bid-submitted', function(data) {
                addMessage('Private Channel Event:', data);
            });

            // Function to add a message to the log
            function addMessage(prefix, data) {
                const messagesDiv = document.getElementById('messages');
                const messageElement = document.createElement('div');
                messageElement.innerHTML = `<strong>${prefix}</strong> ${JSON.stringify(data)}`;
                messagesDiv.prepend(messageElement);
            }

            // Trigger test event button
            document.getElementById('test-event').addEventListener('click', function() {
                axios.post('/broadcast-test', {})
                    .then(response => {
                        addMessage('Test event triggered:', response.data);
                    })
                    .catch(error => {
                        addMessage('Error triggering test event:', error.response?.data || error.message);
                    });
            });
        });
    </script>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .status-panel { margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .messages { margin-top: 20px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; max-height: 400px; overflow-y: auto; }
        .messages div { margin-bottom: 5px; padding: 5px; border-bottom: 1px solid #eee; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Pusher Test with Authentication</h1>
    
    <div class="status-panel">
        <h3>Connection Status</h3>
        <p id="connection-status">Connecting to Pusher...</p>
        <p id="public-channel-status">Not subscribed to public channel</p>
        <p id="private-channel-status">Not subscribed to private channel</p>
    </div>
    
    <button id="test-event">Trigger Test Event</button>
    
    <div class="messages" id="messages">
        <h3>Event Log</h3>
    </div>
</body>
</html>