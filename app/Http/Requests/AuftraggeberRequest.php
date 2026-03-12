<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * AuftraggeberRequest – Validierung der Auftraggeber-Formulardaten
 *
 * Validiert alle Eingaben beim Anlegen und Bearbeiten eines Auftraggebers.
 * Der Stundensatz wird jetzt pro Tätigkeit verwaltet (nicht mehr hier).
 *
 * Zugriff: Nur Administratoren
 */
class AuftraggeberRequest extends FormRequest
{
    /**
     * Nur Administratoren dürfen Auftraggeberdaten einreichen.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Validierungsregeln für Auftraggeberdaten.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Aktuelle Auftraggeber-ID ermitteln (für unique-Prüfung beim Bearbeiten)
        $auftraggeberId = $this->route('auftraggeber')?->id;

        return [
            // Firmenname: Pflichtfeld
            'firmenname'      => ['required', 'string', 'max:255'],

            // Ansprechpartner: Pflichtfeld
            'ansprechpartner' => ['required', 'string', 'max:255'],

            // Adresse: Pflichtfeld (vollständige Adresse für Rechnungen)
            'adresse'         => ['required', 'string', 'max:500'],

            // E-Mail: Pflichtfeld, gültiges Format
            'email'           => ['required', 'email', 'max:255'],

            // Telefon: Optional
            'telefon'         => ['nullable', 'string', 'max:50'],

            // Status: Aktiv oder Inaktiv
            'is_active'       => ['boolean'],
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
            'firmenname.required'      => 'Der Firmenname ist ein Pflichtfeld.',
            'ansprechpartner.required' => 'Der Ansprechpartner ist ein Pflichtfeld.',
            'adresse.required'         => 'Die Adresse ist ein Pflichtfeld.',
            'email.required'           => 'Die E-Mail-Adresse ist ein Pflichtfeld.',
            'email.email'              => 'Bitte eine gueltige E-Mail-Adresse eingeben.',
        ];
    }
}
