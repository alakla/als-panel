{{-- Admin: Neuen Arbeitsauftrag erstellen --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Neuer Arbeitsauftrag</h4>
            <p class="text-muted small mb-0">Mitarbeitenden einen Einsatz zuweisen</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.auftraege.index') }}" class="btn btn-outline-secondary btn-sm">
                &larr; Zurück
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    {{-- Validierungsfehler anzeigen --}}
                    @if($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Datum wählen (GET-Formular, lädt Seite neu -> aktualisiert Mitarbeiterliste) --}}
                    {{-- Prefill-Parameter werden als Hidden-Felder mitgesendet, damit sie beim Datumswechsel erhalten bleiben --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Einsatzdatum <span class="text-danger">*</span></label>
                        <form method="GET" action="{{ route('admin.auftraege.create') }}" class="d-flex gap-2">
                            <input type="date"
                                   name="datum"
                                   value="{{ $datum }}"
                                   min="{{ $today }}"
                                   class="form-control"
                                   onchange="this.form.submit()">
                            {{-- Vorausgefüllte Werte beibehalten --}}
                            @if($prefill['mitarbeiter_id'])  <input type="hidden" name="mitarbeiter_id"  value="{{ $prefill['mitarbeiter_id'] }}">  @endif
                            @if($prefill['auftraggeber_id']) <input type="hidden" name="auftraggeber_id" value="{{ $prefill['auftraggeber_id'] }}"> @endif
                            <input type="hidden" name="von_h"         value="{{ $prefill['von_h'] }}">
                            <input type="hidden" name="von_m"         value="{{ $prefill['von_m'] }}">
                            <input type="hidden" name="bis_h"         value="{{ $prefill['bis_h'] }}">
                            <input type="hidden" name="bis_m"         value="{{ $prefill['bis_m'] }}">
                            <input type="hidden" name="pause"         value="{{ $prefill['pause'] }}">
                            @if($prefill['taetigkeit_id'])  <input type="hidden" name="taetigkeit_id"   value="{{ $prefill['taetigkeit_id'] }}">  @endif
                        </form>
                    </div>

                    {{-- POST-Formular für alle weiteren Felder --}}
                    <form method="POST" action="{{ route('admin.auftraege.store') }}">
                        @csrf
                        <input type="hidden" name="datum" value="{{ $datum }}">

                        {{-- Mitarbeitender: Nur verfügbare werden angezeigt --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mitarbeitender <span class="text-danger">*</span></label>
                            @if($mitarbeiter->isEmpty())
                                <div class="alert alert-warning mb-0">
                                    Alle aktiven Mitarbeitenden haben an diesem Tag bereits einen Auftrag.
                                    Bitte wählen Sie ein anderes Datum.
                                </div>
                            @else
                                <select name="mitarbeiter_id" class="form-select" required>
                                    <option value="">-- Mitarbeitenden wählen --</option>
                                    @foreach($mitarbeiter as $ma)
                                        <option value="{{ $ma->id }}"
                                            {{ old('mitarbeiter_id', $prefill['mitarbeiter_id']) == $ma->id ? 'selected' : '' }}>
                                            {{ $ma->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">
                                    Nur verfügbare Mitarbeitende werden angezeigt (ohne Auftrag an diesem Tag).
                                </div>
                            @endif
                        </div>

                        {{-- Auftraggeber (Einsatzfirma) --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Auftraggeber <span class="text-danger">*</span></label>
                            <select name="auftraggeber_id" class="form-select" required>
                                <option value="">-- Auftraggeber wählen --</option>
                                @foreach($auftraggeber as $ag)
                                    <option value="{{ $ag->id }}"
                                        {{ old('auftraggeber_id', $prefill['auftraggeber_id']) == $ag->id ? 'selected' : '' }}>
                                        {{ $ag->firmenname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Arbeitszeit: Von / Bis (Stunde + Minute getrennt) --}}
                        @php
                            [$vonH, $vonM] = explode(':', old('von', $prefill['von_h'].':'.$prefill['von_m']));
                            [$bisH, $bisM] = explode(':', old('bis', $prefill['bis_h'].':'.$prefill['bis_m']));
                        @endphp
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Arbeitszeit <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center gap-2">
                                {{-- Von --}}
                                <span class="text-muted small">Von</span>
                                <div class="input-group" style="width:130px">
                                    <select name="von_h" class="form-select form-select-sm px-1">
                                        @for($h = 0; $h < 24; $h++)
                                            <option value="{{ sprintf('%02d', $h) }}"
                                                {{ $vonH == sprintf('%02d', $h) ? 'selected' : '' }}>
                                                {{ sprintf('%02d', $h) }}
                                            </option>
                                        @endfor
                                    </select>
                                    <span class="input-group-text px-1">:</span>
                                    <select name="von_m" class="form-select form-select-sm px-1">
                                        @foreach(['00','15','30','45'] as $m)
                                            <option value="{{ $m }}" {{ $vonM == $m ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Bis --}}
                                <span class="text-muted small ms-3">Bis</span>
                                <div class="input-group" style="width:130px">
                                    <select name="bis_h" class="form-select form-select-sm px-1">
                                        @for($h = 0; $h < 24; $h++)
                                            <option value="{{ sprintf('%02d', $h) }}"
                                                {{ $bisH == sprintf('%02d', $h) ? 'selected' : '' }}>
                                                {{ sprintf('%02d', $h) }}
                                            </option>
                                        @endfor
                                    </select>
                                    <span class="input-group-text px-1">:</span>
                                    <select name="bis_m" class="form-select form-select-sm px-1">
                                        @foreach(['00','15','30','45'] as $m)
                                            <option value="{{ $m }}" {{ $bisM == $m ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Pause (30 Minuten) --}}
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       id="pause"
                                       name="pause"
                                       value="1"
                                       {{ old('pause', $prefill['pause']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="pause">
                                    Pause (30 Minuten werden von der Arbeitszeit abgezogen)
                                </label>
                            </div>
                        </div>

                        {{-- Tätigkeit (Art der Arbeit) --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Tätigkeit <span class="text-danger">*</span></label>
                            <select name="taetigkeit_id" class="form-select" required>
                                <option value="">-- Tätigkeit wählen --</option>
                                @foreach($taetigkeiten as $t)
                                    <option value="{{ $t->id }}"
                                        {{ old('taetigkeit_id', $prefill['taetigkeit_id']) == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Absenden --}}
                        <div class="d-flex gap-2">
                            @if($mitarbeiter->isNotEmpty())
                                <button type="submit" class="btn btn-primary">
                                    Auftrag senden
                                </button>
                            @endif
                            <a href="{{ route('admin.auftraege.index') }}" class="btn btn-outline-secondary">
                                Abbrechen
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Info-Karte: Erklärung des Ablaufs --}}
        <div class="col-md-4 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Ablauf</div>
                <div class="card-body">
                    <ol class="ps-3 small text-muted">
                        <li class="mb-2">Admin wählt Datum und erstellt Auftrag</li>
                        <li class="mb-2">Mitarbeitender sieht den Auftrag mit Status <strong>Gesendet</strong></li>
                        <li class="mb-2">Nach Ausführung bestätigt Mitarbeitender den Auftrag</li>
                        <li class="mb-2">Zeiteintrag wird automatisch erstellt (Status: <strong>Offen</strong>)</li>
                        <li>Admin genehmigt in der Aufträge</li>
                    </ol>
                    <hr>
                    <p class="small text-muted mb-0"><strong>Pausenabzug:</strong> Bei aktivierter Pause werden 30 Minuten abgezogen.</p>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
