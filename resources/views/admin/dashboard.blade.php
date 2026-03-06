{{-- Admin-Dashboard-Ansicht --}}
{{-- Zeigt Kennzahlen und Schnellzugriffe fuer den Administrator --}}
<x-app-layout>

    {{-- Seitentitel --}}
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold">Dashboard</h4>
            <p class="text-muted mb-0">Willkommen, {{ Auth::user()->name }}</p>
        </div>
        <div class="col-auto">
            <span class="badge bg-primary">Administrator</span>
        </div>
    </div>

    {{-- KPI-Karten: Wichtigste Kennzahlen auf einen Blick --}}
    <div class="row g-3 mb-4">

        {{-- Karte: Aktive Mitarbeitende --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Aktive Mitarbeitende</p>
                        <h3 class="fw-bold mb-0 text-primary">{{ $mitarbeiterCount }}</h3>
                    </div>
                    <div class="fs-1 text-primary opacity-25">&#128100;</div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('admin.mitarbeiter.index') }}" class="small text-decoration-none">Alle anzeigen &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Karte: Aktive Auftraggeber --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Aktive Auftraggeber</p>
                        <h3 class="fw-bold mb-0 text-success">{{ $auftraggeberCount }}</h3>
                    </div>
                    <div class="fs-1 text-success opacity-25">&#127970;</div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('admin.auftraggeber.index') }}" class="small text-decoration-none">Alle anzeigen &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Karte: Offene Zeiteintraege (warten auf Freigabe) --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Offene Zeiteintraege</p>
                        <h3 class="fw-bold mb-0 text-warning">{{ $offeneZeiteintraege }}</h3>
                    </div>
                    <div class="fs-1 text-warning opacity-25">&#9201;</div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('admin.zeitfreigabe.index') }}" class="small text-decoration-none">Freigeben &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Karte: Rechnungen diesen Monat --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Rechnungen ({{ now()->format('M Y') }})</p>
                        <h3 class="fw-bold mb-0 text-danger">{{ $rechnungenMonat }}</h3>
                    </div>
                    <div class="fs-1 text-danger opacity-25">&#128196;</div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('admin.rechnungen.index') }}" class="small text-decoration-none">Alle anzeigen &rarr;</a>
                </div>
            </div>
        </div>

    </div>

    {{-- Schnellzugriff-Buttons --}}
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Schnellzugriff</div>
                <div class="card-body d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.mitarbeiter.create') }}" class="btn btn-outline-primary btn-sm">+ Mitarbeiter anlegen</a>
                    <a href="{{ route('admin.auftraggeber.create') }}" class="btn btn-outline-success btn-sm">+ Auftraggeber anlegen</a>
                    <a href="{{ route('admin.zeitfreigabe.index') }}" class="btn btn-outline-warning btn-sm">Zeitfreigabe</a>
                    <a href="{{ route('admin.rechnungen.create') }}" class="btn btn-outline-danger btn-sm">Rechnung erstellen</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">System-Info</div>
                <div class="card-body">
                    <small class="text-muted">
                        Laravel {{ app()->version() }} &bull;
                        PHP {{ PHP_VERSION }} &bull;
                        {{ now()->format('d.m.Y H:i') }} Uhr
                    </small>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
