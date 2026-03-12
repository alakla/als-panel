{{-- Mitarbeiter: Eigene Arbeitsaufträge --}}
{{-- Zeigt alle zugewiesenen Aufträge; noch nicht bestätigte können bestätigt werden --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Meine Aufträge</h4>
            <p class="text-muted small mb-0">Vom Admin zugewiesene Arbeitseinsätze</p>
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
        // Zeigt einen Countdown-Timer damit der Nutzer weiß wann die Seite neu geladen wird
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

    {{-- Info-Hinweis: Erklärung für Mitarbeitenden --}}
    <div class="alert alert-info border-0 shadow-sm mb-4 small">
        <strong>Ablauf:</strong> Aufträge mit Status <em>Gesendet</em> sind noch offen.
        Bestätigen Sie einen Auftrag nach der Ausführung – ein Zeiteintrag wird automatisch erstellt
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
                <option value="bestaetigt"  {{ $status === 'bestaetigt'  ? 'selected' : '' }}>Bestätigt</option>
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

            {{-- Filter zurücksetzen --}}
            @if($status !== 'alle' || $monat !== now()->format('Y-m'))
                <a href="{{ route('mitarbeiter.auftraege.index') }}" class="btn btn-outline-secondary btn-sm">
                    Zurücksetzen
                </a>
            @endif

        </div>
    </form>

    {{-- Gesamtstunden-Zusammenfassung für aktuelle Filterauswahl --}}
    <div class="d-flex justify-content-end mb-2">
        <span class="text-muted small">
            Gesamt:
            <strong class="text-primary ms-1">{{ number_format($gesamtStunden, 2, ',', '.') }} Std.</strong>
        </span>
    </div>

    {{--
        Hinweis zum Bearbeiten: Für ausstehende Aufträge können Von/Bis/Pause direkt
        in der Tabelle angepasst werden, bevor der Auftrag bestätigt wird.
        Der Admin wird über Zeitänderungen informiert.
    --}}
    <div class="alert alert-info border-0 shadow-sm mb-3 small py-2">
        <i class="bi bi-pencil-square me-1"></i>
        Bei <strong>ausstehenden</strong> Aufträgen können Sie die Arbeitszeit direkt in der Tabelle anpassen –
        der Admin wird über Änderungen informiert.
    </div>

    {{--
        Bestätigen-Formulare werden AUSSERHALB der Tabelle platziert (valides HTML5).
        Die Inputs in der Tabelle verweisen per form="form-X" auf das jeweilige Formular.
    --}}
    @foreach($auftraege as $auftrag)
        @if($auftrag->status === 'gesendet')
            <form id="form-{{ $auftrag->id }}"
                  method="POST"
                  action="{{ route('mitarbeiter.auftraege.bestaetigen', $auftrag->id) }}">
                @csrf
                @method('PATCH')
            </form>
        @endif
    @endforeach

    {{-- Aufträge-Tabelle --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Datum</th>
                        <th>Auftraggeber</th>
                        <th>Von</th>
                        <th>Bis</th>
                        <th>Pause</th>
                        <th>Tätigkeit</th>
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

                            @if($auftrag->status === 'gesendet')
                                {{-- Von: editierbares Zeitfeld (gesperrt bis Arbeitsende) --}}
                                <td>
                                    <input type="time"
                                           name="von"
                                           form="form-{{ $auftrag->id }}"
                                           value="{{ $auftrag->vonFormatiert() }}"
                                           class="form-control form-control-sm zeit-von"
                                           data-row="{{ $auftrag->id }}"
                                           style="width:110px"
                                           required
                                           disabled>
                                </td>
                                {{-- Bis: editierbares Zeitfeld (gesperrt bis Arbeitsende) --}}
                                <td>
                                    <input type="time"
                                           name="bis"
                                           form="form-{{ $auftrag->id }}"
                                           value="{{ $auftrag->bisFormatiert() }}"
                                           class="form-control form-control-sm zeit-bis"
                                           data-row="{{ $auftrag->id }}"
                                           style="width:110px"
                                           required
                                           disabled>
                                </td>
                                {{-- Pause: editierbare Checkbox (gesperrt bis Arbeitsende) --}}
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input type="checkbox"
                                               name="pause"
                                               form="form-{{ $auftrag->id }}"
                                               class="form-check-input pause-check"
                                               data-row="{{ $auftrag->id }}"
                                               value="1"
                                               {{ $auftrag->pause ? 'checked' : '' }}
                                               disabled>
                                    </div>
                                </td>
                            @else
                                {{-- Von/Bis/Pause: statische Anzeige --}}
                                <td>{{ $auftrag->vonFormatiert() }}</td>
                                <td>{{ $auftrag->bisFormatiert() }}</td>
                                <td>
                                    @if($auftrag->pause)
                                        <span class="text-muted small">30 Min.</span>
                                    @else
                                        <span class="text-muted small">–</span>
                                    @endif
                                </td>
                            @endif

                            <td>{{ $auftrag->taetigkeit->name }}</td>

                            {{-- Berechnete Stunden – wird per JS live aktualisiert wenn Zeiten geändert werden --}}
                            <td class="stunden-anzeige" data-row="{{ $auftrag->id }}">
                                {{ number_format($auftrag->berechneteStunden(), 2, ',', '.') }} Std.
                            </td>

                            {{-- Statusanzeige (Mitarbeiter-Bezeichnungen) --}}
                            <td>
                                @if($auftrag->status === 'gesendet')
                                    <span class="badge badge-orange badge-status">Ausstehend</span>
                                @elseif($auftrag->status === 'bestaetigt')
                                    <span class="badge bg-secondary badge-status">Bestätigt</span>
                                @elseif($auftrag->status === 'freigegeben')
                                    <span class="badge bg-success badge-status">Freigegeben</span>
                                @elseif($auftrag->status === 'abgelehnt')
                                    <span class="badge bg-danger badge-status">Abgelehnt</span>
                                @endif
                            </td>

                            <td>
                                @if($auftrag->status === 'gesendet')
                                    {{--
                                        Bestätigen-Button: erst nach Arbeitsende aktiv.
                                        data-bis und data-datum werden von JavaScript gelesen.
                                    --}}
                                    <button type="submit"
                                            form="form-{{ $auftrag->id }}"
                                            class="btn btn-sm btn-success btn-bestaetigen"
                                            data-bis="{{ $auftrag->bisFormatiert() }}"
                                            data-datum="{{ $auftrag->datum->format('Y-m-d') }}"
                                            disabled>
                                        Bestätigen
                                    </button>
                                @else
                                    <span class="text-muted small">–</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Keine Aufträge vorhanden.
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
                @if(($statusCounts['gesendet'] ?? 0) > 0)
                    <span class="small"><span class="badge badge-orange me-1">{{ $statusCounts['gesendet'] }}</span>Ausstehend</span>
                @endif
                @if(($statusCounts['bestaetigt'] ?? 0) > 0)
                    <span class="small"><span class="badge bg-secondary me-1">{{ $statusCounts['bestaetigt'] }}</span>Bestätigt</span>
                @endif
                @if(($statusCounts['freigegeben'] ?? 0) > 0)
                    <span class="small"><span class="badge bg-success me-1">{{ $statusCounts['freigegeben'] }}</span>Freigegeben</span>
                @endif
                @if(($statusCounts['abgelehnt'] ?? 0) > 0)
                    <span class="small"><span class="badge bg-danger me-1">{{ $statusCounts['abgelehnt'] }}</span>Abgelehnt</span>
                @endif
                @if($statusCounts->isEmpty())
                    <span class="text-muted small">Keine Einträge</span>
                @endif
            </div>
        </div>

        {{-- Paginierung --}}
        @if($auftraege->hasPages())
            <div class="card-footer bg-white">
                {{ $auftraege->withQueryString()->links() }}
            </div>
        @endif
    </div>

{{-- Live-Berechnung der Stunden wenn Mitarbeitender Von/Bis/Pause ändert --}}
<script>
(function () {
    /**
     * Berechnet die Arbeitsstunden aus Von/Bis-Zeiten.
     * Gibt die Minuten zurück (inkl. optionalem Pausenabzug von 30 Min.).
     */
    function berechneStunden(von, bis, pause) {
        if (!von || !bis) return null;
        var vonMin = zeitZuMinuten(von);
        var bisMin = zeitZuMinuten(bis);
        var diff   = bisMin - vonMin;
        if (diff <= 0) return null;
        if (pause) diff -= 30;
        return Math.max(diff, 0) / 60;
    }

    function zeitZuMinuten(zeit) {
        var teile = zeit.split(':');
        return parseInt(teile[0]) * 60 + parseInt(teile[1]);
    }

    /**
     * Aktualisiert die Stundenanzeige für eine bestimmte Zeile.
     */
    function aktualisiereStunden(rowId) {
        var vonEl    = document.querySelector('.zeit-von[data-row="' + rowId + '"]');
        var bisEl    = document.querySelector('.zeit-bis[data-row="' + rowId + '"]');
        var pauseEl  = document.querySelector('.pause-check[data-row="' + rowId + '"]');
        var anzeige  = document.querySelector('.stunden-anzeige[data-row="' + rowId + '"]');

        if (!vonEl || !bisEl || !anzeige) return;

        var stunden = berechneStunden(vonEl.value, bisEl.value, pauseEl && pauseEl.checked);
        if (stunden !== null) {
            anzeige.textContent = stunden.toFixed(2).replace('.', ',') + ' Std.';
        }
    }

    // Event-Listener auf alle Zeitfelder und Pause-Checkboxen setzen
    document.querySelectorAll('.zeit-von, .zeit-bis').forEach(function (el) {
        el.addEventListener('change', function () {
            aktualisiereStunden(this.dataset.row);
        });
    });

    document.querySelectorAll('.pause-check').forEach(function (el) {
        el.addEventListener('change', function () {
            aktualisiereStunden(this.dataset.row);
        });
    });

    /**
     * Bestätigen-Button und alle zugehörigen Eingabefelder aktivieren/deaktivieren.
     * Alles wird erst nach Arbeitsende (Bis-Zeit) freigeschaltet.
     */
    function pruefeBestaetigenButtons() {
        var jetzt = new Date();
        document.querySelectorAll('.btn-bestaetigen').forEach(function (btn) {
            var bisZeit  = new Date(btn.dataset.datum + 'T' + btn.dataset.bis + ':00');
            var aktiv    = jetzt >= bisZeit;
            var hinweis  = aktiv ? '' : 'Verfügbar ab ' + btn.dataset.bis + ' Uhr';

            // Bestätigen-Button
            btn.disabled = !aktiv;
            btn.title    = hinweis;

            // Alle Eingabefelder die zu demselben Formular gehören (Von, Bis, Pause)
            var formId = btn.getAttribute('form');
            document.querySelectorAll('[form="' + formId + '"]:not([type="hidden"])').forEach(function (feld) {
                feld.disabled = !aktiv;
                feld.title    = hinweis;
            });
        });
    }

    // Sofort beim Laden und danach jede Minute erneut prüfen
    pruefeBestaetigenButtons();
    setInterval(pruefeBestaetigenButtons, 60000);

})();
</script>

</x-app-layout>
