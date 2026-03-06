{{-- Detailansicht eines Auftraggebers --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf mit Titel und Bearbeiten-Button --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">{{ $auftraggeber->firmenname }}</h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('admin.auftraggeber.index') }}" class="text-decoration-none">Auftraggeberliste</a>
                &rsaquo; Details
            </p>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('admin.auftraggeber.edit', $auftraggeber) }}" class="btn btn-outline-primary btn-sm">
                Bearbeiten
            </a>
            {{-- Toggle-Formular: Auftraggeber aktivieren oder deaktivieren --}}
            <form method="POST" action="{{ route('admin.auftraggeber.toggle', $auftraggeber) }}">
                @csrf
                @method('PATCH')
                @if($auftraggeber->is_active)
                    <button type="submit" class="btn btn-outline-danger btn-sm"
                        onclick="return confirm('Auftraggeber wirklich deaktivieren?')">
                        Deaktivieren
                    </button>
                @else
                    <button type="submit" class="btn btn-outline-success btn-sm">
                        Reaktivieren
                    </button>
                @endif
            </form>
        </div>
    </div>

    <div class="row g-4">

        {{-- Stammdaten-Karte --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Stammdaten</div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:45%">Firmenname</td>
                            <td class="fw-semibold">{{ $auftraggeber->firmenname }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ansprechpartner</td>
                            <td>{{ $auftraggeber->ansprechpartner }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">E-Mail</td>
                            <td>
                                <a href="mailto:{{ $auftraggeber->email }}" class="text-decoration-none">
                                    {{ $auftraggeber->email }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Telefon</td>
                            <td>{{ $auftraggeber->telefon ?? '–' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Adresse</td>
                            <td>{{ $auftraggeber->adresse }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Stundensatz</td>
                            <td class="fw-semibold">{{ number_format($auftraggeber->stundensatz, 2, ',', '.') }} €/Std.</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($auftraggeber->is_active)
                                    <span class="badge bg-success">Aktiv</span>
                                @else
                                    <span class="badge bg-secondary">Inaktiv</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Letzte Zeiterfassungen fuer diesen Auftraggeber --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Letzte Zeiterfassungen</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Datum</th>
                                <th>Mitarbeiter</th>
                                <th>Stunden</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auftraggeber->zeiterfassungen->take(10) as $ze)
                                <tr>
                                    <td>{{ $ze->datum->format('d.m.Y') }}</td>
                                    <td>{{ $ze->mitarbeiter->user->name }}</td>
                                    <td>{{ number_format($ze->stunden, 2, ',', '.') }} Std.</td>
                                    <td>
                                        {{-- Statusanzeige als farbiges Badge --}}
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
                                        Noch keine Zeiterfassungen vorhanden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Rechnungsuebersicht fuer diesen Auftraggeber --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white fw-semibold">Rechnungen</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rechnungsnummer</th>
                                <th>Datum</th>
                                <th>Betrag</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auftraggeber->rechnungen->take(5) as $rechnung)
                                <tr>
                                    <td>{{ $rechnung->rechnungsnummer }}</td>
                                    <td>{{ $rechnung->rechnungsdatum->format('d.m.Y') }}</td>
                                    <td>{{ number_format($rechnung->gesamtbetrag, 2, ',', '.') }} €</td>
                                    <td>
                                        @if($rechnung->status === 'bezahlt')
                                            <span class="badge bg-success">Bezahlt</span>
                                        @elseif($rechnung->status === 'storniert')
                                            <span class="badge bg-danger">Storniert</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Offen</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        Noch keine Rechnungen vorhanden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</x-app-layout>
