{{-- Tätigkeitenverwaltung – Admin kann Tätigkeiten hinzufügen, umbenennen und löschen --}}
{{-- Diese Liste erscheint bei Mitarbeitenden als Auswahl in der Zeiterfassung --}}
<x-app-layout>

    {{-- Seitenkopf --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold mb-0">Tätigkeiten verwalten</h4>
            <p class="text-muted small mb-0">Vordefinierte Beschreibungen für die Mitarbeiter-Zeiterfassung</p>
        </div>
        <div class="col-auto">
            <span class="text-muted small">
                Aktualisierung in <span id="refreshCountdown" class="fw-semibold">60</span>s
                <a href="{{ request()->fullUrl() }}" class="ms-1 text-decoration-none">&#8635;</a>
            </span>
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

    {{-- Validierungsfehler --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">

        {{-- Linke Spalte: Liste der vorhandenen Tätigkeiten --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    Vorhandene Tätigkeiten
                    <span class="badge bg-secondary ms-1">{{ $taetigkeiten->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Bezeichnung</th>
                                <th>Abrechnungsart</th>
                                <th>Betrag</th>
                                <th class="text-end">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($taetigkeiten as $t)
                                <tr>
                                    <td class="text-muted small">{{ $t->reihenfolge }}</td>
                                    <td>{{ $t->name }}</td>
                                    <td>
                                        {{-- Abrechnungsart als Badge anzeigen --}}
                                        @if($t->abrechnungsart === 'pauschal')
                                            <span class="badge bg-info text-dark">Pauschal</span>
                                        @else
                                            <span class="badge bg-secondary">Stundensatz</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ number_format($t->stundensatz, 2, ',', '.') }}
                                        {{ $t->abrechnungsart === 'pauschal' ? '€' : '€/Std.' }}
                                    </td>
                                    <td class="text-end">

                                        {{-- Bearbeiten-Button oeffnet Modal --}}
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal{{ $t->id }}">
                                            Bearbeiten
                                        </button>

                                        {{-- Löschen-Formular mit Bestätigung --}}
                                        <form method="POST"
                                              action="{{ route('admin.taetigkeiten.destroy', $t) }}"
                                              class="d-inline"
                                              data-confirm='Tätigkeit wirklich löschen?' data-confirm-btn="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Löschen</button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Bearbeiten-Modal für jede Tätigkeit --}}
                                <div class="modal fade" id="editModal{{ $t->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h6 class="modal-title">Tätigkeit bearbeiten</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.taetigkeiten.update', $t) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small">Bezeichnung</label>
                                                        <input type="text" name="name" value="{{ $t->name }}"
                                                               class="form-control" required maxlength="100">
                                                    </div>

                                                    {{-- Abrechnungsart wählen: Stundensatz oder Pauschal --}}
                                                    <div class="mb-3">
                                                        <label class="form-label small">Abrechnungsart</label>
                                                        <div class="d-flex gap-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input edit-abrechnungsart-{{ $t->id }}"
                                                                       type="radio" name="abrechnungsart"
                                                                       id="edit_stundensatz_{{ $t->id }}"
                                                                       value="stundensatz"
                                                                       {{ $t->abrechnungsart !== 'pauschal' ? 'checked' : '' }}
                                                                       onchange="updateEditLabel({{ $t->id }})">
                                                                <label class="form-check-label" for="edit_stundensatz_{{ $t->id }}">
                                                                    Stundensatz
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input edit-abrechnungsart-{{ $t->id }}"
                                                                       type="radio" name="abrechnungsart"
                                                                       id="edit_pauschal_{{ $t->id }}"
                                                                       value="pauschal"
                                                                       {{ $t->abrechnungsart === 'pauschal' ? 'checked' : '' }}
                                                                       onchange="updateEditLabel({{ $t->id }})">
                                                                <label class="form-check-label" for="edit_pauschal_{{ $t->id }}">
                                                                    Pauschal
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-0">
                                                        <label class="form-label small" id="editBetragLabel{{ $t->id }}">
                                                            {{ $t->abrechnungsart === 'pauschal' ? 'Pauschalbetrag (€)' : 'Stundensatz (€/Std.)' }}
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="number" name="stundensatz"
                                                                   value="{{ $t->stundensatz }}"
                                                                   class="form-control" step="0.01" min="0" required>
                                                            <span class="input-group-text" id="editBetragEinheit{{ $t->id }}">
                                                                {{ $t->abrechnungsart === 'pauschal' ? '€' : '€/Std.' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Abbrechen</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">Speichern</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Noch keine Tätigkeiten vorhanden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Rechte Spalte: Neue Tätigkeit hinzufügen --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Neue Tätigkeit hinzufügen</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.taetigkeiten.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Bezeichnung <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name') }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="z.B. Unterhaltsreinigung"
                                   maxlength="100" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Abrechnungsart: Stundensatz oder Pauschal --}}
                        <div class="mb-3">
                            <label class="form-label">Abrechnungsart <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="abrechnungsart"
                                           id="neu_stundensatz" value="stundensatz"
                                           {{ old('abrechnungsart', 'stundensatz') === 'stundensatz' ? 'checked' : '' }}
                                           onchange="updateNeuLabel()">
                                    <label class="form-check-label" for="neu_stundensatz">Stundensatz</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="abrechnungsart"
                                           id="neu_pauschal" value="pauschal"
                                           {{ old('abrechnungsart') === 'pauschal' ? 'checked' : '' }}
                                           onchange="updateNeuLabel()">
                                    <label class="form-check-label" for="neu_pauschal">Pauschal</label>
                                </div>
                            </div>
                            @error('abrechnungsart')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Betrag: Bezeichnung und Einheit hängen von der Abrechnungsart ab --}}
                        <div class="mb-3">
                            <label for="stundensatz" class="form-label" id="neuBetragLabel">
                                Stundensatz (€/Std.) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" id="stundensatz" name="stundensatz"
                                       value="{{ old('stundensatz') }}"
                                       class="form-control @error('stundensatz') is-invalid @enderror"
                                       step="0.01" min="0" required placeholder="0.00">
                                <span class="input-group-text" id="neuBetragEinheit">€/Std.</span>
                            </div>
                            @error('stundensatz')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">+ Hinzufügen</button>
                    </form>

                    <hr>
                    <p class="small text-muted mb-0">
                        <strong>Hinweis:</strong> Diese Tätigkeiten erscheinen als Auswahloptionen
                        in der Zeiterfassung der Mitarbeitenden.
                        Neue Einträge werden automatisch ans Ende der Liste gesetzt.
                    </p>
                </div>
            </div>
        </div>

    </div>

    {{-- JavaScript: Label und Einheit dynamisch anpassen je nach Abrechnungsart --}}
    <script>
        // Einheit im "Neue Tätigkeit"-Formular aktualisieren
        function updateNeuLabel() {
            var istPauschal = document.getElementById('neu_pauschal').checked;
            document.getElementById('neuBetragLabel').innerHTML =
                istPauschal ? 'Pauschalbetrag (€) <span class="text-danger">*</span>'
                            : 'Stundensatz (€/Std.) <span class="text-danger">*</span>';
            document.getElementById('neuBetragEinheit').textContent =
                istPauschal ? '€' : '€/Std.';
        }

        // Einheit im Bearbeiten-Modal aktualisieren
        function updateEditLabel(id) {
            var pauschalRadio = document.getElementById('edit_pauschal_' + id);
            var istPauschal = pauschalRadio && pauschalRadio.checked;
            var labelEl  = document.getElementById('editBetragLabel'  + id);
            var einheitEl = document.getElementById('editBetragEinheit' + id);
            if (labelEl)   labelEl.textContent  = istPauschal ? 'Pauschalbetrag (€)' : 'Stundensatz (€/Std.)';
            if (einheitEl) einheitEl.textContent = istPauschal ? '€' : '€/Std.';
        }

        // Beim Laden sicherstellen, dass der korrekte Anfangszustand angezeigt wird (old()-Wert)
        document.addEventListener('DOMContentLoaded', function () {
            updateNeuLabel();
        });
    </script>

</x-app-layout>
