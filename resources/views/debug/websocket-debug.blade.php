<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WebSocket Debug Console</title>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <style>
        body { 
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { margin-top: 0; color: #333; }
        .control-panel {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .log-container {
            background: #1e1e1e;
            color: #ddd;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            height: 400px;
            overflow-y: auto;
            margin-bottom: 15px;
        }
        .log-entry {
            margin-bottom: 8px;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
        }
        .log-entry pre {
            margin: 5px 0;
            white-space: pre-wrap;
        }
        .timestamp {
            color: #888;
            font-size: 0.8em;
        }
        .channel {
            color: #4caf50;
        }
        .event {
            color: #2196f3;
        }
        .error {
            color: #f44336;
        }
        input, button, select {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover { background: #45a049; }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .row {
            margin-bottom: 15px;
        }
        .clear-btn {
            background: #f44336;
        }
        .clear-btn:hover { background: #d32f2f; }
    </style>
</head>
<body>
    <div class="container">
        <h1>WebSocket Debug Console</h1>
        
        <div class="control-panel">
            <div>
                <div class="row">
                    <label for="pusherKey">Pusher App Key</label>
                    <input type="text" id="pusherKey" value="{{ env('PUSHER_APP_KEY') }}" />
                </div>
                
                <div class="row">
                    <label for="cluster">Cluster</label>
                    <input type="text" id="cluster" value="{{ env('PUSHER_APP_CLUSTER') }}" />
                </div>
                
                <div class="row">
                    <label for="channel">Channel to Listen</label>
                    <input type="text" id="channel" value="waste-item.1.bids" />
                </div>
                
                <div class="row">
                    <label for="event">Event Name</label>
                    <input type="text" id="event" value=".bid-submitted" />
                </div>
                
                <div class="row">
                    <button id="connectBtn">Connect & Listen</button>
                </div>
                
                <div class="row">
                    <button id="disconnectBtn">Disconnect</button>
                </div>
            </div>
            
            <div>
                <div class="row">
                    <label for="testWasteItemId">Test Waste Item ID</label>
                    <input type="number" id="testWasteItemId" value="1" />
                </div>
                
                <div class="row">
                    <label for="testAmount">Test Bid Amount</label>
                    <input type="number" id="testAmount" value="250.00" />
                </div>
                
                <div class="row">
                    <label for="testCurrency">Test Currency</label>
                    <select id="testCurrency">
                        <option value="EUR">EUR</option>
                        <option value="USD">USD</option>
                        <option value="TND">TND</option>
                    </select>
                </div>
                
                <div class="row">
                    <button id="triggerTestBtn">Trigger Test Bid Event</button>
                </div>
                
                <div class="row">
                    <button id="clearBtn" class="clear-btn">Clear Log</button>
                </div>
            </div>
        </div>
        
        <div class="log-container" id="logContainer"></div>
    </div>
    
    <script>
        let pusher = null;
        let channel = null;
        const logContainer = document.getElementById('logContainer');
        
        function log(message, type = 'info') {
            const entry = document.createElement('div');
            entry.className = `log-entry ${type}`;
            
            const timestamp = document.createElement('span');
            timestamp.className = 'timestamp';
            timestamp.textContent = new Date().toISOString() + ' ';
            entry.appendChild(timestamp);
            
            if (typeof message === 'object') {
                const pre = document.createElement('pre');
                pre.textContent = JSON.stringify(message, null, 2);
                entry.appendChild(pre);
            } else {
                entry.appendChild(document.createTextNode(message));
            }
            
            logContainer.appendChild(entry);
            logContainer.scrollTop = logContainer.scrollHeight;
        }
        
        document.getElementById('connectBtn').addEventListener('click', function() {
            const pusherKey = document.getElementById('pusherKey').value;
            const cluster = document.getElementById('cluster').value;
            const channelName = document.getElementById('channel').value;
            const eventName = document.getElementById('event').value;
            
            if (pusher) {
                pusher.disconnect();
                log('Disconnected from previous Pusher instance');
            }
            
            try {
                // Enable debugging
                Pusher.logToConsole = true;
                
                pusher = new Pusher(pusherKey, {
                    cluster: cluster,
                    forceTLS: true
                });
                
                log(`Connecting to Pusher with key: ${pusherKey}, cluster: ${cluster}`);
                
                pusher.connection.bind('connected', function() {
                    log('✅ Connected to Pusher!', 'success');
                });
                
                pusher.connection.bind('error', function(err) {
                    log(`❌ Connection Error: ${err.message}`, 'error');
                });
                
                // Subscribe to channel
                channel = pusher.subscribe(channelName);
                log(`Subscribing to channel: <span class="channel">${channelName}</span>`);
                
                channel.bind('pusher:subscription_succeeded', function() {
                    log(`✅ Successfully subscribed to <span class="channel">${channelName}</span>`);
                });
                
                channel.bind('pusher:subscription_error', function(status) {
                    log(`❌ Subscription Error: ${status}`, 'error');
                });
                
                // Listen for specified event
                log(`Listening for event: <span class="event">${eventName}</span>`);
                channel.bind(eventName, function(data) {
                    log(`📥 Received event: <span class="event">${eventName}</span>`);
                    log(data);
                });
                
                // Also listen for potential class name event (Laravel default format)
                const className = 'App\\Events\\BidSubmitted';
                log(`Also listening for class name event: <span class="event">${className}</span>`);
                channel.bind(className, function(data) {
                    log(`📥 Received class name event: <span class="event">${className}</span>`);
                    log(data);
                });
                
            } catch (error) {
                log(`❌ Error initializing Pusher: ${error.message}`, 'error');
            }
        });
        
        document.getElementById('disconnectBtn').addEventListener('click', function() {
            if (pusher) {
                pusher.disconnect();
                log('Disconnected from Pusher');
                pusher = null;
                channel = null;
            } else {
                log('No active Pusher connection');
            }
        });
        
        document.getElementById('triggerTestBtn').addEventListener('click', function() {
            const wasteItemId = document.getElementById('testWasteItemId').value;
            const amount = document.getElementById('testAmount').value;
            const currency = document.getElementById('testCurrency').value;
            
            log(`Triggering test bid event for waste item #${wasteItemId}`);
            
            fetch('/broadcast-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    waste_item_id: wasteItemId,
                    amount: amount,
                    currency: currency
                })
            })
            .then(response => response.json())
            .then(data => {
                log(`✅ Test event triggered: ${JSON.stringify(data)}`);
            })
            .catch(error => {
                log(`❌ Error triggering test event: ${error.message}`, 'error');
            });
        });
        
        document.getElementById('clearBtn').addEventListener('click', function() {
            logContainer.innerHTML = '';
        });
        
        // Initial log
        log('WebSocket Debug Console Loaded');
        log('Click "Connect & Listen" to start listening for events');
    </script>
</body>
</html>