{{-- Mitarbeiter-Dashboard --}}
{{-- Startseite fuer angemeldete Mitarbeitende --}}
<x-app-layout>

    {{-- Seitentitel --}}
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold">Dashboard</h4>
            <p class="text-muted mb-0">Willkommen, {{ Auth::user()->name }}</p>
        </div>
        <div class="col-auto">
            <span class="badge bg-success">Mitarbeiter</span>
        </div>
    </div>

    {{-- KPI-Karten: Eigene Kennzahlen auf einen Blick --}}
    <div class="row g-3 mb-4">

        {{-- Karte: Stunden diesen Monat --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Stunden ({{ now()->format('M Y') }})</p>
                        <h3 class="fw-bold mb-0 text-primary">
                            {{ number_format($stundenMonat, 2, ',', '.') }}
                        </h3>
                    </div>
                    <div class="fs-1 text-primary opacity-25">&#9201;</div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('mitarbeiter.zeiterfassung.index') }}" class="small text-decoration-none">Alle anzeigen &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Karte: Offene Eintraege (noch nicht freigegeben) --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Offene Eintraege</p>
                        <h3 class="fw-bold mb-0 text-warning">{{ $offeneEintraege }}</h3>
                    </div>
                    <div class="fs-1 text-warning opacity-25">&#128336;</div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('mitarbeiter.zeiterfassung.index') }}" class="small text-decoration-none">Pruefen &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Karte: Freigegebene Eintraege diesen Monat --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Freigegeben ({{ now()->format('M Y') }})</p>
                        <h3 class="fw-bold mb-0 text-success">{{ $freigegebeneEintraege }}</h3>
                    </div>
                    <div class="fs-1 text-success opacity-25">&#10003;</div>
                </div>
            </div>
        </div>

    </div>

    {{-- Letzte Zeiteintraege --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Letzte Zeiteintraege</span>
                    <a href="{{ route('mitarbeiter.zeiterfassung.create') }}" class="btn btn-primary btn-sm">
                        + Neuer Eintrag
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Datum</th>
                                <th>Auftraggeber</th>
                                <th>Stunden</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($letzteEintraege as $ze)
                                <tr>
                                    <td>{{ $ze->datum->format('d.m.Y') }}</td>
                                    <td>{{ $ze->auftraggeber->firmenname }}</td>
                                    <td>{{ number_format($ze->stunden, 2, ',', '.') }} Std.</td>
                                    <td>
                                        @if($ze->status === 'freigegeben')
                                            <span class="badge bg-success">Freigegeben</span>
                                        @elseif($ze->status === 'abgelehnt')
                                            <span class="badge bg-danger">Abgelehnt</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Offen</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        Noch keine Zeiteintraege vorhanden.
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
                                <span class="badge bg-success">
                                    {{ ucfirst(Auth::user()->mitarbeiter->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
