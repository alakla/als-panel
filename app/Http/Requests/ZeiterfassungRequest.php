<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ZeiterfassungRequest – Validierungsregeln fuer Zeiteintraege
 *
 * Validiert die Eingaben des Mitarbeitenden beim Erfassen oder Bearbeiten
 * eines taeglichen Arbeitszeiteintrags.
 *
 * Zugriff: Mitarbeitende (eigene Eintraege)
 */
class ZeiterfassungRequest extends FormRequest
{
    /**
     * Berechtigung: Alle authentifizierten Mitarbeitenden duerfen Zeiteintraege verwalten.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validierungsregeln fuer das Zeiterfassungsformular.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Auftraggeber muss ausgewaehlt und in der DB vorhanden sein
            'auftraggeber_id' => ['required', 'exists:auftraggeber,id'],

            // Datum muss ein gueltiges Datum sein (nicht in der Zukunft)
            'datum'           => ['required', 'date', 'before_or_equal:today'],

            // Stunden: Dezimalzahl zwischen 0.5 und 24
            'stunden'         => ['required', 'numeric', 'min:0.5', 'max:24'],

            // Taetigkeitsbeschreibung: optional, max. 500 Zeichen
            'beschreibung'    => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Deutsche Fehlermeldungen fuer die Validierung.
     */
    public function messages(): array
    {
        return [
            'auftraggeber_id.required' => 'Bitte einen Auftraggeber auswaehlen.',
            'auftraggeber_id.exists'   => 'Der ausgewaehlte Auftraggeber ist ungueltig.',
            'datum.required'           => 'Das Datum ist erforderlich.',
            'datum.date'               => 'Das Datum muss ein gueltiges Datum sein.',
            'datum.before_or_equal'    => 'Das Datum darf nicht in der Zukunft liegen.',
            'stunden.required'         => 'Die Stundenanzahl ist erforderlich.',
            'stunden.numeric'          => 'Die Stundenanzahl muss eine Zahl sein.',
            'stunden.min'              => 'Es muessen mindestens 0,5 Stunden eingetragen werden.',
            'stunden.max'              => 'Es koennen maximal 24 Stunden pro Tag eingetragen werden.',
            'beschreibung.max'         => 'Die Beschreibung darf maximal 500 Zeichen lang sein.',
        ];
    }
}
