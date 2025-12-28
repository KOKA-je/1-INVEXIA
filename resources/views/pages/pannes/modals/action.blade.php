<div class="modal fade" id="actionPanModal{{ $panne->id }}" tabindex="-1"
    aria-labelledby="diagPanModalLabel{{ $panne->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('pannes.update', $panne->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="actionPanModalLabel{{ $panne->id }}">Action corrective</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mt-3">
                        {{-- <label for="action_pan_{{ $panne->id }}" class="form-label">Diagnostic</label> --}}
                        <textarea name="action_pan" id="diag_pan_{{ $panne->id }}" class="form-control" rows="3"
                            placeholder="Décrivez l'action corrective'..." required>{{ old('action_pan', $panne->action_pan) }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        Valider
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>

                </div>
            </form>
        </div>
    </div>
</div>
