<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * ProfileController – Verwaltung des Benutzerprofils
 *
 * Ermoeglicht jedem angemeldeten Benutzer (Admin und Mitarbeiter)
 * die Verwaltung der eigenen Profildaten:
 * - Profilansicht und -bearbeitung (Name, E-Mail)
 * - Loeschung des eigenen Kontos
 *
 * Zugriff: Alle authentifizierten Benutzer (Middleware: auth)
 */
class ProfileController extends Controller
{
    /**
     * Zeigt das Profil-Bearbeitungsformular des angemeldeten Benutzers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request): View
    {
        // Aktuellen Benutzer an die Profilansicht uebergeben
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Aktualisiert die Profildaten des angemeldeten Benutzers.
     *
     * Bei einer E-Mail-Aenderung wird die E-Mail-Verifizierung zurueckgesetzt,
     * da die neue Adresse noch nicht bestaetigt ist.
     *
     * @param  \App\Http\Requests\ProfileUpdateRequest  $request  Validierter Request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Validierte Felder in das Benutzerobjekt schreiben
        $request->user()->fill($request->validated());

        // Wenn die E-Mail geaendert wurde, Verifizierungsstatus zuruecksetzen
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Aenderungen in der Datenbank speichern
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Loescht das Konto des angemeldeten Benutzers.
     *
     * Sicherheitspruefung: Das aktuelle Passwort muss bestaetigt werden.
     * Nach der Loeschung wird die Session invalidiert und der Benutzer
     * zur Startseite weitergeleitet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Passwortbestaetigung vor der Kontoloeeschung verlangen
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Benutzer abmelden, bevor das Konto geloescht wird
        Auth::logout();

        // Benutzerkonto aus der Datenbank loeschen
        $user->delete();

        // Session bereinigen und CSRF-Token erneuern
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
