{{-- Auftraggeberliste – Uebersicht aller Kundenunternehmen --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Auftraggeberverwaltung</h4>
            <p class="text-muted small mb-0">Alle Kundenunternehmen verwalten</p>
        </div>
        <div class="col-auto d-flex align-items-center gap-2">
            <span class="text-muted small">
                Aktualisierung in <span id="refreshCountdown" class="fw-semibold">60</span>s
                <a href="{{ request()->fullUrl() }}" class="ms-1 text-decoration-none">&#8635;</a>
            </span>
            <a href="{{ route('admin.auftraggeber.create') }}" class="btn btn-primary">
                + Neuer Auftraggeber
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

    {{-- Suchformular --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.auftraggeber.index') }}" class="row g-2" id="suchformAuftraggeber">
                <div class="col-md-8">
                    <input type="text" name="suche" id="sucheInput" value="{{ $suche }}"
                        class="form-control" placeholder="Suche nach Firmenname, Ansprechpartner oder E-Mail..."
                        oninput="debounceSubmit('suchformAuftraggeber')">
                </div>
                <div class="col-auto">
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
                            <td>
                                @if($ag->is_active)
                                    <span class="badge badge-status bg-success">Aktiv</span>
                                @else
                                    <span class="badge badge-status bg-secondary">Inaktiv</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.auftraggeber.show', $ag) }}"
                                    class="btn btn-sm btn-outline-info">Details</a>
                                <a href="{{ route('admin.auftraggeber.edit', $ag) }}"
                                    class="btn btn-sm btn-outline-primary">Bearbeiten</a>
                                <form method="POST" action="{{ route('admin.auftraggeber.destroy', $ag) }}"
                                    class="d-inline"
                                    data-confirm="Status wirklich aendern?" data-confirm-btn="danger">
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
                            <td colspan="5" class="text-center text-muted py-4">
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
