{{-- Rechnungsvorschau: Zeigt Zeiteintraege und berechnete Betraege vor der Erstellung --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold mb-0">Rechnungsvorschau</h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('admin.rechnungen.index') }}" class="text-decoration-none">Rechnungen</a>
                &rsaquo;
                <a href="{{ route('admin.rechnungen.create') }}" class="text-decoration-none">Neue Rechnung</a>
                &rsaquo; Vorschau
            </p>
        </div>
    </div>

    <div class="row g-4">

        {{-- Zeiteintraege-Tabelle --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    Freigegebene Zeiteintraege: {{ $auftraggeber->firmenname }}
                    <span class="text-muted fw-normal small ms-2">
                        {{ \Carbon\Carbon::parse($request->zeitraum_von)->format('d.m.Y') }}
                        – {{ \Carbon\Carbon::parse($request->zeitraum_bis)->format('d.m.Y') }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Datum</th>
                                <th>Mitarbeiter</th>
                                <th class="text-end">Stunden</th>
                                <th class="text-end">Betrag (netto)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($zeiterfassungen as $ze)
                                <tr>
                                    <td>{{ $ze->datum->format('d.m.Y') }}</td>
                                    <td>{{ $ze->mitarbeiter->user->name }}</td>
                                    <td class="text-end">{{ number_format($ze->stunden, 2, ',', '.') }}</td>
                                    <td class="text-end">
                                        {{ number_format($ze->stunden * $auftraggeber->stundensatz, 2, ',', '.') }} €
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Keine freigegebenen Zeiteintraege im gewaehlten Zeitraum vorhanden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($zeiterfassungen->isNotEmpty())
                            <tfoot class="table-light fw-semibold">
                                <tr>
                                    <td colspan="2">Gesamt</td>
                                    <td class="text-end">{{ number_format($gesamtstunden, 2, ',', '.') }} Std.</td>
                                    <td class="text-end">{{ number_format($nettobetrag, 2, ',', '.') }} €</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Zusammenfassung und Erstellungs-Button --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Rechnungszusammenfassung</div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Auftraggeber</td>
                            <td class="fw-semibold">{{ $auftraggeber->firmenname }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Stundensatz</td>
                            <td>{{ number_format($auftraggeber->stundensatz, 2, ',', '.') }} €/Std.</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Gesamtstunden</td>
                            <td>{{ number_format($gesamtstunden, 2, ',', '.') }} Std.</td>
                        </tr>
                        <tr><td colspan="2"><hr class="my-1"></td></tr>
                        <tr>
                            <td class="text-muted">Nettobetrag</td>
                            <td>{{ number_format($nettobetrag, 2, ',', '.') }} €</td>
                        </tr>
                        <tr>
                            <td class="text-muted">MwSt (19%)</td>
                            <td>{{ number_format($mwstBetrag, 2, ',', '.') }} €</td>
                        </tr>
                        <tr>
                            <td colspan="2"><hr class="my-1"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Gesamtbetrag</td>
                            <td class="fw-bold text-primary fs-5">
                                {{ number_format($gesamtbetrag, 2, ',', '.') }} €
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer bg-white border-top">
                    @if($zeiterfassungen->isEmpty())
                        <div class="alert alert-warning small mb-0">
                            Keine Zeiteintraege vorhanden. Rechnung kann nicht erstellt werden.
                        </div>
                    @else
                        {{-- Rechnung endgueltig erstellen --}}
                        <form method="POST" action="{{ route('admin.rechnungen.store') }}">
                            @csrf
                            <input type="hidden" name="auftraggeber_id" value="{{ $auftraggeber->id }}">
                            <input type="hidden" name="zeitraum_von" value="{{ $request->zeitraum_von }}">
                            <input type="hidden" name="zeitraum_bis" value="{{ $request->zeitraum_bis }}">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"
                                    onclick="return confirm('Rechnung jetzt erstellen und PDF generieren?')">
                                    Rechnung erstellen & PDF generieren
                                </button>
                                <a href="{{ route('admin.rechnungen.create') }}"
                                   class="btn btn-outline-secondary btn-sm">Zurueck</a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

    </div>

</x-app-layout>
