{{-- Auftraggeberliste – Uebersicht aller Kundenunternehmen --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Auftraggeberverwaltung</h4>
            <p class="text-muted small mb-0">Alle Kundenunternehmen verwalten</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.auftraggeber.create') }}" class="btn btn-primary">
                + Neuer Auftraggeber
            </a>
        </div>
    </div>

    {{-- Suchformular --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.auftraggeber.index') }}" class="row g-2">
                <div class="col-md-8">
                    <input type="text" name="suche" value="{{ $suche }}"
                        class="form-control" placeholder="Suche nach Firmenname, Ansprechpartner oder E-Mail...">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">Suchen</button>
                    @if($suche)
                        <a href="{{ route('admin.auftraggeber.index') }}" class="btn btn-outline-secondary">Zuruecksetzen</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Auftraggeberliste --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Firmenname</th>
                        <th>Ansprechpartner</th>
                        <th>E-Mail</th>
                        <th>Stundensatz</th>
                        <th>Status</th>
                        <th class="text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($auftraggeber as $ag)
                        <tr>
                            <td class="fw-semibold">{{ $ag->firmenname }}</td>
                            <td>{{ $ag->ansprechpartner }}</td>
                            <td>{{ $ag->email }}</td>
                            <td>{{ number_format($ag->stundensatz, 2, ',', '.') }} €/Std.</td>
                            <td>
                                @if($ag->is_active)
                                    <span class="badge bg-success">Aktiv</span>
                                @else
                                    <span class="badge bg-secondary">Inaktiv</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.auftraggeber.show', $ag) }}"
                                    class="btn btn-sm btn-outline-info">Details</a>
                                <a href="{{ route('admin.auftraggeber.edit', $ag) }}"
                                    class="btn btn-sm btn-outline-primary">Bearbeiten</a>
                                <form method="POST" action="{{ route('admin.auftraggeber.destroy', $ag) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Status wirklich aendern?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-sm {{ $ag->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $ag->is_active ? 'Deaktivieren' : 'Reaktivieren' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Keine Auftraggeber gefunden.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($auftraggeber->hasPages())
            <div class="card-footer bg-white">
                {{ $auftraggeber->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
