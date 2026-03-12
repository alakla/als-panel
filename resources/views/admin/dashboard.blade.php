{{-- Admin-Dashboard-Ansicht --}}
{{-- Zeigt Kennzahlen und Schnellzugriffe für den Administrator --}}
<x-app-layout>

    {{-- Seitentitel --}}
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold">Dashboard</h4>
            <p class="text-muted mb-0">Willkommen, {{ Auth::user()->name }}</p>
        </div>
        <div class="col-auto d-flex align-items-center gap-2">
            <span class="badge bg-primary">Administrator</span>
            <span class="text-muted small">
                Aktualisierung in <span id="refreshCountdown" class="fw-semibold">60</span>s
                <a href="{{ request()->fullUrl() }}" class="ms-1 text-decoration-none">&#8635;</a>
            </span>
        </div>
    </div>

    <script>
        (function () {
            var sekunden = 60;
            var anzeige  = document.getElementById('refreshCountdown');
            var intervall = setInterval(function () {
                sekunden--;
                if (anzeige) anzeige.textContent = sekunden;
                if (sekunden <= 0) { clearInterval(intervall); window.location.reload(); }
            }, 1000);
        })();
    </script>

    {{-- Hover-Effekt für klickbare KPI-Karten + Benutzerdefinierte Farbe Lila --}}
    <style>
        .kpi-karte { transition: background-color .15s ease, box-shadow .15s ease; }
        .kpi-karte:hover { background-color: #f0f4ff !important; box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important; }

        /* Benutzerdefinierte Farben (Bootstrap hat kein eingebautes "purple"/"orange") */
        .text-purple         { color: #6f42c1 !important; }
        .btn-outline-purple  { color: #6f42c1; border-color: #6f42c1; }
        .btn-outline-purple:hover { background-color: #6f42c1; border-color: #6f42c1; color: #fff; }

        .text-orange         { color: #fd7e14 !important; }
        .btn-outline-orange  { color: #fd7e14; border-color: #fd7e14; }
        .btn-outline-orange:hover { background-color: #fd7e14; border-color: #fd7e14; color: #fff; }
    </style>

    {{-- KPI-Karten: Wichtigste Kennzahlen auf einen Blick --}}
    <div class="row g-3 mb-4">

        {{-- Karte: Offene Aufträge (warten auf Freigabe) --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 kpi-karte"
                 style="cursor:pointer" onclick="window.location='{{ route('admin.auftraege.index', ['status' => 'bestaetigt']) }}'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Offene Aufträge</p>
                        <h3 class="fw-bold mb-0 text-orange">{{ $offeneZeiteintraege }}</h3>
                    </div>
                    <div class="fs-1 text-orange opacity-25">&#9201;</div>
                </div>
            </div>
        </div>

        {{-- Karte: Aktive Mitarbeitende --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 kpi-karte"
                 style="cursor:pointer" onclick="window.location='{{ route('admin.mitarbeiter.index') }}'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Aktive Mitarbeitende</p>
                        <h3 class="fw-bold mb-0 text-primary">{{ $mitarbeiterCount }}</h3>
                    </div>
                    <div class="fs-1 text-primary opacity-25">&#128100;</div>
                </div>
            </div>
        </div>

        {{-- Karte: Aktive Auftraggeber --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 kpi-karte"
                 style="cursor:pointer" onclick="window.location='{{ route('admin.auftraggeber.index') }}'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Aktive Auftraggeber</p>
                        <h3 class="fw-bold mb-0 text-success">{{ $auftraggeberCount }}</h3>
                    </div>
                    <div class="fs-1 text-success opacity-25">&#127970;</div>
                </div>
            </div>
        </div>

        {{-- Karte: Rechnungen diesen Monat --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 kpi-karte"
                 style="cursor:pointer" onclick="window.location='{{ route('admin.rechnungen.index') }}'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Rechnungen ({{ now()->format('M Y') }})</p>
                        <h3 class="fw-bold mb-0 text-purple">{{ $rechnungenMonat }}</h3>
                    </div>
                    <div class="fs-1 text-purple opacity-25">&#128196;</div>
                </div>
            </div>
        </div>

    </div>

    {{-- Schnellzugriff-Buttons --}}
    <div class="row g-3">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Schnellzugriff</div>
                <div class="card-body d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.auftraege.create') }}" class="btn btn-outline-orange btn-sm">+ Auftrag erstellen</a>
                    <a href="{{ route('admin.mitarbeiter.create') }}" class="btn btn-outline-primary btn-sm">+ Mitarbeiter anlegen</a>
                    <a href="{{ route('admin.auftraggeber.create') }}" class="btn btn-outline-success btn-sm">+ Auftraggeber anlegen</a>
                    <a href="{{ route('admin.rechnungen.create') }}" class="btn btn-outline-purple btn-sm">Rechnung erstellen</a>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
