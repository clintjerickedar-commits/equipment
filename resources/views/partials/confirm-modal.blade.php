<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px">
            <div class="modal-body p-4">
                <h5 class="mb-2" id="confirmTitle">Are you sure?</h5>
                <p class="text-muted mb-2" id="confirmMessage">This action cannot be undone.</p>
                <div class="confirm-name d-none" id="confirmName"></div>
                <p class="small text-muted d-none" id="confirmNote"></p>
                <div class="mb-3"></div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light no-loading" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger no-loading" id="confirmOk">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>
