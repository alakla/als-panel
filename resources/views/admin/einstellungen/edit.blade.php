{{--
    Firmeneinstellungen – Bearbeitungsformular
    ==========================================
    Ermöglicht dem Admin, die Firmendaten zu bearbeiten,
    die automatisch in jeder Rechnungsvorschau erscheinen.

    Funktionen:
    - "Einstellungen speichern" ist deaktiviert bis eine Änderung gemacht wird
    - "Auf Standardwerte zurücksetzen" stellt die Originalwerte wieder her
    Zugriff: Nur Administratoren
--}}
<x-app-layout>

{{-- ====== SEITENKOPF ====== --}}
<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h3 mb-0">Rechnungeinstellungen</h1>
        <p class="text-muted mb-0">
            Diese Daten erscheinen automatisch in jeder Rechnungsvorschau und im PDF.
        </p>
    </div>
    <div class="col-auto"></div>
</div>

<form id="einstellungen-formular" method="POST" action="{{ route('admin.einstellungen.update') }}">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- ====== LINKE SPALTE: Allgemeine Felder ====== --}}
        <div class="col-lg-6">

            {{-- Absenderzeile --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    <i class="bi bi-envelope me-2"></i>Absenderzeile
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Erscheint klein und blau unter der Trennlinie – direkt über der Empfängeradresse.
                    </p>
                    <input type="text"
                           id="feld_absender"
                           name="absender"
                           class="form-control @error('absender') is-invalid @enderror"
                           value="{{ old('absender', $einstellung->absender) }}">
                    @error('absender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Anrede + Einleitung --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    <i class="bi bi-text-left me-2"></i>Anrede &amp; Einleitung
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Erscheinen am Anfang der Rechnung, direkt vor der Positionstabelle.
                    </p>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Anrede</label>
                        <input type="text"
                               id="feld_anrede"
                               name="anrede"
                               class="form-control @error('anrede') is-invalid @enderror"
                               value="{{ old('anrede', $einstellung->anrede) }}">
                        @error('anrede')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-1">Einleitungssatz</label>
                        <input type="text"
                               id="feld_einleitung"
                               name="einleitung"
                               class="form-control @error('einleitung') is-invalid @enderror"
                               value="{{ old('einleitung', $einstellung->einleitung) }}">
                        @error('einleitung')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Standard-Zahlungstext --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    <i class="bi bi-cash me-2"></i>Standard-Zahlungstext
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Erscheint nach der Positionstabelle. Das Wort "Rechnungsnummer" wird automatisch fett gedruckt.
                    </p>
                    <textarea id="feld_zahlungstext"
                              name="zahlungstext"
                              rows="3"
                              class="form-control @error('zahlungstext') is-invalid @enderror">{{ old('zahlungstext', $einstellung->zahlungstext) }}</textarea>
                    @error('zahlungstext')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Grussformel --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    <i class="bi bi-chat-text me-2"></i>Standard-Grussformel
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Erscheint nach dem Zahlungstext am Ende der Rechnung.
                    </p>
                    <textarea id="feld_gruss"
                              name="gruss"
                              rows="3"
                              class="form-control @error('gruss') is-invalid @enderror">{{ old('gruss', $einstellung->gruss) }}</textarea>
                    @error('gruss')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>

        {{-- ====== RECHTE SPALTE: Footer-Informationen ====== --}}
        <div class="col-lg-6">

            {{-- Footer: Firmeninfo --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    <i class="bi bi-building me-2"></i>Footer – Firmeninformationen
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Erscheint in der linken Spalte des Rechnungs-Footers (unter "ALS Dienstleistungen").
                    </p>
                    <textarea id="feld_footer_firma"
                              name="footer_firma"
                              rows="5"
                              class="form-control font-monospace @error('footer_firma') is-invalid @enderror">{{ old('footer_firma', $einstellung->footer_firma) }}</textarea>
                    @error('footer_firma')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Footer: Kontakt --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    <i class="bi bi-telephone me-2"></i>Footer – Kontaktdaten
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Erscheint in der mittleren Spalte des Rechnungs-Footers.
                    </p>
                    <textarea id="feld_footer_kontakt"
                              name="footer_kontakt"
                              rows="5"
                              class="form-control font-monospace @error('footer_kontakt') is-invalid @enderror">{{ old('footer_kontakt', $einstellung->footer_kontakt) }}</textarea>
                    @error('footer_kontakt')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Footer: Bankverbindung --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    <i class="bi bi-bank me-2"></i>Footer – Bankverbindung
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Erscheint in der rechten Spalte des Rechnungs-Footers.
                    </p>
                    <textarea id="feld_footer_bank"
                              name="footer_bank"
                              rows="5"
                              class="form-control font-monospace @error('footer_bank') is-invalid @enderror">{{ old('footer_bank', $einstellung->footer_bank) }}</textarea>
                    @error('footer_bank')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>
    </div>

    {{-- ====== AKTIONSLEISTE ====== --}}
    <div class="d-flex justify-content-between align-items-center mb-5">

        {{-- Auf Standardwerte zurücksetzen --}}
        <button type="button"
                id="btn-reset"
                class="btn btn-outline-secondary">
            <i class="bi bi-arrow-counterclockwise me-2"></i>Auf Standardwerte zurücksetzen
        </button>

        {{-- Speichern (deaktiviert bis eine Änderung gemacht wird) --}}
        <button type="submit"
                id="btn-speichern"
                class="btn btn-primary px-5"
                disabled>
            <i class="bi bi-floppy me-2"></i>Einstellungen speichern
        </button>

    </div>

</form>

{{-- ====== VORSCHAU-HINWEIS ====== --}}
<div class="card border-info mb-4">
    <div class="card-body text-info-emphasis bg-info-subtle rounded">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Hinweis:</strong> Änderungen gelten für alle zukünftigen Rechnungen.
        In der Rechnungsvorschau können die Werte vor dem Erstellen noch angepasst werden.
    </div>
</div>

{{-- ====== JAVASCRIPT: Änderungserkennung + Standardwerte ====== --}}
<script>
(function () {

    /**
     * Standardwerte aus PHP – werden als JSON übergeben,
     * damit sie immer mit dem Model synchron sind.
     */
    var standardwerte = @json($standardwerte);

    /**
     * IDs aller Formularfelder und der zugehörige Schlüssel
     * im $standardwerte-Array.
     */
    var felder = [
        { id: 'feld_absender',       key: 'absender'       },
        { id: 'feld_anrede',         key: 'anrede'         },
        { id: 'feld_einleitung',     key: 'einleitung'     },
        { id: 'feld_zahlungstext',   key: 'zahlungstext'   },
        { id: 'feld_gruss',          key: 'gruss'          },
        { id: 'feld_footer_firma',   key: 'footer_firma'   },
        { id: 'feld_footer_kontakt', key: 'footer_kontakt' },
        { id: 'feld_footer_bank',    key: 'footer_bank'    },
    ];

    var btnSpeichern = document.getElementById('btn-speichern');
    var btnReset     = document.getElementById('btn-reset');

    /**
     * Ursprüngliche Werte beim Seitenladen speichern –
     * dienen als Referenz für die Änderungserkennung.
     */
    var ursprungswerte = {};
    felder.forEach(function (f) {
        var el = document.getElementById(f.id);
        if (el) ursprungswerte[f.id] = el.value;
    });

    /**
     * Prüft ob irgendeines der Felder vom Ursprungswert abweicht.
     * Wenn ja: Speichern-Button aktivieren. Wenn nein: deaktivieren.
     */
    function pruefeAenderungen() {
        var geaendert = felder.some(function (f) {
            var el = document.getElementById(f.id);
            return el && el.value !== ursprungswerte[f.id];
        });
        btnSpeichern.disabled = !geaendert;
    }

    /**
     * Event-Listener auf alle Felder setzen –
     * bei jeder Eingabe wird geprüft ob Änderungen vorliegen.
     */
    felder.forEach(function (f) {
        var el = document.getElementById(f.id);
        if (el) el.addEventListener('input', pruefeAenderungen);
    });

    /**
     * "Auf Standardwerte zurücksetzen"-Button:
     * Füllt alle Felder mit den Standardwerten und prüft danach
     * ob der neue Zustand vom Ursprung abweicht (um Speichern zu aktivieren).
     */
    btnReset.addEventListener('click', function () {
        felder.forEach(function (f) {
            var el = document.getElementById(f.id);
            if (el) el.value = standardwerte[f.key];
        });
        // Nach dem Zurücksetzen Änderungsstatus neu prüfen
        pruefeAenderungen();
    });

})();
</script>

</x-app-layout>
