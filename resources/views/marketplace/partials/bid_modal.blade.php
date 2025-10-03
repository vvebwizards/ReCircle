<div class="modal hidden" id="marketplaceBidModal" role="dialog" aria-modal="true" aria-labelledby="bidModalTitle">
    <div class="modal-card bid-modal-card">
        <div class="modal-header">
            <h2 id="bidModalTitle"><i class="fa-solid fa-gavel"></i> Make a Bid</h2>
            <button type="button" class="modal-close" data-close>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="bidForm" novalidate>
                <input type="hidden" name="waste_item_id" id="bidWasteItemId" />
                <div class="form-row">
                    <label for="bidAmount">Amount</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0.01" name="amount" id="bidAmount" required placeholder="0.00" />
                        <select name="currency" id="bidCurrency" required>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="TND">TND</option>
                        </select>
                    </div>
                    <small class="error" data-error-for="amount"></small>
                </div>
                <div class="form-row">
                    <label for="bidNotes">Notes (optional)</label>
                    <textarea name="notes" id="bidNotes" rows="3" placeholder="Add any additional details..."></textarea>
                    <small class="error" data-error-for="notes"></small>
                </div>
                <div class="form-row bid-feedback" id="bidFeedback" hidden></div>
                <div class="form-actions">
                    <button type="button" class="btn ghost" data-close>Cancel</button>
                    <button type="submit" class="btn primary" id="bidSubmitBtn">
                        <span class="btn-label-default"><i class="fa-solid fa-paper-plane"></i> Submit Bid</span>
                        <span class="btn-label-loading" hidden><i class="fa-solid fa-circle-notch fa-spin"></i> Submitting...</span>
                    </button>
                </div>
            </form>
            <div class="bid-existing" id="bidExistingWrap" hidden>
                <h3 class="section-title">Current Bids</h3>
                <div id="bidExistingList" class="bid-list"></div>
            </div>
        </div>
    </div>
</div>
