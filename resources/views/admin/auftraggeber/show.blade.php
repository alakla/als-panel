{{-- Detailansicht eines Auftraggebers --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf mit Titel und Bearbeiten-Button --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">{{ $auftraggeber->firmenname }}</h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('admin.auftraggeber.index') }}" class="text-decoration-none">Auftraggeberliste</a>
                &rsaquo; Details
            </p>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('admin.auftraggeber.edit', $auftraggeber) }}" class="btn btn-outline-primary btn-sm">
                Bearbeiten
            </a>
            {{-- Toggle-Formular: Auftraggeber aktivieren oder deaktivieren --}}
            <form method="POST" action="{{ route('admin.auftraggeber.toggle', $auftraggeber) }}">
                @csrf
                @method('PATCH')
                @if($auftraggeber->is_active)
                    <button type="submit" class="btn btn-outline-danger btn-sm"
                        data-confirm="Auftraggeber wirklich deaktivieren?" data-confirm-btn="danger">
                        Deaktivieren
                    </button>
                @else
                    <button type="submit" class="btn btn-outline-success btn-sm">
                        Reaktivieren
                    </button>
                @endif
            </form>
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
                            <td class="text-muted" style="width:45%">Firmenname</td>
                            <td class="fw-semibold">{{ $auftraggeber->firmenname }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ansprechpartner</td>
                            <td>{{ $auftraggeber->ansprechpartner }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">E-Mail</td>
                            <td>
                                <a href="mailto:{{ $auftraggeber->email }}" class="text-decoration-none">
                                    {{ $auftraggeber->email }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Telefon</td>
                            <td>{{ $auftraggeber->telefon ?? '–' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Adresse</td>
                            <td>{{ $auftraggeber->adresse }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($auftraggeber->is_active)
                                    <span class="badge badge-status bg-success">Aktiv</span>
                                @else
                                    <span class="badge badge-status bg-secondary">Inaktiv</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Aufträge für diesen Auftraggeber --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Aufträge</div>

                {{-- Filterleiste: Status, Monat und Jahr --}}
                <div class="card-body border-bottom py-3">
                    <form method="GET" action="{{ route('admin.auftraggeber.show', $auftraggeber) }}" id="filterForm">
                        <div class="d-flex gap-2 align-items-center flex-wrap">

                            {{-- Filter: Status --}}
                            <select name="status" class="form-select form-select-sm" style="width:160px"
                                    onchange="document.getElementById('filterForm').submit()">
                                <option value="alle"        {{ $filterStatus === 'alle'        ? 'selected' : '' }}>Alle</option>
                                <option value="gesendet"    {{ $filterStatus === 'gesendet'    ? 'selected' : '' }}>Gesendet</option>
                                <option value="bestaetigt"  {{ $filterStatus === 'bestaetigt'  ? 'selected' : '' }}>Offen</option>
                                <option value="freigegeben" {{ $filterStatus === 'freigegeben' ? 'selected' : '' }}>Freigegeben</option>
                                <option value="abgelehnt"   {{ $filterStatus === 'abgelehnt'   ? 'selected' : '' }}>Abgelehnt</option>
                            </select>

                            {{-- Monatsfilter: Monat- und Jahr-Auswahl --}}
                            @php
                                [$filterJahrVal, $filterMonatVal] = explode('-', $monat);
                                $monate = [1=>'Januar',2=>'Februar',3=>'März',4=>'April',5=>'Mai',6=>'Juni',
                                           7=>'Juli',8=>'August',9=>'September',10=>'Oktober',11=>'November',12=>'Dezember'];
                            @endphp
                            <select name="monat_nr" class="form-select form-select-sm" style="width:130px"
                                    onchange="document.getElementById('filterForm').submit()">
                                @foreach($monate as $nr => $name)
                                    <option value="{{ $nr }}" {{ (int)$filterMonatVal === $nr ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <select name="jahr" class="form-select form-select-sm" style="width:90px"
                                    onchange="document.getElementById('filterForm').submit()">
                                @foreach($jahre as $j)
                                    <option value="{{ $j }}" {{ (int)$filterJahrVal === $j ? 'selected' : '' }}>{{ $j }}</option>
                                @endforeach
                            </select>

                            {{-- Zurücksetzen --}}
                            @if($filterStatus !== 'alle' || $monat !== now()->format('Y-m'))
                                <a href="{{ route('admin.auftraggeber.show', $auftraggeber) }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    Zurücksetzen
                                </a>
                            @endif

                        </div>
                    </form>
                </div>

                {{-- Aufträge-Tabelle --}}
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Datum</th>
                                <th>Mitarbeiter</th>
                                <th>Arbeitszeit</th>
                                <th>Stunden</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auftraege as $auftrag)
                                <tr>
                                    <td>{{ $auftrag->datum->format('d.m.Y') }}</td>
                                    <td>{{ $auftrag->mitarbeiter->user->name }}</td>
                                    <td class="text-nowrap">{{ $auftrag->vonFormatiert() }} – {{ $auftrag->bisFormatiert() }}</td>
                                    <td>{{ number_format($auftrag->berechneteStunden(), 2, ',', '.') }} Std.</td>
                                    <td>
                                        @if($auftrag->status === 'gesendet')
                                            <span class="badge badge-status bg-primary">Gesendet</span>
                                        @elseif($auftrag->status === 'bestaetigt')
                                            <span class="badge badge-status badge-orange">Offen</span>
                                        @elseif($auftrag->status === 'freigegeben')
                                            <span class="badge badge-status bg-success">Freigegeben</span>
                                        @elseif($auftrag->status === 'abgelehnt')
                                            <span class="badge badge-status bg-danger">Abgelehnt</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Keine Aufträge für den gewählten Filter gefunden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Statusübersicht: Anzahl pro Zustand --}}
                <div class="card-footer bg-white border-top py-2">
                    <div class="d-flex gap-3 flex-wrap align-items-center">
                        <span class="text-muted small">Gesamt:</span>
                        @if(($statusCounts['gesendet'] ?? 0) > 0)
                            <span class="small"><span class="badge bg-primary me-1">{{ $statusCounts['gesendet'] }}</span>Gesendet</span>
                        @endif
                        @if(($statusCounts['bestaetigt'] ?? 0) > 0)
                            <span class="small"><span class="badge badge-orange me-1">{{ $statusCounts['bestaetigt'] }}</span>Offen</span>
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

            </div>

            {{-- Rechnungsübersicht für diesen Auftraggeber --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white fw-semibold">Rechnungen</div>

                {{-- Filterleiste für Rechnungen (eigene Parameter mit Prefix r_) --}}
                <div class="card-body border-bottom py-3">
                    <form method="GET" action="{{ route('admin.auftraggeber.show', $auftraggeber) }}" id="rFilterForm">
                        {{-- Auftragsfilter-Parameter beibehalten --}}
                        @if(request('status'))     <input type="hidden" name="status"     value="{{ request('status') }}"> @endif
                        @if(request('monat_nr'))   <input type="hidden" name="monat_nr"   value="{{ request('monat_nr') }}"> @endif
                        @if(request('jahr'))       <input type="hidden" name="jahr"       value="{{ request('jahr') }}"> @endif
                        <div class="d-flex gap-2 align-items-center flex-wrap">

                            {{-- Filter: Status --}}
                            <select name="r_status" class="form-select form-select-sm" style="width:140px"
                                    onchange="document.getElementById('rFilterForm').submit()">
                                <option value="alle"      {{ $rFilterStatus === 'alle'      ? 'selected' : '' }}>Alle Status</option>
                                <option value="offen"     {{ $rFilterStatus === 'offen'     ? 'selected' : '' }}>Offen</option>
                                <option value="bezahlt"   {{ $rFilterStatus === 'bezahlt'   ? 'selected' : '' }}>Bezahlt</option>
                                <option value="storniert" {{ $rFilterStatus === 'storniert' ? 'selected' : '' }}>Storniert</option>
                            </select>

                            {{-- Monatsfilter für Rechnungen --}}
                            @php
                                [$rJahrVal, $rMonatVal] = explode('-', $rMonat);
                                $monate = [1=>'Januar',2=>'Februar',3=>'März',4=>'April',5=>'Mai',6=>'Juni',
                                           7=>'Juli',8=>'August',9=>'September',10=>'Oktober',11=>'November',12=>'Dezember'];
                            @endphp
                            <select name="r_monat_nr" class="form-select form-select-sm" style="width:130px"
                                    onchange="document.getElementById('rFilterForm').submit()">
                                @foreach($monate as $nr => $name)
                                    <option value="{{ $nr }}" {{ (int)$rMonatVal === $nr ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <select name="r_jahr" class="form-select form-select-sm" style="width:90px"
                                    onchange="document.getElementById('rFilterForm').submit()">
                                @foreach($rJahre as $j)
                                    <option value="{{ $j }}" {{ (int)$rJahrVal === $j ? 'selected' : '' }}>{{ $j }}</option>
                                @endforeach
                            </select>

                            {{-- Zurücksetzen --}}
                            @if($rFilterStatus !== 'alle' || $rMonat !== now()->format('Y-m'))
                                <a href="{{ route('admin.auftraggeber.show', $auftraggeber) }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    Zurücksetzen
                                </a>
                            @endif

                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rechnungsnummer</th>
                                <th>Datum</th>
                                <th>Betrag</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rechnungen as $rechnung)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.rechnungen.show', $rechnung) }}"
                                           class="text-decoration-none fw-semibold">
                                            {{ $rechnung->rechnungsnummer }}
                                        </a>
                                    </td>
                                    <td>{{ $rechnung->rechnungsdatum?->format('d.m.Y') ?? '–' }}</td>
                                    <td>{{ number_format($rechnung->gesamtbetrag, 2, ',', '.') }} €</td>
                                    <td>
                                        @if($rechnung->status === 'bezahlt')
                                            <span class="badge badge-status bg-success">Bezahlt</span>
                                        @elseif($rechnung->status === 'storniert')
                                            <span class="badge badge-status bg-danger">Storniert</span>
                                        @else
                                            <span class="badge badge-status badge-orange">Offen</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        Keine Rechnungen für den gewählten Filter gefunden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Statusübersicht Rechnungen --}}
                <div class="card-footer bg-white border-top py-2">
                    <div class="d-flex gap-3 flex-wrap align-items-center">
                        <span class="text-muted small">Gesamt:</span>
                        @if(($rStatusCounts['offen'] ?? 0) > 0)
                            <span class="small"><span class="badge badge-orange me-1">{{ $rStatusCounts['offen'] }}</span>Offen</span>
                        @endif
                        @if(($rStatusCounts['bezahlt'] ?? 0) > 0)
                            <span class="small"><span class="badge bg-success me-1">{{ $rStatusCounts['bezahlt'] }}</span>Bezahlt</span>
                        @endif
                        @if(($rStatusCounts['storniert'] ?? 0) > 0)
                            <span class="small"><span class="badge bg-danger me-1">{{ $rStatusCounts['storniert'] }}</span>Storniert</span>
                        @endif
                        @if($rStatusCounts->isEmpty())
                            <span class="text-muted small">Keine Einträge</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>

</x-app-layout>
