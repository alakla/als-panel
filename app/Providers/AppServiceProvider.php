<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider – Zentraler Anwendungs-Service-Provider
 *
 * Dieser Provider wird beim Start der Anwendung automatisch geladen.
 * Hier können anwendungsweite Dienste registriert und konfiguriert werden,
 * z.B. eigene Makros, View-Composer, Datenbankregeln oder globale Einstellungen.
 *
 * Für das ALS Panel wird dieser Provider bei Bedarf erweitert,
 * z.B. für benutzerdefinierte Validierungsregeln oder PDF-Konfiguration.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registriert anwendungsweite Dienste im Service-Container.
     *
     * Hier werden Bindungen, Singletons oder Factory-Callbacks
     * für eigene Klassen und Interfaces registriert.
     */
    public function register(): void
    {
        // Zukünftige Service-Registrierungen hier einfügen
    }

    /**
     * Wird nach der Registrierung aller Services ausgeführt.
     *
     * Hier können View-Composer, Event-Listener, Routen-Makros
     * oder globale Middleware-Konfigurationen eingerichtet werden.
     */
    public function boot(): void
    {
        // Zukünftige Boot-Konfigurationen hier einfügen
    }
}
