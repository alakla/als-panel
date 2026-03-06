{{-- Formular zum Erstellen einer neuen Rechnung --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold mb-0">Neue Rechnung erstellen</h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('admin.rechnungen.index') }}" class="text-decoration-none">Rechnungen</a>
                &rsaquo; Neue Rechnung
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Abrechnungsparameter</div>
                <div class="card-body">

                    {{-- Schritt 1: Vorschau anzeigen (POST an vorschau-Route) --}}
                    <form method="POST" action="{{ route('admin.rechnungen.vorschau') }}">
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
                                            ({{ number_format($ag->stundensatz, 2, ',', '.') }} €/Std.)
                                        </option>
                                    @endforeach
                                </select>
                                @error('auftraggeber_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Abrechnungszeitraum: Von-Datum --}}
                            <div class="col-md-6">
                                <label for="zeitraum_von" class="form-label">
                                    Zeitraum von <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="zeitraum_von" name="zeitraum_von"
                                    value="{{ old('zeitraum_von', now()->startOfMonth()->format('Y-m-d')) }}"
                                    class="form-control @error('zeitraum_von') is-invalid @enderror" required>
                                @error('zeitraum_von')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Abrechnungszeitraum: Bis-Datum --}}
                            <div class="col-md-6">
                                <label for="zeitraum_bis" class="form-label">
                                    Zeitraum bis <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="zeitraum_bis" name="zeitraum_bis"
                                    value="{{ old('zeitraum_bis', now()->endOfMonth()->format('Y-m-d')) }}"
                                    class="form-control @error('zeitraum_bis') is-invalid @enderror" required>
                                @error('zeitraum_bis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.rechnungen.index') }}"
                               class="btn btn-outline-secondary">Abbrechen</a>
                            <button type="submit" class="btn btn-primary">Vorschau anzeigen</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- Hinweisbox --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Wie funktioniert die Rechnungserstellung?</h6>
                    <ol class="small text-muted mb-0">
                        <li class="mb-2">Auftraggeber und Abrechnungszeitraum auswaehlen</li>
                        <li class="mb-2">Vorschau pruefen: Welche freigegebenen Zeiteintraege werden abgerechnet?</li>
                        <li class="mb-2">Rechnung erstellen: Das System berechnet automatisch Netto, MwSt und Brutto</li>
                        <li class="mb-2">Eine PDF-Datei wird automatisch generiert und steht zum Download bereit</li>
                    </ol>
                    <hr>
                    <p class="small text-muted mb-0">
                        <strong>Hinweis:</strong> Nur Zeiteintraege mit Status
                        <span class="badge bg-success">Freigegeben</span>
                        werden in die Rechnung aufgenommen.
                    </p>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
