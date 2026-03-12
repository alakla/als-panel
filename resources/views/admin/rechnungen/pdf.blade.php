<!DOCTYPE html>
{{--
    PDF-Template – ALS Dienstleistungen
    =====================================
    Dieses Template wird von DomPDF gerendert und als PDF gespeichert.
    Es verwendet KEIN Bootstrap – nur inline CSS und einen style-Block,
    da DomPDF keine externe CSS-Dateien laden kann.

    Übergebene Variablen (alle kommen aus RechnungController::generierePdf()):
      $rechnung        - Rechnung-Model (für Nummer, Datum, Zeitraum)
      $positionen      - array, direkt iterierbar (vom Admin bearbeitet)
      $absender        - string (Absenderzeile unter der Trennlinie)
      $empfaenger_name - string (Firmenname des Empfängers)
      $adresse_zeilen  - array of strings (je eine Zeile der Empfängeradresse)
      $anrede          - string
      $einleitung      - string
      $zahlungstext    - string (kann mehrere Sätze enthalten)
      $gruss           - string (kann \n-Zeilenumbrüche enthalten)
      $footer_firma    - string (kann \n-Zeilenumbrüche enthalten)
      $footer_kontakt  - string (kann \n-Zeilenumbrüche enthalten)
      $footer_bank     - string (kann \n-Zeilenumbrüche enthalten)
--}}
@php
    /*
     * Logo als Base64 einbetten.
     * DomPDF kann keine externen URLs laden; daher wird das Bild direkt
     * als Base64-Datenstream im src-Attribut eingebettet.
     */
    $logoPfad   = public_path('logo.png');
    $logoBase64 = file_exists($logoPfad)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPfad))
        : '';

    /*
     * Zeitraum-Datumsformatierung für die Positionstabelle.
     * $rechnung->zeitraum_von und zeitraum_bis werden als Carbon-Objekte gecastet.
     * Format: TT.MM.JJ (kurz, passend zur Referenz-PDF)
     */
    $zeitraumVon = $rechnung->zeitraum_von->format('d.m.y');
    $zeitraumBis = $rechnung->zeitraum_bis->format('d.m.y');

    /*
     * Summen aus den (eventuell vom Admin bearbeiteten) Positionen neu berechnen.
     * Wir berechnen hier aus den tatsächlichen Positionsdaten, nicht aus der DB,
     * damit bearbeitete Preise korrekt in der PDF erscheinen.
     */
    $nettoBetragPdf  = array_sum(array_column($positionen, 'gesamtpreis'));
    $mwstBetragPdf   = $nettoBetragPdf * 0.19;
    $gesamtBetragPdf = $nettoBetragPdf + $mwstBetragPdf;
