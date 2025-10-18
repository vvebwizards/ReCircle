<div id="genericConfirmPopup" class="custom-popup hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="popup-content bg-white rounded-lg shadow-lg p-6 w-96 mx-4">
        <h3 class="popup-title text-lg font-semibold mb-2">Confirmation</h3>
        <p class="popup-message mb-4">Are you sure?</p>
        <div class="flex justify-end gap-3">
            <button type="button" id="modalCancelBtn" class="btn-cancel px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-colors">
                Cancel
            </button>
            <button type="button" id="modalConfirmBtn" class="btn-confirm px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                Confirm
            </button>
        </div>
    </div>
</div>