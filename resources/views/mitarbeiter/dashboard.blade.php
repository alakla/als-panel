{{-- Mitarbeiter-Dashboard --}}
{{-- Startseite für angemeldete Mitarbeitende --}}
<x-app-layout>

    {{-- Seitentitel --}}
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold">Dashboard</h4>
            <p class="text-muted mb-0">Willkommen, {{ Auth::user()->name }}</p>
        </div>
        <div class="col-auto d-flex align-items-center gap-2">
            <span class="badge bg-success">Mitarbeiter</span>
            {{-- Auto-Refresh-Anzeige --}}
            <span class="text-muted small">
                Aktualisierung in <span id="refreshCountdown" class="fw-semibold">60</span>s
                <a href="{{ request()->fullUrl() }}" class="ms-1 text-decoration-none">&#8635;</a>
            </span>
        </div>
    </div>

    <script>
        // Dashboard automatisch alle 60 Sekunden aktualisieren
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

    {{-- Hover-Effekt für KPI-Karten + Benutzerdefinierte Farbe Orange --}}
    <style>
        .kpi-karte { transition: background-color .15s ease, box-shadow .15s ease; }
        .kpi-karte:hover { background-color: #f0f4ff !important; box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important; }
        .text-orange { color: #fd7e14 !important; }
    </style>

    {{-- KPI-Karten --}}
    <div class="row g-3 mb-4">

        {{-- Karte: Ausstehende Aufträge (noch nicht bestätigt) --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 kpi-karte"
                 style="cursor:pointer" onclick="window.location='{{ route('mitarbeiter.auftraege.index') }}?status=gesendet'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Ausstehende Aufträge</p>
                        <h3 class="fw-bold mb-0 text-orange">{{ $ausstehend }}</h3>
                    </div>
                    <div class="fs-1 text-orange opacity-25">&#128336;</div>
                </div>
            </div>
        </div>

        {{-- Karte: Freigegebene Einträge diesen Monat --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 kpi-karte"
                 style="cursor:pointer" onclick="window.location='{{ route('mitarbeiter.auftraege.index') }}?status=freigegeben'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Freigegeben ({{ now()->format('M Y') }})</p>
                        <h3 class="fw-bold mb-0 text-success">{{ $freigegebeneEintraege }}</h3>
                    </div>
                    <div class="fs-1 text-success opacity-25">&#10003;</div>
                </div>
            </div>
        </div>

        {{-- Karte: Stunden diesen Monat --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 kpi-karte"
                 style="cursor:pointer" onclick="window.location='{{ route('mitarbeiter.auftraege.index') }}'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Stunden ({{ now()->format('M Y') }})</p>
                        <h3 class="fw-bold mb-0 text-primary">
                            {{ number_format($stundenMonat, 2, ',', '.') }}
                        </h3>
                    </div>
                    <div class="fs-1 text-primary opacity-25">&#9201;</div>
                </div>
            </div>
        </div>

    </div>

    {{-- Letzte Aufträge --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Letzte Aufträge</span>
                    <a href="{{ route('mitarbeiter.auftraege.index') }}" class="small text-decoration-none">Alle anzeigen &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Datum</th>
                                <th>Auftraggeber</th>
                                <th>Arbeitszeit</th>
                                <th>Tätigkeit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($letzteAuftraege as $auftrag)
                                <tr>
                                    <td>{{ $auftrag->datum->format('d.m.Y') }}</td>
                                    <td>{{ $auftrag->auftraggeber->firmenname }}</td>
                                    <td>{{ $auftrag->vonFormatiert() }} – {{ $auftrag->bisFormatiert() }}</td>
                                    <td>{{ $auftrag->taetigkeit->name }}</td>
                                    <td>
                                        @if($auftrag->status === 'gesendet')
                                            <span class="badge badge-status badge-orange">Ausstehend</span>
                                        @elseif($auftrag->status === 'bestaetigt')
                                            <span class="badge badge-status bg-secondary">Bestätigt</span>
                                        @elseif($auftrag->status === 'freigegeben')
                                            <span class="badge badge-status bg-success">Freigegeben</span>
                                        @elseif($auftrag->status === 'abgelehnt')
                                            <span class="badge badge-status bg-danger">Abgelehnt</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Noch keine Aufträge vorhanden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Stammdaten-Karte --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Meine Daten</div>
                <div class="card-body">
                    <table class="table table-borderless mb-0 small">
                        <tr>
                            <td class="text-muted">Personalnr.</td>
                            <td class="fw-semibold">{{ Auth::user()->mitarbeiter->personalnummer }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">E-Mail</td>
                            <td>{{ Auth::user()->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Eingestellt</td>
                            <td>{{ Auth::user()->mitarbeiter->einstellungsdatum->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @php $maStatus = Auth::user()->mitarbeiter->status; @endphp
                                <span class="badge {{ $maStatus === 'aktiv' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($maStatus) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
