<?php

/**
 * Migration: Status-Enum der Auftraege-Tabelle erweitern
 *
 * Fuegt die Werte 'freigegeben' und 'abgelehnt' zum Status-Enum hinzu,
 * damit der Admin-Freigabe-Status auch im Auftrag sichtbar ist.
 *
 * Neuer Status-Ablauf:
 *   gesendet    -> Admin hat den Auftrag versendet (Mitarbeitender sieht: Ausstehend)
 *   bestaetigt  -> Mitarbeitender hat bestaetigt (Admin sieht: Offen)
 *   freigegeben -> Admin hat den Zeiteintrag freigegeben (beide sehen: Freigegeben)
 *   abgelehnt   -> Admin hat den Zeiteintrag abgelehnt (beide sehen: Abgelehnt)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Enum-Spalte um neue Werte erweitern
     */
    public function up(): void
    {
        // MySQL erfordert ALTER TABLE fuer Enum-Aenderungen
        DB::statement("
            ALTER TABLE auftraege
            MODIFY COLUMN status
            ENUM('gesendet','bestaetigt','freigegeben','abgelehnt')
            NOT NULL DEFAULT 'gesendet'
        ");
    }

    /**
     * Enum-Spalte auf urspruengliche Werte zuruecksetzen (Rollback)
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE auftraege
            MODIFY COLUMN status
            ENUM('gesendet','bestaetigt')
            NOT NULL DEFAULT 'gesendet'
        ");
    }
};
