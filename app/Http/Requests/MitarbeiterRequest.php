<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * MitarbeiterRequest – Validierung der Mitarbeiter-Formulardaten
 *
 * Dieser FormRequest validiert alle Eingaben beim Anlegen
 * und Bearbeiten eines Mitarbeitenden.
 *
 * Beim Bearbeiten (PUT/PATCH) wird die E-Mail-Eindeutigkeit
 * dynamisch angepasst, um den aktuellen Datensatz auszuschliessen.
 *
 * Zugriff: Nur Administratoren (gesichert durch Route-Middleware)
 */
class MitarbeiterRequest extends FormRequest
{
    /**
     * Nur Administratoren dürfen Mitarbeiterdaten einreichen.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Validierungsregeln für Mitarbeiterdaten.
     *
     * Beim Erstellen (POST): E-Mail muss eindeutig in der users-Tabelle sein.
     * Beim Bearbeiten (PUT/PATCH): E-Mail darf die eigene sein (unique ignoriert aktuellen User).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Benutzer-ID aus der Route ermitteln (beim Bearbeiten vorhanden)
        $userId = optional($this->route('mitarbeiter'))->user_id;

        return [
            // Name: Pflichtfeld, max. 255 Zeichen
            'name'             => ['required', 'string', 'max:255'],

            // E-Mail: Pflichtfeld, gültiges Format, eindeutig (ausser beim eigenen Datensatz)
            'email'            => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],

            // Passwort: Nur beim Erstellen Pflichtfeld, min. 8 Zeichen
            'password'         => [$this->isMethod('POST') ? 'required' : 'nullable', 'string', 'min:8'],

            // Personalnummer: Pflichtfeld, eindeutig in der mitarbeiter-Tabelle (kein Duplikat möglich)
            'personalnummer'   => ['required', 'string', 'max:50',
                Rule::unique('mitarbeiter', 'personalnummer')->ignore($this->route('mitarbeiter'))
            ],

            // Telefonnummer: optional
            'telefon'          => ['nullable', 'string', 'max:50'],

            // Einstellungsdatum: Pflichtfeld, gültiges Datum
            'einstellungsdatum' => ['required', 'date'],

            // Stundenlohn: Pflichtfeld, numerisch, mindestens 0
            'stundenlohn'      => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Benutzerfreundliche Fehlermeldungen auf Deutsch.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'              => 'Der Name ist ein Pflichtfeld.',
            'email.required'             => 'Die E-Mail-Adresse ist ein Pflichtfeld.',
            'email.email'                => 'Bitte eine gültige E-Mail-Adresse eingeben.',
            'email.unique'               => 'Diese E-Mail-Adresse ist bereits vergeben.',
            'password.required'          => 'Das Passwort ist ein Pflichtfeld.',
            'password.min'               => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
            'personalnummer.required'    => 'Die Personalnummer ist ein Pflichtfeld.',
            'personalnummer.unique'      => 'Diese Personalnummer ist bereits vergeben.',
            'einstellungsdatum.required' => 'Das Einstellungsdatum ist ein Pflichtfeld.',
            'einstellungsdatum.date'     => 'Bitte ein gültiges Datum eingeben.',
            'stundenlohn.required'       => 'Der Stundenlohn ist ein Pflichtfeld.',
            'stundenlohn.numeric'        => 'Der Stundenlohn muss eine Zahl sein.',
            'stundenlohn.min'            => 'Der Stundenlohn darf nicht negativ sein.',
        ];
    }
}
