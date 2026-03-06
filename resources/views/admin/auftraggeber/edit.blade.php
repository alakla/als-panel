{{-- Formular zum Bearbeiten eines vorhandenen Auftraggebers --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold mb-0">Auftraggeber bearbeiten</h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('admin.auftraggeber.index') }}" class="text-decoration-none">Auftraggeberliste</a>
                &rsaquo; {{ $auftraggeber->firmenname }}
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Daten bearbeiten</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.auftraggeber.update', $auftraggeber) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="firmenname" class="form-label">Firmenname <span class="text-danger">*</span></label>
                                <input type="text" id="firmenname" name="firmenname"
                                    value="{{ old('firmenname', $auftraggeber->firmenname) }}"
                                    class="form-control @error('firmenname') is-invalid @enderror" required>
                                @error('firmenname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="ansprechpartner" class="form-label">Ansprechpartner <span class="text-danger">*</span></label>
                                <input type="text" id="ansprechpartner" name="ansprechpartner"
                                    value="{{ old('ansprechpartner', $auftraggeber->ansprechpartner) }}"
                                    class="form-control @error('ansprechpartner') is-invalid @enderror" required>
                                @error('ansprechpartner') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">E-Mail-Adresse <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email"
                                    value="{{ old('email', $auftraggeber->email) }}"
                                    class="form-control @error('email') is-invalid @enderror" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telefon" class="form-label">Telefon</label>
                                <input type="text" id="telefon" name="telefon"
                                    value="{{ old('telefon', $auftraggeber->telefon) }}"
                                    class="form-control">
                            </div>

                            <div class="col-12">
                                <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                                <textarea id="adresse" name="adresse" rows="3"
                                    class="form-control @error('adresse') is-invalid @enderror" required>{{ old('adresse', $auftraggeber->adresse) }}</textarea>
                                @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="stundensatz" class="form-label">Stundensatz (€) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="stundensatz" name="stundensatz"
                                        value="{{ old('stundensatz', $auftraggeber->stundensatz) }}"
                                        step="0.01" min="0"
                                        class="form-control @error('stundensatz') is-invalid @enderror" required>
                                    <span class="input-group-text">€/Std.</span>
                                    @error('stundensatz') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.auftraggeber.index') }}" class="btn btn-outline-secondary">Abbrechen</a>
                            <button type="submit" class="btn btn-primary">Aenderungen speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
