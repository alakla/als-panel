{{--
    Rechnungsvorschau (editierbar) – ALS Panel
    =============================================
    Zeigt eine druckähnliche Vorschau der Rechnung mit vollständig
    editierbaren Feldern. Der Nutzer kann alle Texte, Positionen,
    Preise und Footer-Angaben direkt in der Vorschau anpassen,
    bevor die Rechnung endgültig erstellt und als PDF gespeichert wird.

    Zugriff: Nur Administratoren
    Daten vom Controller: $auftraggeber, $positionen, $nettobetrag,
                          $mwstBetrag, $gesamtbetrag, $zeitraumVon, $zeitraumBis, $request
--}}
<x-app-layout>

{{-- ====== SEITEN-STYLES ====== --}}
<style>
    /*
     * Hintergrundfarbe der Seite – hellgrau, damit das weiße
     * Papier optisch hervorsticht (wie ein Druck-Preview)
     */
    body, .app-main {
        background: #e8e8e8 !important;
    }

    /*
     * Das "Papier": zentriertes weißes Blatt mit Schatten
     * Breite 820px entspricht in etwa einer A4-Seite im Browser
     */
    #rechnungs-papier {
        max-width: 820px;
        margin: 24px auto 60px auto;
        background: #ffffff;
        box-shadow: 0 4px 32px rgba(0,0,0,0.18);
        padding: 44px 52px 36px 52px;
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 10pt;
        color: #222;
        position: relative;
    }

    /*
     * Transparente editierbare Felder:
     * Kein sichtbarer Rahmen, gepunktete Unterlinie zeigt an, dass
     * das Feld editierbar ist. Font-Eigenschaften werden vererbt,
     * damit das Feld optisch nahtlos in den Inhalt passt.
     */
    .editable-field {
        border: none;
        border-bottom: 1.5px dashed #ccc;
        background: transparent;
        font-family: inherit;
        font-size: inherit;
        color: inherit;
        font-weight: inherit;
        outline: none;
        width: 100%;
        padding: 0 2px;
        line-height: inherit;
    }
    .editable-field:focus {
        background: rgba(26,86,160,0.05);
        border-bottom-color: #0d6efd;
    }

    /* Textarea-Variante: mehrzeilig, ohne Scrollbar-Resize-Handle */
    .editable-textarea {
        border: none;
        border-bottom: 1.5px dashed #ccc;
        background: transparent;
        font-family: inherit;
        font-size: inherit;
        color: inherit;
        font-weight: inherit;
        outline: none;
        width: 100%;
        padding: 0 2px;
        resize: vertical;
        overflow: hidden;
    }
    .editable-textarea:focus {
        background: rgba(26,86,160,0.05);
        border-bottom-color: #0d6efd;
    }

    /* ===== HEADER: "RECHNUNG" + LOGO ===== */
    .rechnung-heading {
        font-size: 32pt;
        font-weight: normal;
        color: #c8c8c8;
        letter-spacing: 3px;
        line-height: 1;
    }

    /* Trennlinie in ALS-Blau */
    .blaue-linie {
        border: none;
        border-top: 2px solid #0d6efd;
        margin: 6px 0 30px 0;
    }

    /* Absenderzeile in ALS-Blau, klein */
    .absender-zeile {
        font-size: 7.5pt;
        color: #0d6efd;
        margin-bottom: 16px;
    }
    .absender-zeile .editable-field {
        font-size: 7.5pt;
        color: #0d6efd;
    }

    /* Empfänger: fett, größer */
    .empfaenger-block {
        font-weight: bold;
        font-size: 10.5pt;
        line-height: 2;
        margin-bottom: 28px;
    }
    .empfaenger-block .editable-field {
        font-weight: bold;
        font-size: 10.5pt;
    }

    /* Rechnungsnummer: blau, gross */
    .rech-nr {
        font-size: 13pt;
        color: #0d6efd;
        font-weight: bold;
    }
    .rech-datum-label {
        font-size: 10.5pt;
        text-align: right;
    }

    /* Positionen-Tabelle */
    table.pos-tabelle {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
        margin-bottom: 0;
    }
    table.pos-tabelle thead th {
        border-top: 2px solid #0d6efd;
        border-bottom: 2px solid #0d6efd;
        padding: 6px 7px;
        font-weight: bold;
        background-color: rgba(13,110,253,0.18);
        text-align: left;
    }
    table.pos-tabelle thead th.r { text-align: right; }
    table.pos-tabelle tbody td {
        padding: 5px 7px;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }
    table.pos-tabelle tbody td.r { text-align: right; }
    table.pos-tabelle tbody tr.ungerade td { background: #ffffff; }
    table.pos-tabelle tbody tr.gerade   td { background: #f5f7fa; }
    table.pos-tabelle tfoot td {
        border-top: 1.5px solid #888;
        height: 1px;
        padding: 0;
    }

    /* Summenblock rechts */
    table.summen {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5pt;
    }
    table.summen td {
        padding: 4px 8px;
    }
    table.summen td.lab { color: #555; }
    table.summen td.val { text-align: right; font-weight: bold; }
    table.summen td.val-normal { text-align: right; }
    table.summen tr.gesamt td {
        border-top: 2px solid #0d6efd;
        background-color: rgba(13,110,253,0.18);
        font-weight: bold;
        font-size: 10.5pt;
        padding-top: 6px;
    }
    table.summen tr.gesamt td.val-gesamt { text-align: right; }

    /* Readonly-Summenfelder (per JS aktualisiert) */
    .summen-wert {
        font-weight: bold;
        color: #222;
    }

    /* Zahlungstext + Gruss */
    .zahlungstext { margin: 24px 0 18px 0; font-size: 9.5pt; }
    .gruss-block  { font-size: 9.5pt; line-height: 1.9; }

    /* Footer: 3 Spalten */
    .footer-block {
        border-top: 2px solid #0d6efd;
        margin-top: 20px;
        padding-top: 10px;
        font-size: 7.5pt;
        color: #444;
    }
    .footer-block .footer-head { font-weight: bold; margin-bottom: 3px; }

    /* Aktions-Buttons oben/unten */
    .aktions-leiste {
        max-width: 820px;
        margin: 0 auto 12px auto;
        display: flex;
        gap: 10px;
        align-items: center;
    }
</style>

{{-- ====== AKTIONSLEISTE OBEN ====== --}}
<div class="aktions-leiste">
    {{-- Zurück zum Rechnungsformular --}}
    <a href="{{ route('admin.rechnungen.create') }}" class="btn btn-outline-secondary btn-sm">
        &#8592; Zurück
    </a>
    {{-- Submit-Button als erstes, damit Tastatur-Nutzer ihn finden --}}
    <button type="submit" form="rechnungs-formular" class="btn btn-primary btn-sm ms-auto">
        Rechnung erstellen &amp; PDF generieren
    </button>
</div>

{{--
    HAUPT-FORMULAR
    ==============
    Alle editierbaren Felder werden in diesem Formular übertragen.
    Das Formular umschließt das gesamte Papier-Div.
--}}
<form id="rechnungs-formular"
      method="POST"
      action="{{ route('admin.rechnungen.store') }}">
    @csrf

    {{-- ====== VERSTECKTE PFLICHTFELDER ====== --}}
    {{-- Auftraggeber und Zeitraum werden aus der Vorschau-Anfrage weitergeleitet --}}
    <input type="hidden" name="auftraggeber_id" value="{{ $auftraggeber->id }}">
    <input type="hidden" name="zeitraum_von"    value="{{ $request->zeitraum_von }}">
    <input type="hidden" name="zeitraum_bis"    value="{{ $request->zeitraum_bis }}">

    {{-- ====== DAS PAPIER ====== --}}
    <div id="rechnungs-papier">

        {{-- ====== KOPFZEILE: "RECHNUNG" + LOGO ====== --}}
        <div class="d-flex justify-content-between align-items-end mb-0">
            <div class="rechnung-heading">RECHNUNG</div>
            <div>
                {{-- Logo-Bild aus dem Public-Verzeichnis --}}
                <img src="{{ asset('logo.png') }}"
                     style="width:110px; height:auto;"
                     alt="ALS">
            </div>
        </div>

        {{-- Blaue Trennlinie unterhalb des Headers --}}
        <hr class="blaue-linie">

        {{-- ====== ABSENDERZEILE (blau, klein) – Wert aus Firmeneinstellungen ====== --}}
        <div class="absender-zeile">
            <input type="text"
                   name="absender"
                   class="editable-field"
                   value="{{ $einstellung->absender }}"
                   title="Absenderzeile bearbeiten">
        </div>

        {{-- ====== EMPFÄNGER-ADRESSE ====== --}}
        <div class="empfaenger-block">
            @php
                // Adresse in Zeilen aufteilen: Zeile 0 = Straße, Zeile 1 = PLZ + Ort
                $adresseZeilen = array_values(array_filter(
                    array_map('trim', explode("\n", $auftraggeber->adresse ?? ''))
                ));
            @endphp

            {{-- Zeile 1: Firmenname --}}
            <input type="text"
                   name="empfaenger_name"
                   class="editable-field"
                   value="{{ $auftraggeber->firmenname }}"
                   title="Firmenname">

            {{-- Zeile 2: Straße + Hausnummer --}}
            <input type="text"
                   name="adresse_zeilen[]"
                   class="editable-field"
                   value="{{ $adresseZeilen[0] ?? '' }}"
                   placeholder="Straße, Hausnummer"
                   title="Straße und Hausnummer">

            {{-- Zeile 3: PLZ + Ort --}}
            <input type="text"
                   name="adresse_zeilen[]"
                   class="editable-field"
                   value="{{ $adresseZeilen[1] ?? '' }}"
                   placeholder="PLZ Ort"
                   title="PLZ und Ort">
        </div>

        {{-- ====== RECHNUNGS-NR + DATUM ====== --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                {{-- Vorschau der nächsten Rechnungsnummer (wird beim Speichern endgültig vergeben) --}}
                <span class="rech-nr">
                    Rechnung Nr. <strong>{{ $vorschauNummer }}</strong>
                </span>
            </div>
            <div class="rech-datum-label d-flex align-items-center gap-2">
                <span>Datum:</span>
                {{-- Rechnungsdatum: editierbar, Standard = heute --}}
                <input type="date"
                       name="rechnungsdatum"
                       class="editable-field"
                       style="width:auto;"
                       value="{{ now()->format('Y-m-d') }}"
                       title="Rechnungsdatum">
            </div>
        </div>

        {{-- ====== ANREDE + EINLEITUNGSTEXT (Werte aus Firmeneinstellungen) ====== --}}
        <div style="margin-bottom: 6px;">
            <input type="text"
                   name="anrede"
                   class="editable-field"
                   value="{{ $einstellung->anrede }}"
                   title="Anrede">
        </div>
        <div style="margin-bottom: 14px;">
            <input type="text"
                   name="einleitung"
                   class="editable-field"
                   value="{{ $einstellung->einleitung }}"
                   title="Einleitungstext">
        </div>

        {{-- ====== POSITIONEN-TABELLE ====== --}}
        <table class="pos-tabelle" id="positionen-tabelle">
            <thead>
                <tr>
                    <th style="width:30px">Pos</th>
                    <th>Beschreibung</th>
                    <th style="width:115px">Zeitraum</th>
                    <th class="r" style="width:50px">Menge</th>
                    <th style="width:70px">Einheit</th>
                    <th class="r" style="width:90px">Einzelpreis</th>
                    <th class="r" style="width:90px">Gesamtpreis</th>
                    <th style="width:32px"></th>{{-- Löschen-Spalte --}}
                </tr>
            </thead>
            <tbody id="positionen-body">
                {{--
                    Jede Position wird als editierbare Zeile dargestellt.
                    Index i wird für die Array-Felder positionen[i][...] verwendet.
                    JavaScript aktualisiert die Indices bei Änderungen.
                --}}
                @foreach($positionen as $i => $pos)
                    @php
                        // Pauschal: Menge ist immer 1 und wird deaktiviert
                        $istPauschal = $pos['einheit'] === 'Pauschal';
                    @endphp
                    <tr class="{{ $loop->even ? 'gerade' : 'ungerade' }}" data-index="{{ $i }}">
                        {{-- Positionsnummer (wird per JS neu nummeriert) --}}
                        <td class="pos-nr">{{ $i + 1 }}</td>

                        {{-- Beschreibung/Name der Tätigkeit – Dropdown aus den gespeicherten Tätigkeiten --}}
                        <td>
                            <select name="positionen[{{ $i }}][name]"
                                    class="editable-field pos-name"
                                    title="Beschreibung"
                                    style="width:100%; cursor:pointer;">
                                @foreach($taetigkeiten as $t)
                                    <option value="{{ $t->name }}"
                                        {{ $pos['name'] === $t->name ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                                {{-- Falls der aktuelle Wert keiner gespeicherten Tätigkeit entspricht (z.B. manuell eingegeben) --}}
                                @if($taetigkeiten->where('name', $pos['name'])->isEmpty())
                                    <option value="{{ $pos['name'] }}" selected>{{ $pos['name'] }}</option>
                                @endif
                            </select>
                        </td>

                        {{-- Zeitraum: editierbar (voller Monat -> "März 2026", sonst "TT.MM.JJ – TT.MM.JJ") --}}
                        <td style="font-size:8pt;">
                            <input type="text"
                                   name="positionen[{{ $i }}][zeitraum]"
                                   class="editable-field pos-zeitraum"
                                   value="{{ $zeitraumAnzeige }}"
                                   title="Zeitraum bearbeiten"
                                   style="font-size:8pt; color:#555; width:100%;">
                        </td>

                        {{-- Menge: bei Pauschal deaktiviert und auf 1 gesetzt --}}
                        <td class="r">
                            <input type="number"
                                   name="positionen[{{ $i }}][menge]"
                                   class="editable-field pos-menge text-end"
                                   value="{{ $istPauschal ? 1 : number_format($pos['menge'], 2, '.', '') }}"
                                   step="0.5"
                                   min="0"
                                   {{ $istPauschal ? 'disabled' : '' }}
                                   title="Menge"
                                   style="width:50px; text-align:right;">
                            {{-- Bei deaktivierten Feldern (Pauschal): Wert separat übertragen --}}
                            @if($istPauschal)
                                <input type="hidden" name="positionen[{{ $i }}][menge]" value="1">
                            @endif
                        </td>

                        {{-- Einheit: Dropdown Pauschal / Std. --}}
                        <td>
                            <select name="positionen[{{ $i }}][einheit]"
                                    class="editable-field pos-einheit"
                                    title="Einheit"
                                    style="width:auto; cursor:pointer;">
                                <option value="Pauschal" {{ $istPauschal ? 'selected' : '' }}>Pauschal</option>
                                <option value="Std."     {{ !$istPauschal ? 'selected' : '' }}>Std.</option>
                            </select>
                        </td>

                        {{-- Einzelpreis (netto): editierbar --}}
                        <td class="r">
                            <input type="number"
                                   name="positionen[{{ $i }}][einzelpreis]"
                                   class="editable-field pos-einzelpreis text-end"
                                   value="{{ number_format($pos['einzelpreis'], 2, '.', '') }}"
                                   step="0.01"
                                   min="0"
                                   title="Einzelpreis (netto)"
                                   style="width:80px; text-align:right;">
                        </td>

                        {{-- Gesamtpreis: wird per JS automatisch berechnet, Hidden-Feld für Übertragung --}}
                        <td class="r pos-gesamtpreis-anzeige">
                            {{ number_format($pos['gesamtpreis'], 2, ',', '.') }} &euro;
                        </td>
                        <input type="hidden"
                               name="positionen[{{ $i }}][gesamtpreis]"
                               class="pos-gesamtpreis-hidden"
                               value="{{ number_format($pos['gesamtpreis'], 2, '.', '') }}">

                        {{-- Löschen-Button für diese Zeile --}}
                        <td>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-zeile-loeschen"
                                    title="Position entfernen">
                                &times;
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            {{-- Abschlusslinie --}}
            <tfoot>
                <tr><td colspan="8"></td></tr>
            </tfoot>
        </table>

        {{-- "Position hinzufügen"-Button unter der Tabelle --}}
        <div class="mt-2 mb-3">
            <button type="button"
                    id="btn-position-hinzufuegen"
                    class="btn btn-sm btn-outline-primary">
                &#65291; Position hinzuf&uuml;gen
            </button>
        </div>

        {{-- ====== SUMMENBLOCK (volle Breite, Leerspalte links) ====== --}}
        <table class="summen" style="width:100%;">
            <colgroup>
                {{-- Linke Hälfte: Platzhalter --}}
                <col style="width:55%">
                {{-- Label-Spalte --}}
                <col>
                {{-- Wert-Spalte --}}
                <col style="width:120px">
            </colgroup>
            {{-- Leerzeile als Abstand vor dem Summenblock --}}
            <tr><td></td><td style="padding-top:10px;"></td><td style="padding-top:10px;"></td></tr>
            <tr>
                <td></td>
                <td class="lab">Nettopreis</td>
                <td class="val">
                    <span id="anzeige-netto" class="summen-wert">
                        {{ number_format($nettobetrag, 2, ',', '.') }}
                    </span> &euro;
                </td>
            </tr>
            <tr>
                <td></td>
                <td class="lab">Zzgl. 19% MwSt.</td>
                <td class="val-normal">
                    <span id="anzeige-mwst" class="summen-wert">
                        {{ number_format($mwstBetrag, 2, ',', '.') }}
                    </span> &euro;
                </td>
            </tr>
            {{-- Rechnungsbetrag: volle Breite mit blauer Hintergrundfarbe --}}
            <tr class="gesamt">
                <td></td>
                <td>Rechnungsbetrag</td>
                <td class="val-gesamt">
                    <strong>
                        <span id="anzeige-gesamt" class="summen-wert">
                            {{ number_format($gesamtbetrag, 2, ',', '.') }}
                        </span> &euro;
                    </strong>
                </td>
            </tr>
        </table>

        {{-- ====== ZAHLUNGSTEXT – Wert aus Firmeneinstellungen ====== --}}
        <div class="zahlungstext">
            <textarea name="zahlungstext"
                      class="editable-textarea"
                      rows="2"
                      title="Zahlungstext bearbeiten">{{ $einstellung->zahlungstext }}</textarea>
        </div>

        {{-- ====== GRUSS – Wert aus Firmeneinstellungen ====== --}}
        <div class="gruss-block">
            <textarea name="gruss"
                      class="editable-textarea"
                      rows="2"
                      title="Gruss bearbeiten">{{ $einstellung->gruss }}</textarea>
        </div>

        {{-- ====== FOOTER: 3 Spalten – Werte aus Firmeneinstellungen ====== --}}
        <div class="footer-block">
            <div class="row g-3">
                {{-- Spalte 1: Firmeninfo --}}
                <div class="col-4">
                    <div class="footer-head">ALS Dienstleistungen</div>
                    <textarea name="footer_firma"
                              class="editable-textarea"
                              rows="3"
                              title="Footer: Firmeninfo">{{ $einstellung->footer_firma }}</textarea>
                </div>
                {{-- Spalte 2: Kontakt --}}
                <div class="col-4">
                    <div class="footer-head">Kontakt</div>
                    <textarea name="footer_kontakt"
                              class="editable-textarea"
                              rows="3"
                              title="Footer: Kontaktinfos">{{ $einstellung->footer_kontakt }}</textarea>
                </div>
                {{-- Spalte 3: Bankverbindung (etwas nach rechts verschoben) --}}
                <div class="col-4" style="padding-left: 32px;">
                    <div class="footer-head">Bankverbindung</div>
                    <textarea name="footer_bank"
                              class="editable-textarea"
                              rows="3"
                              title="Footer: Bankverbindung">{{ $einstellung->footer_bank }}</textarea>
                </div>
            </div>
        </div>

    </div>{{-- Ende #rechnungs-papier --}}
</form>

{{-- ====== AKTIONSLEISTE UNTEN ====== --}}
<div class="aktions-leiste mt-3">
    <a href="{{ route('admin.rechnungen.create') }}" class="btn btn-outline-secondary btn-sm">
        &#8592; Zurück
    </a>
    <button type="submit" form="rechnungs-formular" class="btn btn-primary btn-sm ms-auto">
        Rechnung erstellen &amp; PDF generieren
    </button>
</div>

{{-- ====== JAVASCRIPT: Zeilenberechnung + Positions-Management ====== --}}
<script>
/**
 * Map aller Tätigkeiten aus der Datenbank, indexiert nach Name.
 * Enthält abrechnungsart und stundensatz für automatische Feldbefüllung
 * wenn der Nutzer eine andere Beschreibung auswählt.
 *
 * Struktur: { "Tätigkeit A": { einheit: "Std.", einzelpreis: 25.00 }, ... }
 */
const taetigkeitenMap = @json(
    $taetigkeiten->mapWithKeys(fn($t) => [
        $t->name => [
            'einheit'      => $t->abrechnungsart === 'pauschal' ? 'Pauschal' : 'Std.',
            'einzelpreis'  => (float) $t->stundensatz,
        ]
    ])
);
/**
 * Berechnet den Gesamtpreis einer einzelnen Tabellenzeile.
 *
 * Regeln:
 * - Pauschal: Gesamtpreis = Einzelpreis (Menge ist immer 1)
 * - Std.:     Gesamtpreis = Menge × Einzelpreis
 *
 * @param {HTMLTableRowElement} row  Die Tabellenzeile (tr)
 */
function berechneZeile(row) {
    // Eingabefelder der Zeile ermitteln
    const einheitSelect  = row.querySelector('.pos-einheit');
    const mengeInput     = row.querySelector('.pos-menge');
    const einzelpreisIn  = row.querySelector('.pos-einzelpreis');
    const gesamtAnzeige  = row.querySelector('.pos-gesamtpreis-anzeige');
    const gesamtHidden   = row.querySelector('.pos-gesamtpreis-hidden');

    if (!einheitSelect || !einzelpreisIn) return;

    const einheit      = einheitSelect.value;
    const einzelpreis  = parseFloat(einzelpreisIn.value) || 0;

    let gesamtpreis;

    if (einheit === 'Pauschal') {
        // Pauschal: Menge ignorieren, Einzelpreis = Gesamtpreis
        gesamtpreis = einzelpreis;
        if (mengeInput) {
            mengeInput.value    = 1;
            mengeInput.disabled = true;
            // Sicherstellen, dass ein Hidden-Input den Wert 1 überträgt
            let hiddenMenge = row.querySelector('.pos-menge-hidden');
            if (!hiddenMenge) {
                hiddenMenge = document.createElement('input');
                hiddenMenge.type      = 'hidden';
                hiddenMenge.className = 'pos-menge-hidden';
                hiddenMenge.value     = '1';
                row.querySelector('td:nth-child(4)').appendChild(hiddenMenge);
            }
            hiddenMenge.value = '1';
        }
    } else {
        // Stundensatz: Gesamtpreis = Menge × Einzelpreis
        const menge = parseFloat(mengeInput?.value) || 0;
        gesamtpreis = menge * einzelpreis;

        if (mengeInput) {
            mengeInput.disabled = false;
            // Alten Hidden-Input entfernen, falls vorhanden
            const altHidden = row.querySelector('.pos-menge-hidden');
            if (altHidden) altHidden.remove();
        }
    }

    // Anzeige und Hidden-Feld aktualisieren
    if (gesamtAnzeige) {
        gesamtAnzeige.textContent =
            gesamtpreis.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
    }
    if (gesamtHidden) {
        gesamtHidden.value = gesamtpreis.toFixed(2);
    }
}

/**
 * Berechnet die Gesamtsummen (Netto, MwSt, Rechnungsbetrag)
 * aus allen vorhandenen Gesamtpreisen der Positionen.
 *
 * Aktualisiert die drei Anzeige-Spans im Summenblock.
 */
function berechneSummen() {
    // Alle versteckten Gesamtpreis-Felder auslesen
    const gesamtpreisFelder = document.querySelectorAll('.pos-gesamtpreis-hidden');
    let nettoSumme = 0;

    gesamtpreisFelder.forEach(function (feld) {
        nettoSumme += parseFloat(feld.value) || 0;
    });

    // MwSt berechnen (19%)
    const mwst      = nettoSumme * 0.19;
    const gesamt    = nettoSumme + mwst;

    // Hilfsfunktion: Zahl auf deutsches Format formatieren
    function fmt(zahl) {
        return zahl.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Anzeige-Spans im DOM aktualisieren
    const anzNetto  = document.getElementById('anzeige-netto');
    const anzMwst   = document.getElementById('anzeige-mwst');
    const anzGesamt = document.getElementById('anzeige-gesamt');

    if (anzNetto)  anzNetto.textContent  = fmt(nettoSumme);
    if (anzMwst)   anzMwst.textContent   = fmt(mwst);
    if (anzGesamt) anzGesamt.textContent = fmt(gesamt);
}

/**
 * Aktualisiert die Indices aller Positionen-Zeilen.
 * Wird nach dem Hinzufügen oder Löschen einer Zeile aufgerufen.
 *
 * Aktualisiert:
 * - Positionsnummer (sichtbare "Pos"-Spalte)
 * - data-index Attribut der Zeile
 * - name-Attribute aller Inputs in der Zeile (positionen[N][...])
 * - Klasse für Zebrastreifen (gerade/ungerade)
 */
function aktualisiereIndices() {
    const zeilen = document.querySelectorAll('#positionen-body tr');
    zeilen.forEach(function (zeile, idx) {
        // Sichtbare Positionsnummer aktualisieren
        const posNr = zeile.querySelector('.pos-nr');
        if (posNr) posNr.textContent = idx + 1;

        // data-index aktualisieren
        zeile.dataset.index = idx;

        // Zebrastreifen: gerade/ungerade
        zeile.className = idx % 2 === 0 ? 'ungerade' : 'gerade';

        // name-Attribute aller Formularfelder in der Zeile aktualisieren
        zeile.querySelectorAll('[name]').forEach(function (feld) {
            // Regex: positionen[alter_index][feld_name] -> positionen[neuer_index][feld_name]
            feld.name = feld.name.replace(/positionen\[\d+\]/, 'positionen[' + idx + ']');
        });
    });
}

/**
 * Hängt Event-Listener an eine neue Positions-Zeile.
 * Wird sowohl für bestehende Zeilen (beim Laden) als auch
 * für neu hinzugefügte Zeilen aufgerufen.
 *
 * @param {HTMLTableRowElement} zeile
 */
function bindZeileEvents(zeile) {
    // Beschreibung-Dropdown: Einheit und Einzelpreis automatisch befüllen
    const nameSelect = zeile.querySelector('.pos-name');
    if (nameSelect) {
        nameSelect.addEventListener('change', function () {
            const taetigkeit = taetigkeitenMap[this.value];
            if (!taetigkeit) return;

            // Einheit setzen (Pauschal / Std.)
            const einheitSelect = zeile.querySelector('.pos-einheit');
            if (einheitSelect) einheitSelect.value = taetigkeit.einheit;

            // Einzelpreis setzen
            const einzelpreisInput = zeile.querySelector('.pos-einzelpreis');
            if (einzelpreisInput) einzelpreisInput.value = taetigkeit.einzelpreis.toFixed(2);

            // Gesamtpreis und Summen neu berechnen
            berechneZeile(zeile);
            berechneSummen();
        });
    }

    // Einheit-Dropdown: Pauschal/Std. umschalten
    const einheitSelect = zeile.querySelector('.pos-einheit');
    if (einheitSelect) {
        einheitSelect.addEventListener('change', function () {
            berechneZeile(zeile);
            berechneSummen();
        });
    }

    // Menge: Neuberechnung bei Änderung
    const mengeInput = zeile.querySelector('.pos-menge');
    if (mengeInput) {
        mengeInput.addEventListener('input', function () {
            berechneZeile(zeile);
            berechneSummen();
        });
    }

    // Einzelpreis: Neuberechnung bei Änderung
    const einzelpreisInput = zeile.querySelector('.pos-einzelpreis');
    if (einzelpreisInput) {
        einzelpreisInput.addEventListener('input', function () {
            berechneZeile(zeile);
            berechneSummen();
        });
    }

    // Löschen-Button: Zeile entfernen
    const loeschenBtn = zeile.querySelector('.btn-zeile-loeschen');
    if (loeschenBtn) {
        loeschenBtn.addEventListener('click', function () {
            // Mindestens eine Zeile behalten
            const alleZeilen = document.querySelectorAll('#positionen-body tr');
            if (alleZeilen.length <= 1) {
                alert('Mindestens eine Position muss vorhanden sein.');
                return;
            }
            zeile.remove();
            aktualisiereIndices();
            berechneSummen();
        });
    }
}

/**
 * Template-Zeile für neue Positionen.
 * Erstellt eine leere Zeile mit allen nötigen Feldern.
 *
 * @param {number} idx  Index für die name-Attribute
 * @returns {HTMLTableRowElement}
 */
function erstelleNeueZeile(idx) {
    const zeile = document.createElement('tr');
    zeile.className = idx % 2 === 0 ? 'ungerade' : 'gerade';
    zeile.dataset.index = idx;

    // Zeitraum-Text aus dem ersten vorhandenen Zeitraum-Feld auslesen
    const zeitraumInput = document.querySelector('#positionen-body .pos-zeitraum');
    const zeitraumText  = zeitraumInput ? zeitraumInput.value : '';

    // Optionen für das Beschreibung-Dropdown aus der Tätigkeiten-Map generieren
    const optionenHtml = Object.keys(taetigkeitenMap).map(function (name) {
        return '<option value="' + name.replace(/"/g, '&quot;') + '">' + name + '</option>';
    }).join('');

    zeile.innerHTML =
        '<td class="pos-nr">' + (idx + 1) + '</td>' +
        '<td>' +
            '<select name="positionen[' + idx + '][name]" ' +
            'class="editable-field pos-name" title="Beschreibung" style="width:100%; cursor:pointer;">' +
            optionenHtml +
            '</select>' +
        '</td>' +
        '<td style="font-size:8pt;">' +
            '<input type="text" name="positionen[' + idx + '][zeitraum]" ' +
            'class="editable-field pos-zeitraum" value="' + zeitraumText + '" ' +
            'title="Zeitraum bearbeiten" style="font-size:8pt; color:#555; width:100%;">' +
        '</td>' +
        '<td class="r">' +
            '<input type="number" name="positionen[' + idx + '][menge]" ' +
            'class="editable-field pos-menge text-end" value="1" step="0.5" min="0" ' +
            'title="Menge" style="width:50px; text-align:right;">' +
        '</td>' +
        '<td>' +
            '<select name="positionen[' + idx + '][einheit]" class="editable-field pos-einheit" ' +
            'title="Einheit" style="width:auto; cursor:pointer;">' +
                '<option value="Pauschal">Pauschal</option>' +
                '<option value="Std." selected>Std.</option>' +
            '</select>' +
        '</td>' +
        '<td class="r">' +
            '<input type="number" name="positionen[' + idx + '][einzelpreis]" ' +
            'class="editable-field pos-einzelpreis text-end" value="0.00" step="0.01" min="0" ' +
            'title="Einzelpreis" style="width:80px; text-align:right;">' +
        '</td>' +
        '<td class="r pos-gesamtpreis-anzeige">0,00 &euro;</td>' +
        '<input type="hidden" name="positionen[' + idx + '][gesamtpreis]" ' +
        'class="pos-gesamtpreis-hidden" value="0.00">' +
        '<td>' +
            '<button type="button" class="btn btn-sm btn-outline-danger btn-zeile-loeschen" ' +
            'title="Position entfernen">&times;</button>' +
        '</td>';

    return zeile;
}

// ====== INITIALISIERUNG beim Seitenladen ======

// Event-Listener für alle bereits vorhandenen Zeilen setzen
document.querySelectorAll('#positionen-body tr').forEach(function (zeile) {
    bindZeileEvents(zeile);
});

// "Position hinzufügen"-Button
document.getElementById('btn-position-hinzufuegen').addEventListener('click', function () {
    // Nächsten Index ermitteln (Anzahl vorhandener Zeilen)
    const anzahl = document.querySelectorAll('#positionen-body tr').length;
    const neueZeile = erstelleNeueZeile(anzahl);

    // Zeile an die Tabelle anhängen und Events binden
    document.getElementById('positionen-body').appendChild(neueZeile);
    bindZeileEvents(neueZeile);

    // Summen neu berechnen (neue Zeile hat Gesamtpreis 0)
    berechneSummen();

    // Fokus auf das Beschreibungsfeld der neuen Zeile setzen
    neueZeile.querySelector('.pos-name')?.focus();
});

// Textareas: automatische Höhe (kein Scrollbar sichtbar)
document.querySelectorAll('.editable-textarea').forEach(function (ta) {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
    ta.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
});
</script>

</x-app-layout>
