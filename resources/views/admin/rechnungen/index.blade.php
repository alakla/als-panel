{{-- Rechnungsliste – Uebersicht aller erstellten Rechnungen --}}
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

    {{-- Rechnungstabelle: Alle Rechnungen, neueste zuerst (orderBy created_at desc via latest()) --}}
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
                                    <span class="badge badge-status bg-warning text-dark">Offen</span>
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
        {{-- Paginierung: 15 Rechnungen pro Seite (definiert in RechnungController::index) --}}
        @if($rechnungen->hasPages())
            <div class="card-footer bg-white">
                {{ $rechnungen->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
