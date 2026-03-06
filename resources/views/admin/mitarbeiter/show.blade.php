{{-- Detailansicht eines Mitarbeitenden --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">{{ $mitarbeiter->user->name }}</h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('admin.mitarbeiter.index') }}" class="text-decoration-none">Mitarbeiterliste</a>
                &rsaquo; Details
            </p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.mitarbeiter.edit', $mitarbeiter) }}" class="btn btn-outline-primary btn-sm">
                Bearbeiten
            </a>
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
                            <td class="text-muted" style="width:45%">Personalnummer</td>
                            <td class="fw-semibold">{{ $mitarbeiter->personalnummer }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Name</td>
                            <td>{{ $mitarbeiter->user->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">E-Mail</td>
                            <td>{{ $mitarbeiter->user->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Einstellungsdatum</td>
                            <td>{{ $mitarbeiter->einstellungsdatum->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Stundenlohn</td>
                            <td>{{ number_format($mitarbeiter->stundenlohn, 2, ',', '.') }} €</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($mitarbeiter->status === 'aktiv')
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

        {{-- Letzte Zeiterfassungen --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Letzte Zeiterfassungen</div>
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
                            @forelse($mitarbeiter->zeiterfassungen->take(10) as $ze)
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
                                        Noch keine Zeiterfassungen vorhanden.
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
