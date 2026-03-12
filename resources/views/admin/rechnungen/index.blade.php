{{-- Rechnungsliste – Übersicht aller erstellten Rechnungen --}}
{{-- Zugriff: Nur Administratoren (Middleware: auth + admin) --}}
{{-- Zeigt alle Rechnungen mit Betrag, Status und PDF-Download-Link --}}
<x-app-layout>

    {{-- Seitenkopf: Titel und Button zum Erstellen einer neuen Rechnung --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Rechnungen</h4>
            <p class="text-muted small mb-0">Alle erstellten Rechnungen verwalten</p>
        </div>
        <div class="col-auto d-flex align-items-center gap-2">
            <span class="text-muted small">
                Aktualisierung in <span id="refreshCountdown" class="fw-semibold">60</span>s
                <a href="{{ request()->fullUrl() }}" class="ms-1 text-decoration-none">&#8635;</a>
            </span>
            {{-- Startet den Zwei-Schritt-Prozess: Parameter → Vorschau → Erstellen --}}
            <a href="{{ route('admin.rechnungen.create') }}" class="btn btn-primary">
                + Neue Rechnung erstellen
            </a>
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

    {{-- Filterleiste: Auftraggeber, Status, Monat und Jahr --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.rechnungen.index') }}" id="filterForm">
                <div class="d-flex gap-2 align-items-center flex-wrap">

                    {{-- Filter: Auftraggeber --}}
                    <select name="auftraggeber_id" class="form-select form-select-sm" style="width:200px"
                            onchange="document.getElementById('filterForm').submit()">
                        <option value="">Alle Auftraggeber</option>
                        @foreach($auftraggeber as $ag)
                            <option value="{{ $ag->id }}" {{ $auftraggeberId == $ag->id ? 'selected' : '' }}>
                                {{ $ag->firmenname }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Filter: Status --}}
                    <select name="status" class="form-select form-select-sm" style="width:140px"
                            onchange="document.getElementById('filterForm').submit()">
                        <option value="alle"      {{ $filterStatus === 'alle'      ? 'selected' : '' }}>Alle Status</option>
                        <option value="offen"     {{ $filterStatus === 'offen'     ? 'selected' : '' }}>Offen</option>
                        <option value="bezahlt"   {{ $filterStatus === 'bezahlt'   ? 'selected' : '' }}>Bezahlt</option>
                        <option value="storniert" {{ $filterStatus === 'storniert' ? 'selected' : '' }}>Storniert</option>
                    </select>

                    {{-- Monatsfilter: Monat- und Jahr-Auswahl --}}
                    @php
                        [$filterJahrVal, $filterMonatVal] = explode('-', $monat);
                        $monate = [1=>'Januar',2=>'Februar',3=>'März',4=>'April',5=>'Mai',6=>'Juni',
                                   7=>'Juli',8=>'August',9=>'September',10=>'Oktober',11=>'November',12=>'Dezember'];
                    @endphp
                    <select name="monat_nr" class="form-select form-select-sm" style="width:130px"
                            onchange="document.getElementById('filterForm').submit()">
                        @foreach($monate as $nr => $name)
                            <option value="{{ $nr }}" {{ (int)$filterMonatVal === $nr ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="jahr" class="form-select form-select-sm" style="width:90px"
                            onchange="document.getElementById('filterForm').submit()">
                        @foreach($jahre as $j)
                            <option value="{{ $j }}" {{ (int)$filterJahrVal === $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>

                    {{-- Zurücksetzen --}}
                    @if($filterStatus !== 'alle' || $auftraggeberId || $monat !== now()->format('Y-m'))
                        <a href="{{ route('admin.rechnungen.index') }}" class="btn btn-outline-secondary btn-sm">
                            Zurücksetzen
                        </a>
                    @endif

                </div>
            </form>
        </div>
    </div>

    {{-- Rechnungstabelle --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Rechnungsnummer</th>
                        <th>Auftraggeber</th>
                        <th>Zeitraum</th>
                        <th>Rechnungsdatum</th>
                        <th class="text-end">Netto</th>
                        <th class="text-end">Gesamt (brutto)</th>
                        <th>Status</th>
                        <th class="text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rechnungen as $rechnung)
                        <tr>
                            {{-- Rechnungsnummer im Format RE-JJJJ-NNNN --}}
                            <td class="fw-semibold">{{ $rechnung->rechnungsnummer }}</td>

                            {{-- Firmenname des Auftraggebers (eager loaded mit 'with') --}}
                            <td>{{ $rechnung->auftraggeber->firmenname }}</td>

                            {{-- Abrechnungszeitraum: automatisch als Carbon-Datum gecastet --}}
                            <td class="text-muted small">
                                {{ $rechnung->zeitraum_von->format('d.m.Y') }}
                                – {{ $rechnung->zeitraum_bis->format('d.m.Y') }}
                            </td>

                            {{-- Rechnungsdatum: nullable → Nullsafe-Operator (?->) verhindert Fehler --}}
                            <td>{{ $rechnung->rechnungsdatum?->format('d.m.Y') ?? '–' }}</td>

                            {{-- Nettobetrag (Stunden x Stundensatz, ohne MwSt) --}}
                            <td class="text-end">
                                {{ number_format($rechnung->nettobetrag, 2, ',', '.') }} €
                            </td>

                            {{-- Gesamtbetrag (Brutto = Netto + 19% MwSt), fett formatiert --}}
                            <td class="text-end fw-semibold">
                                {{ number_format($rechnung->gesamtbetrag, 2, ',', '.') }} €
                            </td>

                            {{-- Zahlungsstatus als farbiges Badge --}}
                            <td>
                                @if($rechnung->status === 'bezahlt')
                                    <span class="badge badge-status bg-success">Bezahlt</span>
                                @elseif($rechnung->status === 'storniert')
                                    <span class="badge badge-status bg-danger">Storniert</span>
                                @else
                                    {{-- Standardstatus direkt nach Rechnungserstellung --}}
                                    <span class="badge badge-status badge-orange">Offen</span>
                                @endif
                            </td>

                            {{-- Aktionsbuttons: Detailansicht und PDF-Download --}}
                            <td class="text-end">
                                {{-- Detailansicht zeigt alle Rechnungsdaten und Auftraggeber-Kontakt --}}
                                <a href="{{ route('admin.rechnungen.show', $rechnung) }}"
                                   class="btn btn-sm btn-outline-info">Details</a>

                                {{-- PDF-Download: nur wenn pdf_pfad in DB gespeichert wurde --}}
                                @if($rechnung->pdf_pfad)
                                    <a href="{{ route('admin.rechnungen.download', $rechnung) }}"
                                       class="btn btn-sm btn-outline-secondary">PDF</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        {{-- Leerstatus: Noch keine Rechnungen im System --}}
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Noch keine Rechnungen erstellt.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Statusübersicht: Anzahl pro Zustand für den gewählten Monat --}}
        <div class="card-footer bg-white border-top py-2">
            <div class="d-flex gap-3 flex-wrap align-items-center">
                <span class="text-muted small">Gesamt:</span>
                @if(($statusCounts['offen'] ?? 0) > 0)
                    <span class="small"><span class="badge badge-orange me-1">{{ $statusCounts['offen'] }}</span>Offen</span>
                @endif
                @if(($statusCounts['bezahlt'] ?? 0) > 0)
                    <span class="small"><span class="badge bg-success me-1">{{ $statusCounts['bezahlt'] }}</span>Bezahlt</span>
                @endif
                @if(($statusCounts['storniert'] ?? 0) > 0)
                    <span class="small"><span class="badge bg-danger me-1">{{ $statusCounts['storniert'] }}</span>Storniert</span>
                @endif
                @if($statusCounts->isEmpty())
                    <span class="text-muted small">Keine Einträge</span>
                @endif
            </div>
        </div>

        {{-- Paginierung --}}
        @if($rechnungen->hasPages())
            <div class="card-footer bg-white">
                {{ $rechnungen->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