@endphp
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Rechnung {{ $rechnung->rechnungsnummer }}</title>
    <style>
        /* ===== RESET ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* ===== SEITE ===== */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5pt;
            color: #222222;
            /* Seitenränder: oben/unten groß für Header und Footer-Bereich */
            margin: 20mm 15mm 55mm 15mm;
        }

        /* ===== HEADER: "RECHNUNG" + LOGO ===== */
        table.header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .rechnung-heading {
            font-size: 32pt;
            font-weight: normal;
            color: #c8c8c8;       /* Hellgrau */
            letter-spacing: 3px;
            line-height: 1;
            vertical-align: bottom;
        }
        .logo-zelle {
            text-align: right;
            vertical-align: bottom;
        }
        .logo-zelle img {
            width: 115px;
            height: auto;
        }

        /* ===== BLAUE TRENNLINIE ===== */
        hr.linie {
            border: none;
            border-top: 2px solid #0d6efd;
            margin: 6px 0 30px 0;
        }

        /* ===== ABSENDER-MINITEXT (blau, klein) ===== */
        .absender {
            font-size: 7.5pt;
            color: #0d6efd;
            margin-bottom: 16px;
        }

        /* ===== EMPFAENGER-ADRESSBLOCK ===== */
        .empfaenger {
            font-weight: bold;
            font-size: 10.5pt;
            line-height: 2;
            margin-bottom: 32px;
        }

        /* ===== RECHNUNGS-INFO (Nummer + Datum) ===== */
        table.rech-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .rech-nr {
            font-size: 13pt;
            color: #0d6efd;
        }
        .rech-nr strong {
            font-weight: bold;
        }
        .rech-datum {
            font-size: 10.5pt;
            text-align: right;
            vertical-align: middle;
        }

        /* ===== ANREDE + EINLEITUNG ===== */
        p.anrede     { margin: 14px 0 4px 0;  font-size: 9.5pt; }
        p.einleitung { margin: 0 0 14px 0;    font-size: 9.5pt; }

        /* ===== POSITIONEN-TABELLE ===== */
        table.pos {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 0;
        }
        table.pos thead tr th {
            border-top: 2px solid #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding: 6px 7px;
            font-weight: bold;
            text-align: left;
            /* rgba(13,110,253,0.18) auf weißem Hintergrund ≈ #d3e5ff */
            background-color: #d3e5ff;
        }
        table.pos thead tr th.r { text-align: right; }

        table.pos tbody tr td {
            padding: 6px 7px;
            border-bottom: 1px solid #dddddd;
        }
        table.pos tbody tr td.r { text-align: right; }

        /* Zebrastreifen: gerade/ungerade Zeilen */
        table.pos tbody tr.ungerade td { background-color: #ffffff; }
        table.pos tbody tr.gerade   td { background-color: #f5f7fa; }

        /* Abschlusslinie der Tabelle */
        table.pos tfoot tr td {
            border-top: 1.5px solid #888;
            padding: 0;
            height: 1px;
        }

        /* ===== SUMMENBLOCK (volle Breite, Platzhalter links) ===== */
        table.summen {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-top: 2px;
        }
        table.summen tr td {
            padding: 4px 8px;
        }
        table.summen tr td.leer     { width: 55%; }
        table.summen tr td.lab      { color: #555555; }
        table.summen tr td.val      { text-align: right; font-weight: bold; width: 120px; }
        table.summen tr td.val-normal { text-align: right; width: 120px; }
        table.summen tr.gesamt td {
            border-top: 2px solid #0d6efd;
            background-color: #d3e5ff;
            font-weight: bold;
            font-size: 10.5pt;
            padding-top: 6px;
        }
        table.summen tr.gesamt td.val-gesamt { text-align: right; width: 120px; }

        /* ===== ZAHLUNGSTEXT + GRUSS ===== */
        .zahlungstext {
            font-size: 9.5pt;
            line-height: 1.6;
            margin: 28px 0 20px 0;
        }
        .gruss {
            font-size: 9.5pt;
            line-height: 1.9;
        }

        /* ===== FOOTER: fixiert am unteren Seitenrand ===== */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 7px 15mm 8px 15mm;
            bottom: 10mm;
            border-top: 2px solid #0d6efd;
            background-color: #ffffff;
            font-size: 7.5pt;
            color: #444444;
        }
        table.footer-cols {
            width: 100%;
            border-collapse: collapse;
        }
        table.footer-cols td {
            vertical-align: top;
            width: 33%;
            padding-right: 10px;
            line-height: 1.6;
        }
        .footer-head {
            font-weight: bold;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

{{-- ====== KOPFZEILE: "RECHNUNG" + LOGO ====== --}}
<table class="header">
    <tr>
        <td class="rechnung-heading" style="width:60%">RECHNUNG</td>
        <td class="logo-zelle" style="width:40%">
            @if($logoBase64)
                {{-- Logo als Base64-Datenstream (DomPDF-kompatibel) --}}
                <img src="{{ $logoBase64 }}" alt="ALS Dienstleistungen">
            @else
                {{-- Fallback: Textrepräsentation des Logos --}}
                <span style="font-size:22pt; font-weight:bold; color:#0d6efd; letter-spacing:3px;">ALS</span><br>
                <span style="font-size:7pt; color:#0d6efd; letter-spacing:2px;">DIENSTLEISTUNGEN</span>
            @endif
        </td>
    </tr>
</table>

{{-- Blaue Trennlinie --}}
<hr class="linie">

{{-- ====== ABSENDERZEILE (blau, klein) ====== --}}
<div class="absender">
    {{-- Absender direkt aus dem vom Admin bearbeiteten Formularfeld --}}
    {{ $absender }}
</div>

{{-- ====== EMPFÄNGER-ADRESSBLOCK ====== --}}
<div class="empfaenger">
    {{-- Firmenname des Empfängers (vom Admin editierbar) --}}
    {{ $empfaenger_name }}<br>
    {{-- Jede Adresszeile wird mit <br> getrennt ausgegeben --}}
    @foreach($adresse_zeilen as $zeile)
        {{ $zeile }}<br>
    @endforeach
</div>

{{-- ====== RECHNUNGS-NR + DATUM ====== --}}
<table class="rech-info">
    <tr>
        <td style="vertical-align:middle;">
            {{-- Rechnungsnummer aus der Datenbank (nicht editierbar, automatisch vergeben) --}}
            <span class="rech-nr">Rechnung Nr. <strong>{{ $rechnung->rechnungsnummer }}</strong></span>
        </td>
        <td class="rech-datum">
            {{-- Rechnungsdatum: vom Admin in der Vorschau editierbar --}}
            Datum: {{ $rechnung->rechnungsdatum?->format('d.m.Y') }}
        </td>
    </tr>
</table>

{{-- ====== ANREDE + EINLEITUNGSTEXT ====== --}}
<p class="anrede">{{ $anrede }}</p>
<p class="einleitung">{{ $einleitung }}</p>

{{-- ====== POSITIONEN-TABELLE ====== --}}
<table class="pos">
    <thead>
        <tr>
            <th style="width:25px">Pos</th>
            <th>Beschreibung</th>
            <th style="width:135px; white-space:nowrap;">Zeitraum</th>
            <th style="width:52px; text-align:center;">Menge</th>
            <th style="width:58px">Einheit</th>
            <th class="r" style="width:78px">Einzelpreis</th>
            <th class="r" style="width:78px">Gesamtpreis</th>
        </tr>
    </thead>
    <tbody>
        {{--
            $positionen ist ein einfaches PHP-Array (direkt iterierbar).
            $loop->even ergibt true für gerade Indices (0, 2, 4, ...) -> Klasse 'gerade'
            Ungerade Indices (1, 3, 5, ...) -> Klasse 'ungerade' (weißer Hintergrund)
        --}}
        @foreach($positionen as $i => $pos)
            <tr class="{{ $loop->even ? 'ungerade' : 'gerade' }}">
                {{-- Positionsnummer (1-basiert) --}}
                <td>{{ $i + 1 }}</td>
                {{-- Beschreibung der Leistung --}}
                <td>{{ $pos['name'] }}</td>
                {{-- Zeitraum der Leistung (editierbar aus der Vorschau, oder Standard-Zeitraum) --}}
                <td style="white-space:nowrap;">{{ $pos['zeitraum'] ?? ($zeitraumVon . ' – ' . $zeitraumBis) }}</td>
                {{-- Menge: bei Pauschal immer "1" anzeigen --}}
                <td style="text-align:center; white-space:nowrap;">
                    @if($pos['einheit'] === 'Pauschal')
                        1
                    @else
                        {{ number_format($pos['menge'], 2, ',', '.') }}
                    @endif
                </td>
                {{-- Einheit: "Pauschal" oder "Std." --}}
                <td>{{ $pos['einheit'] }}</td>
                {{-- Einzelpreis formatiert mit Komma als Dezimaltrennzeichen --}}
                <td class="r">{{ number_format($pos['einzelpreis'], 2, ',', '.') }} &euro;</td>
                {{-- Gesamtpreis: Menge × Einzelpreis (oder Pauschal = Einzelpreis) --}}
                <td class="r">{{ number_format($pos['gesamtpreis'], 2, ',', '.') }} &euro;</td>
            </tr>
        @endforeach
    </tbody>
    {{-- Abschließende Linie unter der Tabelle --}}
    <tfoot>
        <tr><td colspan="7"></td></tr>
    </tfoot>
</table>

{{-- ====== SUMMENBLOCK (volle Breite, Platzhalter links) ====== --}}
{{-- Gleiche Struktur wie Vorschau: 3 Spalten, linke 55% leer --}}
<table class="summen">
    {{-- Leerzeile als Abstand vor dem Summenblock --}}
    <tr>
        <td class="leer"></td>
        <td style="padding-top:10px;"></td>
        <td style="padding-top:10px;"></td>
    </tr>
    <tr>
        <td class="leer"></td>
        <td class="lab">Nettopreis</td>
        <td class="val">{{ number_format($nettoBetragPdf, 2, ',', '.') }} &euro;</td>
    </tr>
    <tr>
        <td class="leer"></td>
        <td class="lab">Zzgl. 19% MwSt.</td>
        <td class="val-normal">{{ number_format($mwstBetragPdf, 2, ',', '.') }} &euro;</td>
    </tr>
    {{-- Rechnungsbetrag: volle Breite mit blauer Hintergrundfarbe --}}
    <tr class="gesamt">
        <td class="leer"></td>
        <td>Rechnungsbetrag</td>
        <td class="val-gesamt">{{ number_format($gesamtBetragPdf, 2, ',', '.') }} &euro;</td>
    </tr>
</table>

{{-- ====== ZAHLUNGSTEXT ====== --}}
<div class="zahlungstext">
    {{--
        nl2br: Zeilenumbrüche (\n) in <br>-Tags umwandeln.
        e():   HTML-Sonderzeichen escapen (XSS-Schutz).
        !!:    Ergebnis nicht doppelt escapen (da nl2br HTML zurückgibt).
    --}}
    {{-- "Rechnungsnummer" wird automatisch fett gedruckt --}}
    {!! str_replace('Rechnungsnummer', '<strong>Rechnungsnummer</strong>', nl2br(e($zahlungstext))) !!}
</div>

{{-- ====== GRUSS ====== --}}
<div class="gruss">
    {{-- nl2br für mehrzeiligen Grußtext (z.B. "Mit freundlichen Grüßen\nALS Dienstleistungen") --}}
    {!! nl2br(e($gruss)) !!}
</div>

{{-- ====== FOOTER (fixiert am unteren Seitenrand) ====== --}}
<div class="footer">
    <table class="footer-cols">
        <tr>
            {{-- Spalte 1: Firmenangaben --}}
            <td>
                <div class="footer-head">ALS Dienstleistungen</div>
                {!! nl2br(e($footer_firma)) !!}
            </td>
            {{-- Spalte 2: Kontaktdaten --}}
            <td>
                <div class="footer-head">Kontakt</div>
                {!! nl2br(e($footer_kontakt)) !!}
            </td>
            {{-- Spalte 3: Bankverbindung (etwas nach rechts verschoben) --}}
            <td style="padding-left: 24px;">
                <div class="footer-head">Bankverbindung</div>
                {!! nl2br(e($footer_bank)) !!}
            </td>
        </tr>
    </table>
</div>

</body>
</html>
