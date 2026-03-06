{{-- Zeiterfassungs-Uebersicht des Mitarbeitenden --}}
{{-- Zeigt alle eigenen Zeiteintraege mit Filter- und Loeschfunktion --}}
<x-app-layout>

    {{-- Seitenkopf mit Titel und Button fuer neuen Eintrag --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Meine Zeiterfassung</h4>
            <p class="text-muted small mb-0">Eigene Arbeitszeiten verwalten</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('mitarbeiter.zeiterfassung.create') }}" class="btn btn-primary">
                + Neuer Eintrag
            </a>
        </div>
    </div>

    {{-- Zusammenfassungskarte: Gesamtstunden des aktuellen Filters --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Stunden im ausgewaehlten Zeitraum</p>
                        <h3 class="fw-bold mb-0 text-primary">
                            {{ number_format($gesamtstunden, 2, ',', '.') }} Std.
                        </h3>
                    </div>
                    <div class="fs-1 text-primary opacity-25">&#9201;</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filterformular: Nach Monat und Auftraggeber filtern --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('mitarbeiter.zeiterfassung.index') }}" class="row g-2 align-items-end">
                {{-- Monatsfilter --}}
                <div class="col-md-4">
                    <label class="form-label small text-muted">Monat</label>
                    <input type="month" name="monat" value="{{ $monat }}" class="form-control">
                </div>
                {{-- Auftraggeberfilter --}}
                <div class="col-md-4">
                    <label class="form-label small text-muted">Auftraggeber</label>
                    <select name="auftraggeber_id" class="form-select">
                        <option value="">Alle Auftraggeber</option>
                        @foreach($auftraggeber as $ag)
                            <option value="{{ $ag->id }}" {{ $auftraggeberId == $ag->id ? 'selected' : '' }}>
                                {{ $ag->firmenname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">Filtern</button>
                    <a href="{{ route('mitarbeiter.zeiterfassung.index') }}" class="btn btn-outline-secondary">Zuruecksetzen</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Erfolgsmeldung nach Aktionen --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Liste der Zeiteintraege --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Datum</th>
                        <th>Auftraggeber</th>
                        <th>Stunden</th>
                        <th>Beschreibung</th>
                        <th>Status</th>
                        <th class="text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($zeiterfassungen as $ze)
                        <tr>
                            <td>{{ $ze->datum->format('d.m.Y') }}</td>
                            <td>{{ $ze->auftraggeber->firmenname }}</td>
                            <td>{{ number_format($ze->stunden, 2, ',', '.') }} Std.</td>
                            <td class="text-muted small">
                                {{ $ze->beschreibung ? Str::limit($ze->beschreibung, 50) : '–' }}
                            </td>
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
                            <td class="text-end">
                                {{-- Bearbeiten und Loeschen nur fuer offene Eintraege --}}
                                @if($ze->status === 'offen')
                                    <a href="{{ route('mitarbeiter.zeiterfassung.edit', $ze) }}"
                                       class="btn btn-sm btn-outline-primary">Bearbeiten</a>
                                    <form method="POST"
                                          action="{{ route('mitarbeiter.zeiterfassung.destroy', $ze) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Diesen Eintrag wirklich loeschen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Loeschen</button>
                                    </form>
                                @else
                                    <span class="text-muted small">Gesperrt</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Keine Zeiteintraege fuer den ausgewaehlten Zeitraum gefunden.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginierung --}}
        @if($zeiterfassungen->hasPages())
            <div class="card-footer bg-white">
                {{ $zeiterfassungen->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
