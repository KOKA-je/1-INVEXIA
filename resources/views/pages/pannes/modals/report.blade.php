<!-- Report Problem Modal -->
<div class="modal fade" id="reportPanneModal" tabindex="-1" aria-labelledby="reportPanneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('pannes.store') }}">
            @csrf
            <input type="hidden" name="equipement_id" id="equipementIdInput">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportPanneModalLabel">Signaler une panne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mt-1 col-12">
                        <label for="lib_pan" class="form-label">Description de la panne</label>
                        <textarea class="form-control" rows="4" id="lib_pan" name="lib_pan" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Envoyer le signalement</button>
                    </div>
                </div>
        </form>
    </div>
</div>

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reportModal = document.getElementById('reportPanneModal');
            if (reportModal) {
                reportModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const equipementId = button.getAttribute('data-equipement-id');
                    const input = document.getElementById('equipementIdInput');
                    if (input) {
                        input.value = equipementId;
                    }
                });
            }
        });
    </script>
@endsection
