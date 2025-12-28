<div class="modal fade" id="diagPanModal{{ $panne->id }}" tabindex="-1"
    aria-labelledby="diagPanModalLabel{{ $panne->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="{{ route('pannes.update', $panne->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="diagPanModalLabel{{ $panne->id }}">Diagnostic de la panne</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mt-3 col-12">
                        <label for="lib_cat" class="form-label">Catégorie de panne</label>
                        <select name="lib_cat" id="lib_cat" class="form-select">
                            <option value="">-- Choisir --</option>
                            <option value="Materielle">Matérielle</option>
                            <option value="Logicielle">Logicielle</option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label for="diag_pan_{{ $panne->id }}" class="form-label">Diagnostic</label>
                        <textarea name="diag_pan" id="diag_pan_{{ $panne->id }}" class="form-control" rows="4"
                            placeholder="La panne diagnostiqué est..." required>{{ old('diag_pan', $panne->diag_pan) }}</textarea>
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
