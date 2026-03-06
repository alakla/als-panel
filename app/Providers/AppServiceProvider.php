<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider – Zentraler Anwendungs-Service-Provider
 *
 * Dieser Provider wird beim Start der Anwendung automatisch geladen.
 * Hier koennen anwendungsweite Dienste registriert und konfiguriert werden,
 * z.B. eigene Makros, View-Composer, Datenbankregeln oder globale Einstellungen.
 *
 * Fuer das ALS Panel wird dieser Provider bei Bedarf erweitert,
 * z.B. fuer benutzerdefinierte Validierungsregeln oder PDF-Konfiguration.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registriert anwendungsweite Dienste im Service-Container.
     *
     * Hier werden Bindungen, Singletons oder Factory-Callbacks
     * fuer eigene Klassen und Interfaces registriert.
     */
    public function register(): void
    {
        // Zukuenftige Service-Registrierungen hier einfuegen
    }

    /**
     * Wird nach der Registrierung aller Services ausgefuehrt.
     *
     * Hier koennen View-Composer, Event-Listener, Routen-Makros
     * oder globale Middleware-Konfigurationen eingerichtet werden.
     */
    public function boot(): void
    {
        // Zukuenftige Boot-Konfigurationen hier einfuegen
    }
}
