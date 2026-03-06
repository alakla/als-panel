{{-- Rechnungsliste – Uebersicht aller erstellten Rechnungen --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Rechnungen</h4>
            <p class="text-muted small mb-0">Alle erstellten Rechnungen verwalten</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.rechnungen.create') }}" class="btn btn-primary">
                + Neue Rechnung erstellen
            </a>
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
                            <td class="fw-semibold">{{ $rechnung->rechnungsnummer }}</td>
                            <td>{{ $rechnung->auftraggeber->firmenname }}</td>
                            <td class="text-muted small">
                                {{ $rechnung->zeitraum_von->format('d.m.Y') }}
                                – {{ $rechnung->zeitraum_bis->format('d.m.Y') }}
                            </td>
                            <td>{{ $rechnung->rechnungsdatum?->format('d.m.Y') ?? '–' }}</td>
                            <td class="text-end">
                                {{ number_format($rechnung->nettobetrag, 2, ',', '.') }} €
                            </td>
                            <td class="text-end fw-semibold">
                                {{ number_format($rechnung->gesamtbetrag, 2, ',', '.') }} €
                            </td>
                            <td>
                                @if($rechnung->status === 'bezahlt')
                                    <span class="badge bg-success">Bezahlt</span>
                                @elseif($rechnung->status === 'storniert')
                                    <span class="badge bg-danger">Storniert</span>
                                @else
                                    <span class="badge bg-warning text-dark">Offen</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.rechnungen.show', $rechnung) }}"
                                   class="btn btn-sm btn-outline-info">Details</a>
                                @if($rechnung->pdf_pfad)
                                    <a href="{{ route('admin.rechnungen.download', $rechnung) }}"
                                       class="btn btn-sm btn-outline-secondary">PDF</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Noch keine Rechnungen erstellt.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rechnungen->hasPages())
            <div class="card-footer bg-white">
                {{ $rechnungen->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
