// Import WebSocket functionality
import { listenForBids } from './bidSocket';

document.addEventListener('DOMContentLoaded', () => {
    // Track active subscriptions
    const bidSubscriptions = new Map();
    
    // Initialize bid cards with real-time updates
    initializeBidCards();
    
    function initializeBidCards() {
        // Find all bid cards on the page
        const bidCards = document.querySelectorAll('[data-waste-item-id]');
        
        // Set up real-time updates for each bid card
        bidCards.forEach(card => {
            const wasteItemId = card.getAttribute('data-waste-item-id');
            if (!wasteItemId) return;
            
            // Unsubscribe any existing subscription for this item
            if (bidSubscriptions.has(wasteItemId)) {
                bidSubscriptions.get(wasteItemId).unsubscribe();
            }
            
            // Subscribe to bid updates for this waste item
            const subscription = listenForBids(wasteItemId, (data) => {
                updateBidCard(card, data);
            });
            
            bidSubscriptions.set(wasteItemId, subscription);
        });
    }
    
    function updateBidCard(card, bidData) {
        if (!card || !bidData) return;
        
        console.log('[DashboardBids] Updating card with data:', bidData);
        
        // Find the bid counter element
        const bidCounter = card.querySelector('.bid-counter');
        if (bidCounter) {
            const currentCountText = bidCounter.textContent.trim();
            const currentCount = parseInt(currentCountText, 10) || 0;
            // Only increment if this is a new bid (not an update)
            if (bidData.status === 'pending') {
                const newCount = currentCount + 1;
                bidCounter.textContent = `${newCount} ${newCount === 1 ? 'Bid' : 'Bids'}`;
            }
        }
        
        // Check if we need to add the bid to the list
        if (bidData.status === 'pending') {
            addBidToList(card, bidData);
        }
        
        // Add a pulsing animation to notify user of update
        card.classList.add('card-updated');
        setTimeout(() => {
            card.classList.remove('card-updated');
        }, 2000);
    }
    
    function addBidToList(card, bidData) {
        // Find the bid list in the card
        const bidList = card.querySelector('ul.bid-rows');
        if (!bidList) {
            console.warn('[DashboardBids] Bid list not found in card');
            return;
        }
        
        // Check if bid already exists to avoid duplicates
        if (bidList.querySelector(`[data-bid-id="${bidData.id}"]`)) {
            console.log('[DashboardBids] Bid already exists in list, not adding duplicate');
            return;
        }
        
        // Create new bid row
        const bidRow = document.createElement('li');
        bidRow.className = `bid-row status-${bidData.status}`;
        bidRow.setAttribute('data-bid-id', bidData.id);
        
        // Format time ago text
        const timeAgo = '1 minute ago'; // Simplified for real-time updates
        
        // Create the HTML content for the bid row
        bidRow.innerHTML = `
            <div class="row-main">
                <span class="amt">${Number(bidData.amount).toFixed(2)} ${bidData.currency}</span>
                <span class="maker">by ${bidData.maker?.name || 'Unknown'}</span>
            </div>
            <div class="row-meta">
                <span class="time">${timeAgo}</span>
                <span class="pill p-${bidData.status}">${bidData.status.toUpperCase()}</span>
            </div>
            <div class="row-actions">
                <button type="button" class="btn-accept-bid" data-accept-bid 
                    data-bid-id="${bidData.id}" 
                    data-item-id="${bidData.waste_item_id}" 
                    data-maker="${bidData.maker?.name || 'Unknown'}" 
                    data-amount="${Number(bidData.amount).toFixed(2)}" 
                    data-currency="${bidData.currency}">
                    Accept
                </button>
            </div>
        `;
        
        // Add bid to top of the list
        if (bidList.children.length > 0) {
            bidList.insertBefore(bidRow, bidList.firstChild);
        } else {
            bidList.appendChild(bidRow);
        }
        
        // Highlight the new bid
        bidRow.classList.add('bid-new-animation');
        setTimeout(() => {
            bidRow.classList.remove('bid-new-animation');
        }, 2000);
    }
    
    // Clean up subscriptions when page is unloaded
    window.addEventListener('beforeunload', () => {
        bidSubscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
        bidSubscriptions.clear();
    });
});