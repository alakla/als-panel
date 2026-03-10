{{-- Admin: Auftraege-Uebersicht --}}
{{-- Zeigt alle Arbeitsauftraege; bestaetgte koennen freigegeben oder abgelehnt werden --}}
<x-app-layout>

    {{-- Seitenkopf mit Titel und "Neuer Auftrag"-Button --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Arbeitsauftraege</h4>
            <p class="text-muted small mb-0">Auftraege zuweisen, pruefen und freigeben</p>
        </div>
        <div class="col-auto d-flex align-items-center gap-2">
            <span class="text-muted small">
                Aktualisierung in <span id="refreshCountdown" class="fw-semibold">60</span>s
                <a href="{{ request()->fullUrl() }}" class="ms-1 text-decoration-none">&#8635;</a>
            </span>
            <a href="{{ route('admin.auftraege.create') }}" class="btn btn-primary">
                + Neuer Auftrag
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

    {{-- Filterformular --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.auftraege.index') }}" class="row g-2 align-items-end">

                {{-- Statusfilter --}}
                <div class="col-md-2">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="alle"        {{ $status === 'alle'        ? 'selected' : '' }}>Alle</option>
                        <option value="gesendet"    {{ $status === 'gesendet'    ? 'selected' : '' }}>Gesendet</option>
                        <option value="bestaetigt"  {{ $status === 'bestaetigt'  ? 'selected' : '' }}>Offen</option>
                        <option value="freigegeben" {{ $status === 'freigegeben' ? 'selected' : '' }}>Freigegeben</option>
                        <option value="abgelehnt"   {{ $status === 'abgelehnt'   ? 'selected' : '' }}>Abgelehnt</option>
                    </select>
                </div>

                {{-- Mitarbeiterfilter --}}
                <div class="col-md-2">
                    <label class="form-label small text-muted">Mitarbeiter</label>
                    <select name="mitarbeiter_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Alle Mitarbeiter</option>
                        @foreach($mitarbeiter as $ma)
                            <option value="{{ $ma->id }}" {{ $mitarbeiterId == $ma->id ? 'selected' : '' }}>
                                {{ $ma->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Auftraggeberfilter --}}
                <div class="col-md-2">
                    <label class="form-label small text-muted">Auftraggeber</label>
                    <select name="auftraggeber_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Alle Auftraggeber</option>
                        @foreach($auftraggeber as $ag)
                            <option value="{{ $ag->id }}" {{ $auftraggeberId == $ag->id ? 'selected' : '' }}>
                                {{ $ag->firmenname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Monatsfilter: Monat- und Jahr-Auswahl (getrennte Dropdowns) --}}
                @php
                    [$filterJahr, $filterMonat] = explode('-', $monat);
                    $monate = [1=>'Januar',2=>'Februar',3=>'März',4=>'April',5=>'Mai',6=>'Juni',
                               7=>'Juli',8=>'August',9=>'September',10=>'Oktober',11=>'November',12=>'Dezember'];
                @endphp
                <div class="col-md-2">
                    <label class="form-label small text-muted">Monat</label>
                    <select name="monat_nr" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($monate as $nr => $name)
                            <option value="{{ $nr }}" {{ (int)$filterMonat === $nr ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small text-muted">Jahr</label>
                    <select name="jahr" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($jahre as $j)
                            <option value="{{ $j }}" {{ (int)$filterJahr === $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter zuruecksetzen --}}
                <div class="col-auto">
                    <label class="form-label small text-muted d-block">&nbsp;</label>
                    <a href="{{ route('admin.auftraege.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{--
        Massenfreigabe-Formular: steht AUSSERHALB der Tabelle.
        Checkboxen in der Tabelle verweisen per form="massenfreigabeForm" auf dieses Formular.
    --}}
    <form method="POST"
          action="{{ route('admin.auftraege.massenfreigabe') }}"
          id="massenfreigabeForm">
        @csrf
    </form>

    {{-- Auftraege-Tabelle --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">
                Auftraege
                @if($status === 'bestaetigt' || $status === 'alle')
                    @php $offeneAnzahl = $auftraege->total(); @endphp
                    @if($status === 'bestaetigt')
                        <span class="badge bg-warning text-dark ms-1">{{ $offeneAnzahl }} offen</span>
                    @endif
                @endif
            </span>
            {{-- Massenfreigabe-Button: nur anzeigen wenn bestaetgte Auftraege sichtbar sind --}}
            @if(($status === 'bestaetigt' || $status === 'alle') && $auftraege->count() > 0)
                <button type="button" class="btn btn-success btn-sm" onclick="massenfreigabeAbsenden()">
                    Auswahl freigeben
                </button>
            @endif
        </div>

        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        {{-- Checkbox-Spalte nur bei relevanten Statusfiltern --}}
                        @if($status === 'bestaetigt' || $status === 'alle')
                            <th style="width:40px">
                                <input type="checkbox" class="form-check-input" id="alleAuswaehlen">
                            </th>
                        @endif
                        <th>Datum</th>
                        <th>Mitarbeiter</th>
                        <th>Auftraggeber</th>
                        <th>Arbeitszeit</th>
                        <th>Pause</th>
                        <th>Taetigkeit</th>
                        <th>Stunden</th>
                        <th style="width:120px">Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auftraege as $auftrag)
                        <tr>
                            {{-- Checkbox: nur fuer bestaetgte Eintraege aktiv --}}
                            @if($status === 'bestaetigt' || $status === 'alle')
                                <td>
                                    @if($auftrag->status === 'bestaetigt')
                                        <input type="checkbox"
                                               class="form-check-input eintrag-checkbox"
                                               name="eintraege[]"
                                               value="{{ $auftrag->id }}"
                                               form="massenfreigabeForm">
                                    @endif
                                </td>
                            @endif
                            <td>{{ $auftrag->datum->format('d.m.Y') }}</td>
                            <td>{{ $auftrag->mitarbeiter->user->name }}</td>
                            <td>{{ $auftrag->auftraggeber->firmenname }}</td>
                            {{-- Von-Bis Zeitbereich --}}
                            <td>{{ $auftrag->vonFormatiert() }} – {{ $auftrag->bisFormatiert() }}</td>
                            {{-- Pause: Ja / Nein --}}
                            <td>
                                @if($auftrag->pause)
                                    <span class="text-muted small">30 Min.</span>
                                @else
                                    <span class="text-muted small">–</span>
                                @endif
                            </td>
                            <td>{{ $auftrag->taetigkeit->name }}</td>
                            {{-- Berechnete Arbeitsstunden (inkl. Pausenabzug) --}}
                            <td>{{ number_format($auftrag->berechneteStunden(), 2, ',', '.') }} Std.</td>
                            {{-- Statusanzeige (Admin-Bezeichnungen) --}}
                            <td>
                                @if($auftrag->status === 'gesendet')
                                    <span class="badge bg-primary badge-status">Gesendet</span>
                                @elseif($auftrag->status === 'bestaetigt')
                                    <span class="badge bg-warning text-dark badge-status">Offen</span>
                                @elseif($auftrag->status === 'freigegeben')
                                    <span class="badge bg-success badge-status">Freigegeben</span>
                                @elseif($auftrag->status === 'abgelehnt')
                                    <span class="badge bg-danger badge-status">Abgelehnt</span>
                                @endif
                            </td>
                            <td>
                                @if($auftrag->status === 'gesendet')
                                    {{-- Stornieren: nur fuer noch nicht bestaetgte Auftraege --}}
                                    <form method="POST"
                                          action="{{ route('admin.auftraege.destroy', $auftrag) }}"
                                          class="d-inline"
                                          data-confirm="Diesen Auftrag wirklich stornieren?"
                                          data-confirm-btn="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Stornieren</button>
                                    </form>
                                @elseif($auftrag->status === 'bestaetigt')
                                    {{-- Freigeben-Button --}}
                                    <form method="POST"
                                          action="{{ route('admin.auftraege.freigeben', $auftrag) }}"
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Freigeben</button>
                                    </form>
                                    {{-- Ablehnen-Button --}}
                                    <form method="POST"
                                          action="{{ route('admin.auftraege.ablehnen', $auftrag) }}"
                                          class="d-inline ablehnen-form">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="ablehnenBestaetigen(this.closest('form'))">
                                            Ablehnen
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">–</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($status === 'bestaetigt' || $status === 'alle') ? 11 : 10 }}"
                                class="text-center text-muted py-4">
                                Keine Auftraege fuer den ausgewaehlten Zeitraum gefunden.
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

    {{-- Bestaetigungs-Modal fuer Massenfreigabe --}}
    <div class="modal fade" id="massenfreigabeModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Auftraege freigeben</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-3">
                    <div class="fs-1 text-success mb-2">&#10003;</div>
                    <p class="mb-0" id="massenfreigabeText"></p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-success btn-sm" id="massenfreigabeBestaetigen">Ja, freigeben</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bestaetigungs-Modal fuer Ablehnen (Einzeleintrag) --}}
    <div class="modal fade" id="ablehnenModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Auftrag ablehnen</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-3">
                    <div class="fs-1 text-danger mb-2">&#10007;</div>
                    <p class="mb-0">Diesen Auftrag wirklich ablehnen?</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-danger btn-sm" id="ablehnenBestaetigen">Ja, ablehnen</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Alle-auswaehlen Checkbox steuert alle Einzelcheckboxen
        const alleCheckbox = document.getElementById('alleAuswaehlen');
        if (alleCheckbox) {
            alleCheckbox.addEventListener('change', function () {
                document.querySelectorAll('.eintrag-checkbox').forEach(cb => {
                    cb.checked = alleCheckbox.checked;
                });
            });
        }

        // Massenfreigabe: prueft ob mindestens eine Checkbox ausgewaehlt ist, dann Modal zeigen
        function massenfreigabeAbsenden() {
            const ausgewaehlt = document.querySelectorAll('.eintrag-checkbox:checked');
            if (ausgewaehlt.length === 0) {
                document.getElementById('massenfreigabeText').textContent =
                    'Bitte mindestens einen Eintrag auswaehlen.';
                document.getElementById('massenfreigabeBestaetigen').style.display = 'none';
            } else {
                document.getElementById('massenfreigabeText').textContent =
                    ausgewaehlt.length + ' Auftrag/Auftraege freigeben?';
                document.getElementById('massenfreigabeBestaetigen').style.display = '';
            }
            new bootstrap.Modal(document.getElementById('massenfreigabeModal')).show();
        }

        // Massenfreigabe-Formular abschicken nach Bestaetigung
        document.getElementById('massenfreigabeBestaetigen').addEventListener('click', function () {
            document.getElementById('massenfreigabeForm').submit();
        });

        // Ablehnen-Modal: speichert das zugehoerige Formular und reicht es ein
        let ablehnenForm = null;
        function ablehnenBestaetigen(formEl) {
            ablehnenForm = formEl;
            new bootstrap.Modal(document.getElementById('ablehnenModal')).show();
        }
        document.getElementById('ablehnenBestaetigen').addEventListener('click', function () {
            if (ablehnenForm) ablehnenForm.submit();
        });
    </script>

</x-app-layout>
