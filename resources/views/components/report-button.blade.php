@props(['action', 'id' => 'reportModal'])

<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#{{ $id }}">
    Report this post
</button>

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ $action }}">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5">Report this post</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <x-input-label for="{{ $id }}_reason" value="Reason" />
                        <select id="{{ $id }}_reason" name="reason" class="form-select" required>
                            <option value="spam">Spam</option>
                            <option value="inappropriate">Inappropriate content</option>
                            <option value="fraud">Fraud</option>
                            <option value="false_claim">False claim</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <x-input-label for="{{ $id }}_details" value="Details (optional)" />
                        <textarea id="{{ $id }}_details" name="details" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
