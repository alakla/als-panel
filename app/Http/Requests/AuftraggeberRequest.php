<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * AuftraggeberRequest – Validierung der Auftraggeber-Formulardaten
 *
 * Validiert alle Eingaben beim Anlegen und Bearbeiten eines Auftraggebers.
 * Der Stundensatz wird jetzt pro Taetigkeit verwaltet (nicht mehr hier).
 *
 * Zugriff: Nur Administratoren
 */
class AuftraggeberRequest extends FormRequest
{
    /**
     * Nur Administratoren duerfen Auftraggeberdaten einreichen.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Validierungsregeln fuer Auftraggeberdaten.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Aktuelle Auftraggeber-ID ermitteln (fuer unique-Pruefung beim Bearbeiten)
        $auftraggeberId = $this->route('auftraggeber')?->id;

        return [
            // Firmenname: Pflichtfeld
            'firmenname'      => ['required', 'string', 'max:255'],

            // Ansprechpartner: Pflichtfeld
            'ansprechpartner' => ['required', 'string', 'max:255'],

            // Adresse: Pflichtfeld (vollstaendige Adresse fuer Rechnungen)
            'adresse'         => ['required', 'string', 'max:500'],

            // E-Mail: Pflichtfeld, gueltiges Format
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
