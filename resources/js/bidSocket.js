// Import Echo from Laravel Echo
import Echo from 'laravel-echo';

// Import Pusher JS
import Pusher from 'pusher-js';

// Configure Echo
window.Pusher = Pusher;

// Add CSRF token to all requests
console.log('[BidSocket] Module loaded - ' + new Date().toISOString());
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

// Import axios if not already available
if (!window.axios) {
    import('axios').then(module => {
        window.axios = module.default;
        if (csrfToken) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        }
    });
}

// Debug environment variables
console.log('Pusher Environment Variables:', {
    VITE_PUSHER_APP_KEY: import.meta.env.VITE_PUSHER_APP_KEY,
    VITE_PUSHER_APP_CLUSTER: import.meta.env.VITE_PUSHER_APP_CLUSTER
});

// Use hardcoded values for now since the env variables might not be working
const pusherKey = 'c8652c49f017e104a528'; // Your actual Pusher key
const pusherCluster = 'eu'; // Changed from mt1 to eu as per your Pusher settings

try {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: pusherCluster,
        forceTLS: true,
        encrypted: true,
        // Auth configuration for private and presence channels
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        }
    });
    console.log('Echo initialized successfully with key:', pusherKey);
} catch (error) {
    console.error('Failed to initialize Echo:', error);
}

/**
 * Handle real-time bid updates for a specific waste item
 * @param {number} wasteItemId - The ID of the waste item to listen for bids on
 * @param {Function} callback - Function to call when a new bid is received
 * @returns {Object} - Subscription object with unsubscribe method
 */
export function listenForBids(wasteItemId, callback) {
    if (!wasteItemId) return { unsubscribe: () => {} };
    
    console.log(`[BidSocket] Subscribing to waste-item.${wasteItemId}.bids`);
    
    const channel = window.Echo.channel(`waste-item.${wasteItemId}.bids`);
    
    // Listen for the custom event name 'bid-submitted' instead of the full class name
    const subscription = channel.listen('.bid-submitted', (data) => {
        console.log('[BidSocket] Received bid data:', data);
        if (typeof callback === 'function') {
            callback(data);
        }
    });
    
    return {
        unsubscribe: () => {
            console.log(`[BidSocket] Unsubscribing from waste-item.${wasteItemId}.bids`);
            window.Echo.leave(`waste-item.${wasteItemId}.bids`);
        }
    };
}

/**
 * Handle real-time bid updates for a specific user
 * @param {number} userId - The ID of the user to listen for bids on
 * @param {Function} callback - Function to call when a new bid is received
 * @returns {Object} - Subscription object with unsubscribe method
 */
export function listenForUserBids(userId, callback) {
    if (!userId) return { unsubscribe: () => {} };
    
    console.log(`[BidSocket] Subscribing to private-user.${userId}.bids`);
    
    const channel = window.Echo.private(`user.${userId}.bids`);
    
    const subscription = channel.listen('.bid-submitted', (data) => {
        console.log('[BidSocket] Received user bid data:', data);
        if (typeof callback === 'function') {
            callback(data);
        }
    });
    
    return {
        unsubscribe: () => {
            console.log(`[BidSocket] Unsubscribing from private-user.${userId}.bids`);
            window.Echo.leave(`private-user.${userId}.bids`);
        }
    };
}