{{-- Formular zum Bearbeiten eines vorhandenen Mitarbeitenden --}}
{{-- Zugriff: Nur Administratoren --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold mb-0">Mitarbeiter bearbeiten</h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('admin.mitarbeiter.index') }}" class="text-decoration-none">Mitarbeiterliste</a>
                &rsaquo; {{ $mitarbeiter->user->name }}
            </p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Daten bearbeiten</div>
                <div class="card-body">

                    {{-- Formular: PUT an MitarbeiterController@update --}}
                    <form method="POST" action="{{ route('admin.mitarbeiter.update', $mitarbeiter) }}">
                        @csrf
                        @method('PUT')

                        {{-- Login-Daten --}}
                        <h6 class="text-muted mb-3 border-bottom pb-2">Login-Daten</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Vollstaendiger Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                    value="{{ old('name', $mitarbeiter->user->name) }}"
                                    class="form-control @error('name') is-invalid @enderror" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">E-Mail-Adresse <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email"
                                    value="{{ old('email', $mitarbeiter->user->email) }}"
                                    class="form-control @error('email') is-invalid @enderror" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Passwort optional beim Bearbeiten --}}
                            <div class="col-md-6">
                                <label for="password" class="form-label">Neues Passwort</label>
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">Leer lassen, um das Passwort nicht zu aendern.</div>
                            </div>
                        </div>

                        {{-- Stammdaten --}}
                        <h6 class="text-muted mb-3 border-bottom pb-2 mt-4">Stammdaten</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="personalnummer" class="form-label">Personalnummer <span class="text-danger">*</span></label>
                                <input type="text" id="personalnummer" name="personalnummer"
                                    value="{{ old('personalnummer', $mitarbeiter->personalnummer) }}"
                                    class="form-control @error('personalnummer') is-invalid @enderror" required>
                                @error('personalnummer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="einstellungsdatum" class="form-label">Einstellungsdatum <span class="text-danger">*</span></label>
                                <input type="date" id="einstellungsdatum" name="einstellungsdatum"
                                    value="{{ old('einstellungsdatum', $mitarbeiter->einstellungsdatum->format('Y-m-d')) }}"
                                    class="form-control @error('einstellungsdatum') is-invalid @enderror" required>
                                @error('einstellungsdatum') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="stundenlohn" class="form-label">Stundenlohn (€) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="stundenlohn" name="stundenlohn" step="0.01" min="0"
                                        value="{{ old('stundenlohn', $mitarbeiter->stundenlohn) }}"
                                        class="form-control @error('stundenlohn') is-invalid @enderror" required>
                                    <span class="input-group-text">€/Std.</span>
                                    @error('stundenlohn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Formular-Buttons --}}
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.mitarbeiter.index') }}" class="btn btn-outline-secondary">Abbrechen</a>
                            <button type="submit" class="btn btn-primary">Aenderungen speichern</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
