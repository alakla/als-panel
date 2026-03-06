{{-- Zeitfreigabe-Uebersicht fuer Administratoren --}}
{{-- Zeigt alle Zeiteintraege der Mitarbeitenden zur Genehmigung oder Ablehnung --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Zeitfreigabe</h4>
            <p class="text-muted small mb-0">Zeiteintraege der Mitarbeitenden pruefen und genehmigen</p>
        </div>
    </div>

    {{-- Filterformular --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.zeitfreigabe.index') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="alle" {{ $status === 'alle' ? 'selected' : '' }}>Alle</option>
                        <option value="offen" {{ $status === 'offen' ? 'selected' : '' }}>Offen</option>
                        <option value="freigegeben" {{ $status === 'freigegeben' ? 'selected' : '' }}>Freigegeben</option>
                        <option value="abgelehnt" {{ $status === 'abgelehnt' ? 'selected' : '' }}>Abgelehnt</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Mitarbeiter</label>
                    <select name="mitarbeiter_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Alle Mitarbeiter</option>
                        @foreach($mitarbeiter as $ma)
                            <option value="{{ $ma->id }}" {{ $mitarbeiterId == $ma->id ? 'selected' : '' }}>
                                {{ $ma->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Auftraggeber</label>
                    <select name="auftraggeber_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Alle Auftraggeber</option>
                        @foreach($auftraggeber as $ag)
                            <option value="{{ $ag->id }}" {{ $auftraggeberId == $ag->id ? 'selected' : '' }}>
                                {{ $ag->firmenname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Monat</label>
                    {{-- Monat: submit bei Aenderung --}}
                    <input type="month" name="monat" value="{{ $monat }}"
                           class="form-control form-control-sm" onchange="this.form.submit()">
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.zeitfreigabe.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{--
        Massenfreigabe-Formular: Steht AUSSERHALB der Tabelle.
        Die Checkboxen in der Tabelle verweisen per form="massenfreigabeForm"
        auf dieses Formular – so werden keine Forms verschachtelt.
    --}}
    <form method="POST"
          action="{{ route('admin.zeitfreigabe.massenfreigabe') }}"
          id="massenfreigabeForm">
        @csrf
    </form>

    {{-- Tabelle mit Zeiteintraegen --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                Zeiteintraege
                @if($status === 'offen')
                    <span class="badge bg-warning text-dark ms-1">{{ $zeiterfassungen->total() }} offen</span>
                @endif
            </span>
            @if($status === 'offen' && $zeiterfassungen->count() > 0)
                <button type="button" class="btn btn-success btn-sm" onclick="massenfreigabeAbsenden()">
                    Auswahl freigeben
                </button>
            @endif
        </div>

        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        @if($status === 'offen' || $status === 'alle')
                            <th style="width:40px">
                                <input type="checkbox" class="form-check-input" id="alleAuswaehlen">
                            </th>
                        @endif
                        <th>Datum</th>
                        <th>Mitarbeiter</th>
                        <th>Auftraggeber</th>
                        <th>Stunden</th>
                        <th>Beschreibung</th>
                        <th>Status</th>
                        @if($status === 'offen')
                            <th class="text-end">Aktionen</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($zeiterfassungen as $ze)
                        <tr>
                            @if($status === 'offen' || $status === 'alle')
                                <td>
                                    {{-- Checkbox: nur fuer offene Eintraege aktiv --}}
                                    @if($ze->status === 'offen')
                                        <input type="checkbox"
                                               class="form-check-input eintrag-checkbox"
                                               name="eintraege[]"
                                               value="{{ $ze->id }}"
                                               form="massenfreigabeForm">
                                    @endif
                                </td>
                            @endif
                            <td>{{ $ze->datum->format('d.m.Y') }}</td>
                            <td>{{ $ze->mitarbeiter->user->name }}</td>
                            <td>{{ $ze->auftraggeber->firmenname }}</td>
                            <td>{{ number_format($ze->stunden, 2, ',', '.') }} Std.</td>
                            <td class="text-muted small">
                                {{ $ze->beschreibung ? Str::limit($ze->beschreibung, 40) : '–' }}
                            </td>
                            <td>
                                @if($ze->status === 'freigegeben')
                                    <span class="badge bg-success">Freigegeben</span>
                                @elseif($ze->status === 'abgelehnt')
                                    <span class="badge bg-danger">Abgelehnt</span>
                                @else
                                    <span class="badge bg-warning text-dark">Offen</span>
                                @endif
                            </td>
                            @if($status === 'offen' || $status === 'alle')
                                <td class="text-end">
                                    {{-- Aktionen nur fuer offene Eintraege anzeigen --}}
                                    @if($ze->status === 'offen')
                                        <form method="POST"
                                              action="{{ route('admin.zeitfreigabe.freigeben', $ze) }}"
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                Freigeben
                                            </button>
                                        </form>
                                        <form method="POST"
                                              action="{{ route('admin.zeitfreigabe.ablehnen', $ze) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Eintrag wirklich ablehnen?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Ablehnen
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">–</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($status === 'offen' || $status === 'alle') ? 8 : 7 }}"
                                class="text-center text-muted py-4">
                                Keine Eintraege gefunden.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($zeiterfassungen->hasPages())
            <div class="card-footer bg-white">
                {{ $zeiterfassungen->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <script>
        // Alle-auswaehlen Checkbox steuert alle Einzelcheckboxen
        const alleCheckbox = document.getElementById('alleAuswaehlen');
        if (alleCheckbox) {
            alleCheckbox.addEventListener('change', function () {
                document.querySelectorAll('.eintrag-checkbox').forEach(cb => {
                    cb.checked = alleCheckbox.checked;
                });
            });
        }

        // Massenfreigabe: prueft ob mindestens eine Checkbox ausgewaehlt ist
        function massenfreigabeAbsenden() {
            const ausgewaehlt = document.querySelectorAll('.eintrag-checkbox:checked');
            if (ausgewaehlt.length === 0) {
                alert('Bitte mindestens einen Eintrag auswaehlen.');
                return;
            }
            if (confirm(ausgewaehlt.length + ' Eintrag/Eintraege freigeben?')) {
                document.getElementById('massenfreigabeForm').submit();
            }
        }
    </script>

</x-app-layout>
