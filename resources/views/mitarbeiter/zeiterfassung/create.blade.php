{{-- Formular zum Erstellen eines neuen Zeiteintrags --}}
{{-- Zugriff: Nur Mitarbeitende --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold mb-0">Neuer Zeiteintrag</h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('mitarbeiter.zeiterfassung.index') }}" class="text-decoration-none">Zeiterfassung</a>
                &rsaquo; Neuer Eintrag
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Arbeitszeit erfassen</div>
                <div class="card-body">

                    {{-- Formular: POST an ZeiterfassungController@store --}}
                    <form method="POST" action="{{ route('mitarbeiter.zeiterfassung.store') }}">
                        @csrf

                        <div class="row g-3">

                            {{-- Auftraggeber-Auswahl --}}
                            <div class="col-12">
                                <label for="auftraggeber_id" class="form-label">
                                    Auftraggeber <span class="text-danger">*</span>
                                </label>
                                <select id="auftraggeber_id" name="auftraggeber_id"
                                    class="form-select @error('auftraggeber_id') is-invalid @enderror" required>
                                    <option value="">— Auftraggeber auswaehlen —</option>
                                    @foreach($auftraggeber as $ag)
                                        <option value="{{ $ag->id }}"
                                            {{ old('auftraggeber_id') == $ag->id ? 'selected' : '' }}>
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
                                    value="{{ old('datum', now()->format('Y-m-d')) }}"
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
                                        value="{{ old('stunden') }}"
                                        step="0.5" min="0.5" max="24"
                                        class="form-control @error('stunden') is-invalid @enderror"
                                        placeholder="z.B. 8" required>
                                    <span class="input-group-text">Std.</span>
                                    @error('stunden')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Taetigkeitsbeschreibung (optional) --}}
                            <div class="col-12">
                                <label for="beschreibung" class="form-label">Taetigkeitsbeschreibung</label>
                                <textarea id="beschreibung" name="beschreibung" rows="3"
                                    class="form-control @error('beschreibung') is-invalid @enderror"
                                    placeholder="Was haben Sie heute gemacht? (optional)">{{ old('beschreibung') }}</textarea>
                                @error('beschreibung')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maximal 500 Zeichen. Optional.</div>
                            </div>

                        </div>

                        {{-- Formular-Buttons --}}
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('mitarbeiter.zeiterfassung.index') }}"
                               class="btn btn-outline-secondary">Abbrechen</a>
                            <button type="submit" class="btn btn-primary">Eintrag speichern</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
