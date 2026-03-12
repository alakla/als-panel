{{-- Formular zum Bearbeiten eines vorhandenen Zeiteintrags --}}
{{-- Zugriff: Nur Mitarbeitende (nur eigene, offene Einträge) --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold mb-0">Zeiteintrag bearbeiten</h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('mitarbeiter.zeiterfassung.index') }}" class="text-decoration-none">Zeiterfassung</a>
                &rsaquo; {{ $zeiterfassung->datum->format('d.m.Y') }}
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Eintrag bearbeiten</div>
                <div class="card-body">

                    {{-- Formular: PUT an ZeiterfassungController@update --}}
                    <form method="POST" action="{{ route('mitarbeiter.zeiterfassung.update', $zeiterfassung) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">

                            {{-- Auftraggeber-Auswahl --}}
                            <div class="col-12">
                                <label for="auftraggeber_id" class="form-label">
                                    Auftraggeber <span class="text-danger">*</span>
                                </label>
                                <select id="auftraggeber_id" name="auftraggeber_id"
                                    class="form-select @error('auftraggeber_id') is-invalid @enderror" required>
                                    <option value="">— Auftraggeber auswählen —</option>
                                    @foreach($auftraggeber as $ag)
                                        <option value="{{ $ag->id }}"
                                            {{ old('auftraggeber_id', $zeiterfassung->auftraggeber_id) == $ag->id ? 'selected' : '' }}>
                                            {{ $ag->firmenname }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('auftraggeber_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Datum --}}
                            <div class="col-md-6">
                                <label for="datum" class="form-label">
                                    Datum <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="datum" name="datum"
                                    value="{{ old('datum', $zeiterfassung->datum->format('Y-m-d')) }}"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="form-control @error('datum') is-invalid @enderror" required>
                                @error('datum')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Anzahl der Stunden --}}
                            <div class="col-md-6">
                                <label for="stunden" class="form-label">
                                    Stunden <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" id="stunden" name="stunden"
                                        value="{{ old('stunden', $zeiterfassung->stunden) }}"
                                        step="0.5" min="0.5" max="12"
                                        class="form-control @error('stunden') is-invalid @enderror" required>
                                    <span class="input-group-text">Std.</span>
                                    @error('stunden')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Tätigkeitsbeschreibung: Auswahl aus DB-Vorgaben (vom Admin verwaltbar) oder eigene Eingabe --}}
                            <div class="col-12">
                                @php
                                    // Aktuellen Wert ermitteln (nach Validierungsfehler: old(), sonst DB-Wert)
                                    $aktuellerWert = old('beschreibung', $zeiterfassung->beschreibung);

                                    // Namen der Vorgaben aus der DB-Collection extrahieren
                                    $vorgabenNamen = $taetigkeiten->pluck('name')->toArray();

                                    // Prüfen ob der gespeicherte Wert einer Vorgabe entspricht
                                    $istVorgabe = in_array($aktuellerWert, $vorgabenNamen);
                                @endphp

                                <label class="form-label">Tätigkeitsbeschreibung <span class="text-muted fw-normal small">(optional)</span></label>

                                {{-- Dropdown: vorhandenen Wert vorauswählen falls möglich --}}
                                <select id="beschreibung_auswahl" class="form-select mb-2"
                                        onchange="handleBeschreibungAuswahl(this)">
                                    <option value="" {{ !$aktuellerWert ? 'selected' : '' }}>— Keine Angabe —</option>
                                    @foreach($taetigkeiten as $t)
                                        <option value="{{ $t->name }}" {{ $aktuellerWert === $t->name ? 'selected' : '' }}>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                    <option value="sonstiges" {{ ($aktuellerWert && !$istVorgabe) ? 'selected' : '' }}>
                                        Sonstiges (eigene Eingabe)...
                                    </option>
                                </select>

                                {{-- Freitextfeld: nur sichtbar wenn "Sonstiges" ausgewählt --}}
                                <textarea id="beschreibung" name="beschreibung" rows="2"
                                    class="form-control @error('beschreibung') is-invalid @enderror"
                                    placeholder="Bitte Tätigkeit beschreiben..."
                                    style="{{ ($aktuellerWert && !$istVorgabe) ? '' : 'display:none' }}">{{ $aktuellerWert }}</textarea>
                                @error('beschreibung')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maximal 500 Zeichen.</div>
                            </div>

                            <script>
                            function handleBeschreibungAuswahl(select) {
                                const textarea = document.getElementById('beschreibung');
                                if (select.value === 'sonstiges') {
                                    textarea.style.display = 'block';
                                    textarea.value = '';
                                    textarea.focus();
                                } else {
                                    textarea.style.display = 'none';
                                    textarea.value = select.value;
                                }
                            }
                            </script>

                        </div>

                        {{-- Formular-Buttons --}}
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('mitarbeiter.zeiterfassung.index') }}"
                               class="btn btn-outline-secondary">Abbrechen</a>
                            <button type="submit" class="btn btn-primary">Änderungen speichern</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
