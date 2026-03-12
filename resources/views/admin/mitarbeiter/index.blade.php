{{-- Mitarbeiterliste – Übersicht aller Mitarbeitenden --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf mit Titel und Button zum Anlegen --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Mitarbeiterverwaltung</h4>
            <p class="text-muted small mb-0">Alle Mitarbeitenden verwalten</p>
        </div>
        <div class="col-auto d-flex align-items-center gap-2">
            <span class="text-muted small">
                Aktualisierung in <span id="refreshCountdown" class="fw-semibold">60</span>s
                <a href="{{ request()->fullUrl() }}" class="ms-1 text-decoration-none">&#8635;</a>
            </span>
            <a href="{{ route('admin.mitarbeiter.create') }}" class="btn btn-primary">
                + Neuer Mitarbeiter
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
            <form method="GET" action="{{ route('admin.mitarbeiter.index') }}" class="row g-2" id="suchformMitarbeiter">
                <div class="col-md-8">
                    <input type="text" name="suche" id="sucheInput" value="{{ $suche }}"
                        class="form-control" placeholder="Suche nach Name, E-Mail oder Personalnummer..."
                        oninput="debounceSubmit('suchformMitarbeiter')">
                </div>
                <div class="col-auto">
                    @if($suche)
                        <a href="{{ route('admin.mitarbeiter.index') }}" class="btn btn-outline-secondary">Zurücksetzen</a>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mitarbeiter as $ma)
                        {{-- Klick auf Zeile navigiert zur Detailseite --}}
                        <tr style="cursor:pointer"
                            onclick="window.location='{{ route('admin.mitarbeiter.show', $ma) }}'">
                            <td class="text-muted small">{{ $ma->personalnummer }}</td>
                            <td class="fw-semibold">{{ $ma->user->name }}</td>
                            <td>{{ $ma->user->email }}</td>
                            <td>{{ $ma->einstellungsdatum->format('d.m.Y') }}</td>
                            <td>{{ number_format($ma->stundenlohn, 2, ',', '.') }} €</td>
                            <td>
                                {{-- Statusanzeige als Badge --}}
                                @if($ma->status === 'aktiv')
                                    <span class="badge badge-status bg-success">Aktiv</span>
                                @else
                                    <span class="badge badge-status bg-secondary">Inaktiv</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        {{-- Keine Mitarbeitenden gefunden --}}
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
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
