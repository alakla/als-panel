<!DOCTYPE html>
{{-- PDF-Template fuer Rechnungen --}}
{{-- Wird von DomPDF gerendert und als PDF gespeichert --}}
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechnung {{ $rechnung->rechnungsnummer }}</title>
    <style>
        /* Grundlegendes Layout fuer das PDF */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Kopfbereich mit Firmenlogo und Rechnungstitel */
        .header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .firma-name {
            font-size: 18pt;
            font-weight: bold;
            color: #0d6efd;
        }

        .firma-info {
            font-size: 9pt;
            color: #666;
        }

        .rechnung-titel {
            font-size: 16pt;
            font-weight: bold;
            text-align: right;
            color: #333;
        }

        /* Adressblock: Absender und Empfaenger nebeneinander */
        .adressen {
            margin-bottom: 25px;
        }

        .absender {
            float: left;
            width: 48%;
        }

        .empfaenger {
            float: right;
            width: 48%;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Rechnungsdetails (Nummer, Datum, Zeitraum) */
        .rechnungs-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 25px;
            font-size: 10pt;
        }

        .rechnungs-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .rechnungs-info td {
            padding: 3px 8px;
        }

        .rechnungs-info .label {
            color: #666;
            width: 40%;
        }

        /* Positionstabelle der Zeiteintraege */
        .positionen {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .positionen th {
            background: #0d6efd;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10pt;
        }

        .positionen th.rechts {
            text-align: right;
        }

        .positionen td {
            padding: 7px 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 10pt;
        }

        .positionen td.rechts {
            text-align: right;
        }

        .positionen tr:nth-child(even) {
            background: #f8f9fa;
        }

        /* Summentabelle: Netto, MwSt, Gesamt */
        .summen {
            float: right;
            width: 45%;
            border-collapse: collapse;
        }

        .summen td {
            padding: 5px 10px;
            font-size: 11pt;
        }

        .summen .label {
            color: #666;
        }

        .summen .gesamt {
            font-weight: bold;
            font-size: 13pt;
            color: #0d6efd;
            border-top: 2px solid #0d6efd;
        }

        /* Bankverbindung und Fussnote */
        .fusszeile {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>
<body>

    {{-- Kopfbereich: Absenderinformationen und Rechnungstitel --}}
    <div class="header clearfix">
        <div style="float:left">
            <div class="firma-name">ALS Personaldienstleistungen GmbH</div>
            <div class="firma-info">
                Musterstrasse 1 &bull; 12345 Musterstadt<br>
                Tel: +49 (0) 123 456789 &bull; info@als-personal.de<br>
                USt-IdNr.: DE123456789
            </div>
        </div>
        <div style="float:right; text-align:right">
            <div class="rechnung-titel">RECHNUNG</div>
            <div style="color:#666; font-size:10pt">{{ $rechnung->rechnungsnummer }}</div>
        </div>
    </div>

    {{-- Empfaengeradresse --}}
    <div class="adressen clearfix">
        <div class="empfaenger">
            <strong>{{ $auftraggeber->firmenname }}</strong><br>
            {{ $auftraggeber->ansprechpartner }}<br>
            {!! nl2br(e($auftraggeber->adresse)) !!}
        </div>
    </div>

    {{-- Rechnungsdetails --}}
    <div class="rechnungs-info">
        <table>
            <tr>
                <td class="label">Rechnungsnummer:</td>
                <td><strong>{{ $rechnung->rechnungsnummer }}</strong></td>
                <td class="label">Rechnungsdatum:</td>
                <td>{{ $rechnung->rechnungsdatum?->format('d.m.Y') }}</td>
            </tr>
            <tr>
                <td class="label">Abrechnungszeitraum:</td>
                <td colspan="3">
                    {{ $rechnung->zeitraum_von->format('d.m.Y') }}
                    bis {{ $rechnung->zeitraum_bis->format('d.m.Y') }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Betreff --}}
    <p>
        <strong>Betreff: Rechnung fuer erbrachte Personaldienstleistungen</strong><br>
        gemaess unserem Vertrag berechnen wir Ihnen fuer den Zeitraum
        {{ $rechnung->zeitraum_von->format('d.m.Y') }} bis {{ $rechnung->zeitraum_bis->format('d.m.Y') }}
        folgende Leistungen:
    </p>

    {{-- Positionstabelle der Zeiteintraege --}}
    <table class="positionen">
        <thead>
            <tr>
                <th>Datum</th>
                <th>Mitarbeiter</th>
                <th>Beschreibung</th>
                <th class="rechts">Stunden</th>
                <th class="rechts">Stundensatz</th>
                <th class="rechts">Betrag</th>
            </tr>
        </thead>
        <tbody>
            @foreach($zeiterfassungen as $ze)
                <tr>
                    <td>{{ $ze->datum->format('d.m.Y') }}</td>
                    <td>{{ $ze->mitarbeiter->user->name }}</td>
                    <td>{{ $ze->beschreibung ?: '–' }}</td>
                    <td class="rechts">{{ number_format($ze->stunden, 2, ',', '.') }}</td>
                    <td class="rechts">{{ number_format($auftraggeber->stundensatz, 2, ',', '.') }} €</td>
                    <td class="rechts">{{ number_format($ze->stunden * $auftraggeber->stundensatz, 2, ',', '.') }} €</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summentabelle --}}
    <div class="clearfix">
        <table class="summen">
            <tr>
                <td class="label">Gesamtstunden:</td>
                <td style="text-align:right">{{ number_format($gesamtstunden, 2, ',', '.') }} Std.</td>
            </tr>
            <tr>
                <td class="label">Nettobetrag:</td>
                <td style="text-align:right">{{ number_format($rechnung->nettobetrag, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td class="label">MwSt. 19%:</td>
                <td style="text-align:right">{{ number_format($rechnung->mwst_betrag, 2, ',', '.') }} €</td>
            </tr>
            <tr class="gesamt">
                <td class="label">Gesamtbetrag:</td>
                <td style="text-align:right">{{ number_format($rechnung->gesamtbetrag, 2, ',', '.') }} €</td>
            </tr>
        </table>
    </div>

    {{-- Zahlungshinweis --}}
    <div style="margin-top: 60px; clear:both;">
        <p>
            Bitte ueberweisen Sie den Gesamtbetrag von
            <strong>{{ number_format($rechnung->gesamtbetrag, 2, ',', '.') }} €</strong>
            innerhalb von 14 Tagen auf folgendes Konto:
        </p>
    </div>

    {{-- Bankverbindung --}}
    <div style="background:#f8f9fa; padding:10px; border-radius:4px; font-size:10pt">
        <strong>Bankverbindung:</strong><br>
        ALS Personaldienstleistungen GmbH &bull;
        IBAN: DE12 3456 7890 1234 5678 90 &bull;
        BIC: MUSTBEBB &bull;
        Musterbank Berlin
    </div>

    {{-- Fusszeile --}}
    <div class="fusszeile">
        <p>
            Verwendungszweck: {{ $rechnung->rechnungsnummer }}<br>
            Diese Rechnung wurde maschinell erstellt und ist ohne Unterschrift gueltig.
        </p>
    </div>

</body>
</html>
