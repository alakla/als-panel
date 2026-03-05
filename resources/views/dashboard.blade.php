<x-app-layout>
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold">Dashboard</h4>
            <p class="text-muted">Willkommen, {{ Auth::user()->name }}</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Mitarbeitende</p>
                            <h3 class="fw-bold mb-0">—</h3>
                        </div>
                        <span class="fs-2 text-primary">&#128100;</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Auftraggeber</p>
                            <h3 class="fw-bold mb-0">—</h3>
                        </div>
                        <span class="fs-2 text-success">&#127970;</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Offene Zeiteintraege</p>
                            <h3 class="fw-bold mb-0">—</h3>
                        </div>
                        <span class="fs-2 text-warning">&#9201;</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Rechnungen</p>
                            <h3 class="fw-bold mb-0">—</h3>
                        </div>
                        <span class="fs-2 text-danger">&#128196;</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
