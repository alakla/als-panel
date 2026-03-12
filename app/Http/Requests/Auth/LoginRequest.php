<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * LoginRequest – Validierung und Authentifizierung der Anmeldedaten
 *
 * Dieser FormRequest ist verantwortlich für:
 * - Validierung der eingegebenen E-Mail-Adresse und des Passworts
 * - Authentifizierung des Benutzers gegen die Datenbank
 * - Schutz gegen Brute-Force-Angriffe (Rate Limiting: max. 5 Versuche)
 *
 * Sicherheitshinweis: Nach 5 fehlgeschlagenen Versuchen wird das Login
 * für eine bestimmte Zeit gesperrt (Lockout).
 */
class LoginRequest extends FormRequest
{
    /**
     * Bestimmt, ob der Benutzer berechtigt ist, diese Anfrage zu stellen.
     * Da die Login-Seite für alle zugänglich ist, wird immer true zurückgegeben.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Gibt die Validierungsregeln für die Anmeldedaten zurück.
     *
     * - email: Pflichtfeld, muss gültiges E-Mail-Format haben
     * - password: Pflichtfeld, muss eine Zeichenkette sein
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Authentifiziert den Benutzer anhand der eingegebenen Zugangsdaten.
     *
     * Ablauf:
     * 1. Prüfen ob Rate-Limit erreicht wurde (Lockout-Schutz)
     * 2. Anmeldeversuch mit E-Mail, Passwort und "Angemeldet bleiben"-Option
     * 3. Bei Misserfolg: Rate-Limiter erhoehen und Fehler ausgeben
     * 4. Bei Erfolg: Rate-Limiter zuruecksetzen
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        // Sicherstellen, dass das Rate-Limit nicht überschritten wurde
        $this->ensureIsNotRateLimited();

        // Authentifizierungsversuch mit den eingegebenen Zugangsdaten
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Fehlversuch beim Rate-Limiter registrieren
            RateLimiter::hit($this->throttleKey());

            // Fehlermeldung ausgeben (bewusst unspezifisch aus Sicherheitsgruenden)
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Erfolgreiche Anmeldung: Rate-Limiter zurücksetzen
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Prüft, ob das Rate-Limit für diesen Login-Versuch erreicht wurde.
     *
     * Erlaubt maximal 5 Fehlversuche pro E-Mail-Adresse und IP-Adresse.
     * Bei Überschreitung wird ein Lockout-Event ausgelöst und eine
     * Fehlermeldung mit der Wartezeit zurückgegeben.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // Prüfen ob weniger als 5 Versuche gemacht wurden
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // Lockout-Event auslösen (für Logging/Benachrichtigungen)
        event(new Lockout($this));

        // Verbleibende Wartezeit in Sekunden ermitteln
        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Fehlermeldung mit Wartezeit ausgeben
        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Erstellt einen eindeutigen Schlüssel für das Rate-Limiting.
     *
     * Der Schlüssel besteht aus E-Mail-Adresse und IP-Adresse,
     * um Angriffe von verschiedenen Konten von einer IP zu erkennen.
     *
     * @return string  Eindeutiger Rate-Limit-Schlüssel
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
