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
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('admin.mitarbeiter.edit', $mitarbeiter) }}" class="btn btn-outline-primary btn-sm">
                Bearbeiten
            </a>
            {{-- Toggle-Formular: Mitarbeiter aktivieren oder deaktivieren --}}
            <form method="POST" action="{{ route('admin.mitarbeiter.toggle', $mitarbeiter) }}">
                @csrf
                @method('PATCH')
                @if($mitarbeiter->status === 'aktiv')
                    <button type="submit" class="btn btn-outline-danger btn-sm"
                        data-confirm="Mitarbeiter wirklich deaktivieren?" data-confirm-btn="danger">
                        Deaktivieren
                    </button>
                @else
                    <button type="submit" class="btn btn-outline-success btn-sm"
                        data-confirm="Mitarbeiter wirklich reaktivieren?" data-confirm-btn="success">
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
                            <td class="text-muted">Telefon</td>
                            <td>{{ $mitarbeiter->telefon ?? '—' }}</td>
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

        {{-- Zeiterfassungen mit Filter und Gehaltsberechnung --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Zeiterfassungen</div>

                {{-- Filterleiste: Status, Monat und Jahr --}}
                <div class="card-body border-bottom py-3">
                    <form method="GET" action="{{ route('admin.mitarbeiter.show', $mitarbeiter) }}" id="filterForm">
                        <div class="d-flex gap-2 align-items-center flex-wrap">

                            {{-- Filter: Status (identisch mit Aufträge-Seite) --}}
                            <select name="status" class="form-select form-select-sm" style="width:160px"
                                    onchange="document.getElementById('filterForm').submit()">
                                <option value="alle"        {{ $filterStatus === 'alle'        ? 'selected' : '' }}>Alle</option>
                                <option value="gesendet"    {{ $filterStatus === 'gesendet'    ? 'selected' : '' }}>Gesendet</option>
                                <option value="bestaetigt"  {{ $filterStatus === 'bestaetigt'  ? 'selected' : '' }}>Offen</option>
                                <option value="freigegeben" {{ $filterStatus === 'freigegeben' ? 'selected' : '' }}>Freigegeben</option>
                                <option value="abgelehnt"   {{ $filterStatus === 'abgelehnt'   ? 'selected' : '' }}>Abgelehnt</option>
                            </select>

                            {{-- Monatsfilter: Monat- und Jahr-Auswahl (wie Aufträge-Seite) --}}
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

                            {{-- Zurücksetzen (nur wenn Filter vom Standard abweicht) --}}
                            @if($filterStatus !== 'alle' || $monat !== now()->format('Y-m'))
                                <a href="{{ route('admin.mitarbeiter.show', $mitarbeiter) }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    Zurücksetzen
                                </a>
                            @endif

                        </div>
                    </form>
                </div>

                {{-- Gehaltsberechnung und Zahlungsstatus --}}
                <div class="card-body border-bottom py-2 bg-light">
                    <div class="row align-items-center g-2">

                        {{-- Gehaltsinfo links --}}
                        <div class="col text-muted small">
                            Monatliches Gehalt ({{ $filterMonatLabel }})
                            <span class="ms-1 text-secondary">— nur freigegebene Stunden</span>
                        </div>

                        {{-- Betrag mittig/rechts --}}
                        <div class="col-auto fw-bold">
                            {{ number_format($freigegebeneStunden, 2, ',', '.') }} Std.
                            &times;
                            {{ number_format($mitarbeiter->stundenlohn, 2, ',', '.') }} €
                            =
                            <span class="text-success">{{ number_format($monatsgehalt, 2, ',', '.') }} €</span>
                        </div>

                        {{-- Zahlungsbutton oder Bezahlt-Badge --}}
                        <div class="col-auto">
                            @if($lohnabrechnung)
                                {{-- Bereits bezahlt: grünes Badge mit Datum --}}
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Bezahlt am {{ $lohnabrechnung->bezahlt_am->format('d.m.Y') }}
                                </span>
                            @elseif($monatsgehalt > 0)
                                {{-- Noch nicht bezahlt: Button anzeigen --}}
                                <form method="POST"
                                      action="{{ route('admin.mitarbeiter.bezahlen', $mitarbeiter) }}"
                                      class="d-inline">
                                    @csrf
                                    <input type="hidden" name="monat" value="{{ $monat }}">
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-success"
                                            data-confirm="Gehalt von {{ number_format($monatsgehalt, 2, ',', '.') }} € für {{ $filterMonatLabel }} als bezahlt markieren?"
                                            data-confirm-btn="success">
                                        <i class="bi bi-cash-coin me-1"></i>Als bezahlt markieren
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- Aufträge-Tabelle (identische Struktur wie Aufträge-Seite) --}}
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Datum</th>
                                <th>Auftraggeber</th>
                                <th>Arbeitszeit</th>
                                <th>Stunden</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auftraege as $auftrag)
                                <tr>
                                    <td>{{ $auftrag->datum->format('d.m.Y') }}</td>
                                    <td>{{ $auftrag->auftraggeber->firmenname }}</td>
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

                {{-- Statusübersicht: Anzahl pro Zustand für den gewählten Monat --}}
                @php
                    $statusCounts = $auftraege->countBy('status');
                @endphp
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
                        @if($auftraege->isEmpty())
                            <span class="text-muted small">Keine Einträge</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>

</x-app-layout>
