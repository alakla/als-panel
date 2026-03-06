{{-- Mitarbeiterliste – Uebersicht aller Mitarbeitenden --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf mit Titel und Button zum Anlegen --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Mitarbeiterverwaltung</h4>
            <p class="text-muted small mb-0">Alle Mitarbeitenden verwalten</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.mitarbeiter.create') }}" class="btn btn-primary">
                + Neuer Mitarbeiter
            </a>
        </div>
    </div>

    {{-- Suchformular --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.mitarbeiter.index') }}" class="row g-2">
                <div class="col-md-8">
                    <input type="text" name="suche" value="{{ $suche }}"
                        class="form-control" placeholder="Suche nach Name, E-Mail oder Personalnummer...">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">Suchen</button>
                    @if($suche)
                        <a href="{{ route('admin.mitarbeiter.index') }}" class="btn btn-outline-secondary">Zuruecksetzen</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Mitarbeiterliste --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Personalnr.</th>
                        <th>Name</th>
                        <th>E-Mail</th>
                        <th>Einstellung</th>
                        <th>Stundenlohn</th>
                        <th>Status</th>
                        <th class="text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mitarbeiter as $ma)
                        <tr>
                            <td class="text-muted small">{{ $ma->personalnummer }}</td>
                            <td class="fw-semibold">{{ $ma->user->name }}</td>
                            <td>{{ $ma->user->email }}</td>
                            <td>{{ $ma->einstellungsdatum->format('d.m.Y') }}</td>
                            <td>{{ number_format($ma->stundenlohn, 2, ',', '.') }} €</td>
                            <td>
                                {{-- Statusanzeige als Badge --}}
                                @if($ma->status === 'aktiv')
                                    <span class="badge bg-success">Aktiv</span>
                                @else
                                    <span class="badge bg-secondary">Inaktiv</span>
                                @endif
                            </td>
                            <td class="text-end">
                                {{-- Detailansicht --}}
                                <a href="{{ route('admin.mitarbeiter.show', $ma) }}"
                                    class="btn btn-sm btn-outline-info">Details</a>

                                {{-- Bearbeiten --}}
                                <a href="{{ route('admin.mitarbeiter.edit', $ma) }}"
                                    class="btn btn-sm btn-outline-primary">Bearbeiten</a>

                                {{-- Deaktivieren / Reaktivieren --}}
                                <form method="POST" action="{{ route('admin.mitarbeiter.toggle', $ma) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Status wirklich aendern?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="btn btn-sm {{ $ma->status === 'aktiv' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $ma->status === 'aktiv' ? 'Deaktivieren' : 'Reaktivieren' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        {{-- Keine Mitarbeitenden gefunden --}}
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Keine Mitarbeitenden gefunden.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginierung --}}
        @if($mitarbeiter->hasPages())
            <div class="card-footer bg-white">
                {{ $mitarbeiter->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
