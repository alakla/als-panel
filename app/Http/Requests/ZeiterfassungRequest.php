<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ZeiterfassungRequest – Validierungsregeln für Zeiteinträge
 *
 * Validiert die Eingaben des Mitarbeitenden beim Erfassen oder Bearbeiten
 * eines täglichen Arbeitszeiteintrags.
 *
 * Zugriff: Mitarbeitende (eigene Einträge)
 */
class ZeiterfassungRequest extends FormRequest
{
    /**
     * Berechtigung: Alle authentifizierten Mitarbeitenden dürfen Zeiteinträge verwalten.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validierungsregeln für das Zeiterfassungsformular.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Auftraggeber muss ausgewählt und in der DB vorhanden sein
            'auftraggeber_id' => ['required', 'exists:auftraggeber,id'],

            // Datum muss ein gültiges Datum sein (nicht in der Zukunft)
            'datum'           => ['required', 'date', 'before_or_equal:today'],

            // Stunden: Dezimalzahl zwischen 0.5 und 12
            'stunden'         => ['required', 'numeric', 'min:0.5', 'max:12'],

            // Tätigkeitsbeschreibung: optional, max. 500 Zeichen
            'beschreibung'    => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Deutsche Fehlermeldungen für die Validierung.
     */
    public function messages(): array
    {
        return [
            'auftraggeber_id.required' => 'Bitte einen Auftraggeber auswählen.',
            'auftraggeber_id.exists'   => 'Der ausgewählte Auftraggeber ist ungültig.',
            'datum.required'           => 'Das Datum ist erforderlich.',
            'datum.date'               => 'Das Datum muss ein gültiges Datum sein.',
            'datum.before_or_equal'    => 'Das Datum darf nicht in der Zukunft liegen.',
            'stunden.required'         => 'Die Stundenanzahl ist erforderlich.',
            'stunden.numeric'          => 'Die Stundenanzahl muss eine Zahl sein.',
            'stunden.min'              => 'Es müssen mindestens 0,5 Stunden eingetragen werden.',
            'stunden.max'              => 'Es können maximal 12 Stunden pro Tag eingetragen werden.',
            'beschreibung.max'         => 'Die Beschreibung darf maximal 500 Zeichen lang sein.',
        ];
    }
}
