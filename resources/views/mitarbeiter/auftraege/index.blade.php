{{-- Mitarbeiter: Eigene Arbeitsauftraege --}}
{{-- Zeigt alle zugewiesenen Auftraege; noch nicht bestaetgte koennen bestaetigt werden --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Meine Auftraege</h4>
            <p class="text-muted small mb-0">Vom Admin zugewiesene Arbeitseinsaetze</p>
        </div>
        {{-- Auto-Refresh-Anzeige: Seite aktualisiert sich alle 60 Sekunden --}}
        <div class="col-auto">
            <span class="text-muted small">
                Aktualisierung in <span id="refreshCountdown" class="fw-semibold">60</span>s
                <a href="{{ request()->fullUrl() }}" class="ms-1 text-decoration-none small">&#8635;</a>
            </span>
        </div>
    </div>

    <script>
        // Seite automatisch alle 60 Sekunden aktualisieren
        // Zeigt einen Countdown-Timer damit der Nutzer weiss wann die Seite neu geladen wird
        (function () {
            var sekunden = 60;
            var anzeige  = document.getElementById('refreshCountdown');

            var intervall = setInterval(function () {
                sekunden--;
                if (anzeige) anzeige.textContent = sekunden;

                if (sekunden <= 0) {
                    clearInterval(intervall);
                    window.location.reload();
                }
            }, 1000);
        })();
    </script>

    {{-- Info-Hinweis: Erklaerung fuer Mitarbeitenden --}}
    <div class="alert alert-info border-0 shadow-sm mb-4 small">
        <strong>Ablauf:</strong> Auftraege mit Status <em>Gesendet</em> sind noch offen.
        Bestaetigen Sie einen Auftrag nach der Ausfuehrung – ein Zeiteintrag wird automatisch erstellt
        und an den Admin zur Freigabe weitergeleitet.
    </div>

    {{-- Filterleiste --}}
    <form method="GET" action="{{ route('mitarbeiter.auftraege.index') }}" id="filterForm" class="mb-3">
        <div class="d-flex gap-2 align-items-center flex-wrap">

            {{-- Statusfilter (Mitarbeiter-Bezeichnungen) --}}
            <select name="status" class="form-select form-select-sm" style="width:160px"
                    onchange="document.getElementById('filterForm').submit()">
                <option value="alle"        {{ $status === 'alle'        ? 'selected' : '' }}>Alle Status</option>
                <option value="gesendet"    {{ $status === 'gesendet'    ? 'selected' : '' }}>Ausstehend</option>
                <option value="bestaetigt"  {{ $status === 'bestaetigt'  ? 'selected' : '' }}>Bestaetigt</option>
                <option value="freigegeben" {{ $status === 'freigegeben' ? 'selected' : '' }}>Freigegeben</option>
                <option value="abgelehnt"   {{ $status === 'abgelehnt'   ? 'selected' : '' }}>Abgelehnt</option>
            </select>

            {{-- Monatsfilter: Monat- und Jahr-Auswahl --}}
            @php
                [$filterJahr, $filterMonat] = explode('-', $monat);
                $monate = [1=>'Januar',2=>'Februar',3=>'März',4=>'April',5=>'Mai',6=>'Juni',
                           7=>'Juli',8=>'August',9=>'September',10=>'Oktober',11=>'November',12=>'Dezember'];
            @endphp
            <select name="monat_nr" class="form-select form-select-sm" style="width:130px"
                    onchange="document.getElementById('filterForm').submit()">
                @foreach($monate as $nr => $name)
                    <option value="{{ $nr }}" {{ (int)$filterMonat === $nr ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select name="jahr" class="form-select form-select-sm" style="width:90px"
                    onchange="document.getElementById('filterForm').submit()">
                @foreach($jahre as $j)
                    <option value="{{ $j }}" {{ (int)$filterJahr === $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>

            {{-- Filter zuruecksetzen --}}
            @if($status !== 'alle' || $monat !== now()->format('Y-m'))
                <a href="{{ route('mitarbeiter.auftraege.index') }}" class="btn btn-outline-secondary btn-sm">
                    Zuruecksetzen
                </a>
            @endif

        </div>
    </form>

    {{-- Auftraege-Tabelle --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Datum</th>
                        <th>Auftraggeber</th>
                        <th>Arbeitszeit</th>
                        <th>Pause</th>
                        <th>Taetigkeit</th>
                        <th>Stunden</th>
                        <th style="width:120px">Status</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auftraege as $auftrag)
                        <tr>
                            <td>{{ $auftrag->datum->format('d.m.Y') }}</td>
                            <td>{{ $auftrag->auftraggeber->firmenname }}</td>
                            {{-- Von-Bis Zeitbereich --}}
                            <td>{{ $auftrag->vonFormatiert() }} – {{ $auftrag->bisFormatiert() }}</td>
                            {{-- Pause --}}
                            <td>
                                @if($auftrag->pause)
                                    <span class="text-muted small">30 Min.</span>
                                @else
                                    <span class="text-muted small">–</span>
                                @endif
                            </td>
                            <td>{{ $auftrag->taetigkeit->name }}</td>
                            {{-- Berechnete Stunden (inkl. Pausenabzug) --}}
                            <td>{{ number_format($auftrag->berechneteStunden(), 2, ',', '.') }} Std.</td>
                            {{-- Statusanzeige (Mitarbeiter-Bezeichnungen) --}}
                            <td>
                                @if($auftrag->status === 'gesendet')
                                    <span class="badge bg-primary badge-status">Ausstehend</span>
                                @elseif($auftrag->status === 'bestaetigt')
                                    <span class="badge bg-secondary badge-status">Bestaetigt</span>
                                @elseif($auftrag->status === 'freigegeben')
                                    <span class="badge bg-success badge-status">Freigegeben</span>
                                @elseif($auftrag->status === 'abgelehnt')
                                    <span class="badge bg-danger badge-status">Abgelehnt</span>
                                @endif
                            </td>
                            <td>
                                {{-- Bestaetigen-Button nur fuer ausstehende Auftraege --}}
                                @if($auftrag->status === 'gesendet')
                                    <form method="POST"
                                          action="{{ route('mitarbeiter.auftraege.bestaetigen', $auftrag) }}"
                                          class="d-inline"
                                          data-confirm="Auftrag bestaetigen? Ein Zeiteintrag wird automatisch erstellt."
                                          data-confirm-btn="success">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success">
                                            Bestaetigen
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">–</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Keine Auftraege vorhanden.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginierung --}}
        @if($auftraege->hasPages())
            <div class="card-footer bg-white">
                {{ $auftraege->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
