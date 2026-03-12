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
 * Ermöglicht jedem angemeldeten Benutzer (Admin und Mitarbeiter)
 * die Verwaltung der eigenen Profildaten:
 * - Profilansicht und -bearbeitung (Name, E-Mail)
 * - Löschung des eigenen Kontos
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
        // Aktuellen Benutzer an die Profilansicht übergeben
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Aktualisiert die Profildaten des angemeldeten Benutzers.
     *
     * Bei einer E-Mail-Änderung wird die E-Mail-Verifizierung zurückgesetzt,
     * da die neue Adresse noch nicht bestätigt ist.
     *
     * @param  \App\Http\Requests\ProfileUpdateRequest  $request  Validierter Request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Validierte Felder in das Benutzerobjekt schreiben (nur User-Felder)
        $request->user()->fill($request->only(['name', 'email']));

        // Wenn die E-Mail geändert wurde, Verifizierungsstatus zurücksetzen
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Änderungen in der Datenbank speichern
        $request->user()->save();

        // Telefonnummer im Mitarbeiter-Datensatz speichern (falls vorhanden)
        if ($request->user()->mitarbeiter) {
            $request->user()->mitarbeiter->update(['telefon' => $request->input('telefon')]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Löscht das Konto des angemeldeten Benutzers.
     *
     * Sicherheitsprüfung: Das aktuelle Passwort muss bestätigt werden.
     * Nach der Löschung wird die Session invalidiert und der Benutzer
     * zur Startseite weitergeleitet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Passwortbestätigung vor der Kontolöschung verlangen
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Benutzer abmelden, bevor das Konto gelöscht wird
        Auth::logout();

        // Benutzerkonto aus der Datenbank löschen
        $user->delete();

        // Session bereinigen und CSRF-Token erneuern
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
