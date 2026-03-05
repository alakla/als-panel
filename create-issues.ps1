# GitHub Issues Script - ALS Panel Projektverwaltungssystem
# Sprache: Deutsch | Projekt: Webbasiertes Verwaltungssystem fuer Personaldienstleistung

Write-Host "Erstelle Labels..." -ForegroundColor Cyan

gh label create "Epic: Setup" --color "#0075ca" --description "Projekteinrichtung und Entwicklungsumgebung" 2>$null
gh label create "Epic: Datenbank" --color "#e4e669" --description "Datenbankdesign und Migrationen" 2>$null
gh label create "Epic: Authentifizierung" --color "#d93f0b" --description "Login, Rollen und Zugriffskontrolle" 2>$null
gh label create "Epic: Mitarbeiterverwaltung" --color "#0052cc" --description "CRUD fuer Mitarbeitende" 2>$null
gh label create "Epic: Auftraggeberverwaltung" --color "#5319e7" --description "CRUD fuer Auftraggeber" 2>$null
gh label create "Epic: Zeiterfassung" --color "#006b75" --description "Zeiterfassung und Freigabe" 2>$null
gh label create "Epic: Abrechnung" --color "#e11d48" --description "Abrechnungslogik und PDF-Rechnungen" 2>$null
gh label create "Epic: UI/UX" --color "#f97316" --description "Benutzeroberflaechenentwicklung" 2>$null
gh label create "Epic: Testing" --color "#84cc16" --description "Testfaelle und Qualitaetssicherung" 2>$null
gh label create "Epic: Dokumentation" --color "#6b7280" --description "Technische und fachliche Dokumentation" 2>$null
gh label create "erledigt" --color "#22c55e" --description "Bereits abgeschlossen" 2>$null

Write-Host "Labels erstellt." -ForegroundColor Green
Write-Host ""
Write-Host "Erstelle Issues..." -ForegroundColor Cyan

# ============================================================
# EPIC 1: PROJEKTEINRICHTUNG (bereits erledigt)
# ============================================================

gh issue create `
  --title "[Setup] Entwicklungsumgebung einrichten (PHP, Composer, Git, GitHub CLI)" `
  --label "Epic: Setup,erledigt" `
  --body "## User Story
Als Entwickler moechte ich eine vollstaendige lokale Entwicklungsumgebung einrichten, damit ich das Projekt professionell entwickeln und versionieren kann.

## Hintergrund
Vor der eigentlichen Entwicklung muss die gesamte Toolchain installiert und konfiguriert werden. Dies umfasst alle notwendigen Laufzeitumgebungen, Paketverwaltungssysteme und Versionierungstools.

## Akzeptanzkriterien
- [x] PHP 8.4 installiert und im PATH verfuegbar (\`php --version\`)
- [x] Composer 2.x installiert und verfuegbar (\`composer --version\`)
- [x] Git installiert und konfiguriert (\`git --version\`)
- [x] Node.js und npm installiert (\`node --version\`, \`npm --version\`)
- [x] GitHub CLI (gh) installiert und authentifiziert (\`gh auth status\`)
- [x] Laravel Herd als lokale PHP-Verwaltung installiert

## Technische Details
- **PHP**: 8.4.16 via Laravel Herd (C:\Users\Alakla\.config\herd\bin\php84)
- **Composer**: 2.9.5
- **Git**: 2.45.1 (Windows)
- **Node.js**: v22.16.0
- **npm**: 10.9.2
- **GitHub CLI**: 2.87.3
- **Betriebssystem**: Windows 10 Enterprise

## Zeitschaetzung
2 Stunden (Setup und Konfiguration)

## Status
Abgeschlossen"

# ============================================================

gh issue create `
  --title "[Setup] Laravel-Projekt erstellen und GitHub-Repository einrichten" `
  --label "Epic: Setup,erledigt" `
  --body "## User Story
Als Entwickler moechte ich ein neues Laravel-Projekt erstellen und mit einem GitHub-Repository verbinden, damit der Quellcode versioniert und nachvollziehbar gespeichert wird.

## Hintergrund
Das Laravel-Framework bildet die technische Grundlage des gesamten Systems. Das GitHub-Repository dient als zentrale Versionsverwaltung und ermoeglicht eine strukturierte Entwicklung.

## Akzeptanzkriterien
- [x] Laravel 11 Projekt erstellt (\`composer create-project laravel/laravel als-panel\`)
- [x] Git-Repository lokal initialisiert (\`git init\`)
- [x] Erster Commit mit Basisprojekt erstellt
- [x] GitHub-Repository 'als-panel' erstellt (public)
- [x] Lokales Repository mit GitHub verbunden (\`gh repo create\`)
- [x] Projekt erfolgreich auf GitHub gepusht

## Technische Details
- **Framework**: Laravel 11
- **Repository**: https://github.com/alakla/als-panel
- **Branch-Strategie**: main (Hauptbranch)
- **Befehl**: \`gh repo create als-panel --public --source=. --remote=origin --push\`

## Zeitschaetzung
1 Stunde

## Status
Abgeschlossen"

# ============================================================

gh issue create `
  --title "[Setup] Bootstrap 5 und Frontend-Abhaengigkeiten installieren und konfigurieren" `
  --label "Epic: Setup" `
  --body "## User Story
Als Entwickler moechte ich Bootstrap 5 und alle Frontend-Abhaengigkeiten einrichten, damit eine responsive und benutzerfreundliche Benutzeroberflaeche entwickelt werden kann.

## Hintergrund
Bootstrap 5 wird als CSS-Framework fuer das UI verwendet. Es ermoeglicht eine schnelle Entwicklung einer responsiven Oberflaeche ohne eigenes CSS-Framework schreiben zu muessen. Vite wird als Build-Tool verwendet.

## Akzeptanzkriterien
- [ ] Bootstrap 5 via npm installiert
- [ ] Vite korrekt konfiguriert (vite.config.js)
- [ ] Bootstrap in app.scss eingebunden
- [ ] Bootstrap JS-Bundle eingebunden
- [ ] \`npm run dev\` laeuft ohne Fehler
- [ ] \`npm run build\` erstellt produktionsfertigen Assets
- [ ] Testseite zeigt Bootstrap-Styling korrekt an

## Technische Details
- **Package**: bootstrap@5.x
- **Build-Tool**: Vite (in Laravel integriert)
- **CSS**: SCSS mit Bootstrap-Import
- **JS**: Bootstrap Bundle (Popper.js inklusive)

## Befehle
\`\`\`bash
npm install bootstrap
npm install --save-dev sass
\`\`\`

## Zeitschaetzung
1 Stunde"

# ============================================================

gh issue create `
  --title "[Setup] MySQL-Datenbank und .env-Konfiguration einrichten" `
  --label "Epic: Setup" `
  --body "## User Story
Als Entwickler moechte ich die Datenbankverbindung konfigurieren, damit die Anwendung Daten persistent speichern kann.

## Hintergrund
Laravel benoetigt eine konfigurierte Datenbankverbindung. Die Verbindungsparameter werden in der .env-Datei gespeichert, die nicht ins Repository eingecheckt wird (in .gitignore).

## Akzeptanzkriterien
- [ ] MySQL/MariaDB lokal installiert und gestartet
- [ ] Datenbank 'als_panel' erstellt
- [ ] .env-Datei korrekt konfiguriert (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- [ ] \`php artisan migrate\` laeuft ohne Fehler
- [ ] Datenbankverbindung erfolgreich getestet

## Technische Details
\`\`\`env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=als_panel
DB_USERNAME=root
DB_PASSWORD=
\`\`\`

## Zeitschaetzung
1 Stunde"

# ============================================================
# EPIC 2: DATENBANKDESIGN
# ============================================================

gh issue create `
  --title "[Datenbank] Datenbankmodell entwerfen (ERD) und Tabellenstruktur planen" `
  --label "Epic: Datenbank" `
  --body "## User Story
Als Entwickler moechte ich ein vollstaendiges Entity-Relationship-Diagramm erstellen, damit die Datenbankstruktur klar definiert und nachvollziehbar dokumentiert ist.

## Hintergrund
Ein sorgfaeltig geplantes Datenbankmodell ist die Grundlage des gesamten Systems. Alle Beziehungen zwischen Entitaeten muessen klar definiert sein, bevor mit der Implementierung begonnen wird.

## Akzeptanzkriterien
- [ ] ERD-Diagramm fuer alle Hauptentitaeten erstellt
- [ ] Alle Tabellen mit Spalten und Datentypen definiert
- [ ] Fremdschluessel-Beziehungen dokumentiert
- [ ] ERD als Bild in der Projektdokumentation enthalten

## Tabellenstruktur

### users
| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| id | bigint PK | Primaerschluessel |
| name | varchar(255) | Vollstaendiger Name |
| email | varchar(255) UNIQUE | E-Mail-Adresse |
| password | varchar(255) | Gehashtes Passwort |
| role | enum('admin','mitarbeiter') | Benutzerrolle |
| is_active | boolean | Konto aktiv/inaktiv |
| timestamps | | created_at, updated_at |

### mitarbeiter
| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| id | bigint PK | Primaerschluessel |
| user_id | FK -> users | Zugehoeriger Benutzer |
| personalnummer | varchar(50) UNIQUE | Personalnummer |
| einstellungsdatum | date | Einstellungsdatum |
| stundenlohn | decimal(8,2) | Stundenlohn in EUR |
| status | enum('aktiv','inaktiv') | Beschaeftigungsstatus |
| timestamps | | created_at, updated_at |

### auftraggeber
| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| id | bigint PK | Primaerschluessel |
| firmenname | varchar(255) | Firmenname |
| ansprechpartner | varchar(255) | Ansprechpartner |
| adresse | text | Vollstaendige Adresse |
| email | varchar(255) | E-Mail |
| telefon | varchar(50) | Telefonnummer |
| stundensatz | decimal(8,2) | Abrechnungssatz in EUR |
| is_active | boolean | Aktiv/inaktiv |
| timestamps | | created_at, updated_at |

### zeiterfassungen
| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| id | bigint PK | Primaerschluessel |
| mitarbeiter_id | FK -> mitarbeiter | Zugehoeriger Mitarbeiter |
| auftraggeber_id | FK -> auftraggeber | Zugehoeriger Auftraggeber |
| datum | date | Arbeitstag |
| stunden | decimal(4,2) | Gearbeitete Stunden |
| beschreibung | text nullable | Taetigkeitsbeschreibung |
| status | enum('offen','freigegeben','abgelehnt') | Freigabestatus |
| freigegeben_von | FK -> users nullable | Admin der freigegeben hat |
| freigegeben_am | timestamp nullable | Zeitpunkt der Freigabe |
| timestamps | | created_at, updated_at |

### rechnungen
| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| id | bigint PK | Primaerschluessel |
| rechnungsnummer | varchar(50) UNIQUE | Eindeutige Rechnungsnummer |
| auftraggeber_id | FK -> auftraggeber | Rechnungsempfaenger |
| zeitraum_von | date | Abrechnungszeitraum Beginn |
| zeitraum_bis | date | Abrechnungszeitraum Ende |
| gesamtbetrag | decimal(10,2) | Rechnungsbetrag in EUR |
| pdf_pfad | varchar(255) | Pfad zur PDF-Datei |
| erstellt_am | timestamp | Erstellungsdatum |
| timestamps | | created_at, updated_at |

## Zeitschaetzung
3 Stunden"

# ============================================================

gh issue create `
  --title "[Datenbank] Laravel Migrationen und Eloquent Models erstellen" `
  --label "Epic: Datenbank" `
  --body "## User Story
Als Entwickler moechte ich alle Datenbank-Migrationen und Eloquent-Models erstellen, damit die Datenbankstruktur versioniert und die Modell-Beziehungen im Code abgebildet sind.

## Hintergrund
Laravel Migrationen ermoeglicht eine versionierte, reproduzierbare Datenbankstruktur. Eloquent Models bilden die Datenbankentitaeten als PHP-Klassen ab und definieren die Beziehungen untereinander.

## Akzeptanzkriterien
- [ ] Migration fuer 'users' (Erweiterung der Standard-Migration um 'role' und 'is_active')
- [ ] Migration fuer 'mitarbeiter' erstellt
- [ ] Migration fuer 'auftraggeber' erstellt
- [ ] Migration fuer 'zeiterfassungen' erstellt
- [ ] Migration fuer 'rechnungen' erstellt
- [ ] Alle Migrations laufen ohne Fehler (\`php artisan migrate\`)
- [ ] Eloquent Model fuer User (erweitert)
- [ ] Eloquent Model fuer Mitarbeiter mit Beziehungen
- [ ] Eloquent Model fuer Auftraggeber mit Beziehungen
- [ ] Eloquent Model fuer Zeiterfassung mit Beziehungen
- [ ] Eloquent Model fuer Rechnung mit Beziehungen
- [ ] Seeders fuer Testdaten erstellt

## Technische Details
\`\`\`bash
php artisan make:migration create_mitarbeiter_table
php artisan make:migration create_auftraggeber_table
php artisan make:migration create_zeiterfassungen_table
php artisan make:migration create_rechnungen_table
php artisan make:model Mitarbeiter
php artisan make:model Auftraggeber
php artisan make:model Zeiterfassung
php artisan make:model Rechnung
\`\`\`

## Eloquent Beziehungen
- Mitarbeiter **belongsTo** User
- Zeiterfassung **belongsTo** Mitarbeiter
- Zeiterfassung **belongsTo** Auftraggeber
- Auftraggeber **hasMany** Zeiterfassung
- Auftraggeber **hasMany** Rechnung
- Rechnung **belongsTo** Auftraggeber

## Zeitschaetzung
4 Stunden"

# ============================================================
# EPIC 3: AUTHENTIFIZIERUNG & ROLLEN
# ============================================================

gh issue create `
  --title "[Authentifizierung] Login- und Logout-System mit Laravel Breeze implementieren" `
  --label "Epic: Authentifizierung" `
  --body "## User Story
Als Benutzer (Admin oder Mitarbeiter) moechte ich mich sicher mit E-Mail und Passwort anmelden und abmelden koennen, damit nur autorisierte Personen Zugang zum System haben.

## Hintergrund
Laravel Breeze stellt ein fertiges Authentifizierungssystem bereit. Es wird angepasst, um das Rollenkonzept (Admin/Mitarbeiter) zu unterstuetzen und nach dem Login auf das jeweilige Dashboard weiterzuleiten.

## Akzeptanzkriterien
- [ ] Laravel Breeze installiert und konfiguriert
- [ ] Login-Seite mit E-Mail und Passwort
- [ ] Logout-Funktion verfuegbar
- [ ] Passwort-Reset-Funktion (per E-Mail)
- [ ] Nach Login: Weiterleitung zum rollenspezifischen Dashboard
- [ ] Fehlermeldung bei falschen Zugangsdaten
- [ ] Session-Timeout konfiguriert
- [ ] CSRF-Schutz aktiv

## Technische Details
\`\`\`bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build
php artisan migrate
\`\`\`

## Login-Weiterleitung
- Admin -> /admin/dashboard
- Mitarbeiter -> /mitarbeiter/dashboard

## Zeitschaetzung
3 Stunden"

# ============================================================

gh issue create `
  --title "[Authentifizierung] Rollenbasierte Zugriffskontrolle implementieren (Admin / Mitarbeiter)" `
  --label "Epic: Authentifizierung" `
  --body "## User Story
Als Systemadministrator moechte ich, dass verschiedene Benutzerrollen (Admin, Mitarbeiter) nur auf die fuer sie vorgesehenen Bereiche zugreifen koennen, damit die Datensicherheit und Uebersichtlichkeit gewaehrleistet sind.

## Hintergrund
Das System hat zwei Rollen:
- **Admin**: Vollzugriff - Verwaltung von Mitarbeitenden, Auftraggebern, Zeitfreigabe, Rechnungen
- **Mitarbeiter**: Eingeschraenkter Zugriff - nur eigene Zeiterfassung einsehen und erfassen

## Akzeptanzkriterien
- [ ] Middleware fuer Admin-Bereich erstellt (\`CheckAdmin\`)
- [ ] Middleware fuer Mitarbeiter-Bereich erstellt (\`CheckMitarbeiter\`)
- [ ] Admin-Routen geschuetzt (Zugriff nur fuer role='admin')
- [ ] Mitarbeiter-Routen geschuetzt (Zugriff nur fuer role='mitarbeiter')
- [ ] Unbefugter Zugriff wird auf Login-Seite umgeleitet
- [ ] Navigation zeigt nur rollenrelevante Menuepunkte an
- [ ] Gates/Policies fuer Ressourcenzugriff definiert

## Technische Details
\`\`\`bash
php artisan make:middleware CheckAdmin
php artisan make:middleware CheckMitarbeiter
\`\`\`

## Routen-Struktur
\`\`\`
/admin/*       -> Middleware: auth + CheckAdmin
/mitarbeiter/* -> Middleware: auth + CheckMitarbeiter
\`\`\`

## Zeitschaetzung
6 Stunden"

# ============================================================
# EPIC 4: MITARBEITERVERWALTUNG
# ============================================================

gh issue create `
  --title "[Mitarbeiterverwaltung] CRUD fuer Mitarbeitende implementieren (Anlegen, Bearbeiten, Deaktivieren)" `
  --label "Epic: Mitarbeiterverwaltung" `
  --body "## User Story
Als Admin moechte ich Mitarbeitende anlegen, bearbeiten und deaktivieren koennen, damit die Personalverwaltung digital und zentral gefuehrt wird.

## Hintergrund
Der Admin verwaltet alle Mitarbeitenden im System. Beim Anlegen eines Mitarbeitenden wird automatisch ein Benutzerkonto (role='mitarbeiter') erstellt. Deaktivierte Mitarbeitende koennen sich nicht mehr einloggen, ihre Daten bleiben jedoch erhalten.

## Akzeptanzkriterien
- [ ] Liste aller Mitarbeitenden (Tabelle mit Suche und Filter)
- [ ] Mitarbeiter-Detailseite anzeigen
- [ ] Neuen Mitarbeiter anlegen (Formular mit Validierung)
  - [ ] Name, E-Mail, Passwort (automatisch generiert)
  - [ ] Personalnummer, Einstellungsdatum, Stundenlohn
- [ ] Mitarbeiter bearbeiten (alle Felder ausser Passwort)
- [ ] Mitarbeiter deaktivieren (soft deactivation, kein Login mehr moeglich)
- [ ] Mitarbeiter reaktivieren
- [ ] Bestaetigung vor Deaktivierung
- [ ] Erfolgsmeldungen und Fehlermeldungen (Flash Messages)

## Technische Details
\`\`\`bash
php artisan make:controller Admin/MitarbeiterController --resource
php artisan make:request MitarbeiterRequest
\`\`\`

## Routen
\`\`\`
GET    /admin/mitarbeiter          -> index
GET    /admin/mitarbeiter/create   -> create
POST   /admin/mitarbeiter          -> store
GET    /admin/mitarbeiter/{id}     -> show
GET    /admin/mitarbeiter/{id}/edit -> edit
PUT    /admin/mitarbeiter/{id}     -> update
PATCH  /admin/mitarbeiter/{id}/deactivate -> deactivate
\`\`\`

## Validierungsregeln
- name: required, string, max:255
- email: required, email, unique:users
- stundenlohn: required, numeric, min:0
- einstellungsdatum: required, date

## Zeitschaetzung
6 Stunden"

# ============================================================
# EPIC 5: AUFTRAGGEBERVERWALTUNG
# ============================================================

gh issue create `
  --title "[Auftraggeberverwaltung] CRUD fuer Auftraggeber implementieren" `
  --label "Epic: Auftraggeberverwaltung" `
  --body "## User Story
Als Admin moechte ich Auftraggeber (Kundenunternehmen) anlegen, bearbeiten und verwalten koennen, damit alle Kundendaten zentral und aktuell im System vorhanden sind.

## Hintergrund
Auftraggeber sind die Unternehmen, an die Mitarbeitende vermittelt werden. Jeder Auftraggeber hat einen individuellen Stundensatz, der als Grundlage fuer die Rechnungsstellung dient.

## Akzeptanzkriterien
- [ ] Liste aller Auftraggeber (Tabelle mit Suche)
- [ ] Auftraggeber-Detailseite mit zugeordneten Zeiterfassungen
- [ ] Neuen Auftraggeber anlegen (Formular mit Validierung)
  - [ ] Firmenname, Ansprechpartner, Adresse
  - [ ] E-Mail, Telefon, Stundensatz
- [ ] Auftraggeber bearbeiten
- [ ] Auftraggeber deaktivieren
- [ ] Erfolgsmeldungen und Fehlermeldungen

## Technische Details
\`\`\`bash
php artisan make:controller Admin/AuftraggeberController --resource
php artisan make:request AuftraggeberRequest
\`\`\`

## Routen
\`\`\`
GET    /admin/auftraggeber          -> index
GET    /admin/auftraggeber/create   -> create
POST   /admin/auftraggeber          -> store
GET    /admin/auftraggeber/{id}     -> show
GET    /admin/auftraggeber/{id}/edit -> edit
PUT    /admin/auftraggeber/{id}     -> update
\`\`\`

## Zeitschaetzung
4 Stunden"

# ============================================================
# EPIC 6: ZEITERFASSUNG
# ============================================================

gh issue create `
  --title "[Zeiterfassung] Digitale Zeiterfassungsfunktion fuer Mitarbeitende implementieren" `
  --label "Epic: Zeiterfassung" `
  --body "## User Story
Als Mitarbeiter moechte ich meine taeglichen Arbeitsstunden digital erfassen koennen, damit meine geleisteten Stunden korrekt dokumentiert und abgerechnet werden.

## Hintergrund
Bisher erfolgte die Zeiterfassung manuell auf Papier oder in unstrukturierten Tabellen. Das neue System ermoeglicht eine digitale, strukturierte und zentrale Erfassung direkt durch den Mitarbeiter ueber den Browser.

## Akzeptanzkriterien
- [ ] Mitarbeiter sieht Uebersicht seiner eigenen Zeiteintraege
- [ ] Neuen Zeiteintrag erstellen:
  - [ ] Datum auswaehlen
  - [ ] Auftraggeber auswaehlen (nur aktive Auftraggeber)
  - [ ] Stunden eingeben (z.B. 8.5 fuer 8,5 Stunden)
  - [ ] Beschreibung (optional)
- [ ] Zeiteintrag bearbeiten (nur wenn Status = 'offen')
- [ ] Zeiteintrag loeschen (nur wenn Status = 'offen')
- [ ] Statusanzeige: offen / freigegeben / abgelehnt
- [ ] Monatsansicht: Stunden pro Monat zusammengefasst
- [ ] Validierung: keine doppelten Eintraege fuer dasselbe Datum/Auftraggeber

## Technische Details
\`\`\`bash
php artisan make:controller Mitarbeiter/ZeiterfassungController --resource
php artisan make:request ZeiterfassungRequest
\`\`\`

## Routen (Mitarbeiter-Bereich)
\`\`\`
GET  /mitarbeiter/zeiterfassung        -> index (eigene Eintraege)
GET  /mitarbeiter/zeiterfassung/create -> create
POST /mitarbeiter/zeiterfassung        -> store
GET  /mitarbeiter/zeiterfassung/{id}/edit -> edit
PUT  /mitarbeiter/zeiterfassung/{id}   -> update
DELETE /mitarbeiter/zeiterfassung/{id} -> destroy
\`\`\`

## Validierungsregeln
- datum: required, date, not_in_future
- auftraggeber_id: required, exists:auftraggeber,id
- stunden: required, numeric, min:0.5, max:24
- kein doppelter Eintrag (datum + auftraggeber_id + mitarbeiter_id)

## Zeitschaetzung
8 Stunden"

# ============================================================

gh issue create `
  --title "[Zeiterfassung] Pruef- und Freigabefunktion fuer Arbeitszeiten implementieren (Admin)" `
  --label "Epic: Zeiterfassung" `
  --body "## User Story
Als Admin moechte ich eingereichte Arbeitszeiteintraege pruefen und freigeben oder ablehnen koennen, damit nur korrekte und verifizierte Stunden als Grundlage fuer die Abrechnung dienen.

## Hintergrund
Nach der Erfassung durch den Mitarbeiter muessen Arbeitsstunden durch den Admin geprueft werden, bevor sie in die Abrechnung einfliessen. Dieser Freigabe-Workflow sichert die Datenqualitaet.

## Akzeptanzkriterien
- [ ] Admin sieht alle offenen (unfreigegebenen) Zeiteintraege
- [ ] Filtermoeglichkeit nach Mitarbeiter, Auftraggeber, Zeitraum, Status
- [ ] Einzelnen Zeiteintrag freigeben
- [ ] Einzelnen Zeiteintrag ablehnen (mit Begruendung)
- [ ] Mehrere Eintraege gleichzeitig freigeben (Bulk-Aktion)
- [ ] Mitarbeiter wird ueber Status informiert (sichtbar in seinem Dashboard)
- [ ] Freigegebene Eintraege koennen nicht mehr geaendert werden
- [ ] Protokollierung: wer hat wann freigegeben

## Technische Details
\`\`\`bash
php artisan make:controller Admin/ZeitfreigabeController
\`\`\`

## Status-Workflow
\`\`\`
[offen] --> [freigegeben] (Admin gibt frei)
[offen] --> [abgelehnt]   (Admin lehnt ab)
[abgelehnt] --> [offen]   (Mitarbeiter korrigiert und reicht erneut ein)
\`\`\`

## Zeitschaetzung
4 Stunden"

# ============================================================
# EPIC 7: ABRECHNUNG & RECHNUNGEN
# ============================================================

gh issue create `
  --title "[Abrechnung] Automatisierte Abrechnungslogik implementieren" `
  --label "Epic: Abrechnung" `
  --body "## User Story
Als Admin moechte ich auf Knopfdruck die Abrechnungsgrundlagen fuer einen bestimmten Zeitraum berechnen lassen, damit die Rechnungsstellung schnell, fehlerfrei und automatisiert erfolgen kann.

## Hintergrund
Bisher wurden Rechnungen manuell auf Basis gesammelter Arbeitszeitdaten erstellt. Das System berechnet automatisch die Gesamtstunden pro Auftraggeber und multipliziert diese mit dem vereinbarten Stundensatz.

## Akzeptanzkriterien
- [ ] Admin waehlt Auftraggeber und Abrechnungszeitraum (von/bis Datum)
- [ ] System berechnet automatisch:
  - [ ] Summe aller freigegebenen Stunden pro Mitarbeiter
  - [ ] Stunden x Stundensatz = Betrag pro Mitarbeiter
  - [ ] Gesamtbetrag fuer den Auftraggeber
- [ ] Vorschau der Abrechnungsdetails vor Rechnungserstellung
- [ ] Positionen-Auflistung (Mitarbeiter, Datum, Stunden, Betrag)
- [ ] Berechnung von Mehrwertsteuer (19% MwSt)
- [ ] Rechnungsnummer automatisch generiert (Format: RE-YYYY-NNNN)
- [ ] Bereits abgerechnete Zeitraeume werden markiert

## Berechnungslogik
\`\`\`
Nettobetrag    = Summe(freigegebene_stunden * stundensatz_auftraggeber)
MwSt (19%)     = Nettobetrag * 0.19
Gesamtbetrag   = Nettobetrag + MwSt
\`\`\`

## Zeitschaetzung
4 Stunden"

# ============================================================

gh issue create `
  --title "[Abrechnung] PDF-Rechnungserstellung implementieren mit DomPDF" `
  --label "Epic: Abrechnung" `
  --body "## User Story
Als Admin moechte ich Rechnungen als professionelle PDF-Dokumente generieren und herunterladen koennen, damit diese direkt an Auftraggeber versendet werden koennen.

## Hintergrund
DomPDF (barryvdh/laravel-dompdf) ermoeglicht die Erstellung von PDF-Dokumenten aus HTML/Blade-Templates direkt in Laravel. Die PDFs werden auf dem Server gespeichert und koennen jederzeit heruntergeladen werden.

## Akzeptanzkriterien
- [ ] DomPDF installiert und konfiguriert
- [ ] Rechnungs-Blade-Template erstellt (professionelles Layout)
- [ ] Rechnungsinhalte:
  - [ ] Firmenlogo/-header
  - [ ] Rechnungsnummer und -datum
  - [ ] Absender- und Empfaengerdaten
  - [ ] Leistungspositions-Tabelle (Datum, Mitarbeiter, Stunden, Einzelpreis, Gesamtpreis)
  - [ ] Nettobetrag, MwSt (19%), Bruttobetrag
  - [ ] Zahlungsbedingungen
- [ ] PDF wird serverseitig generiert und gespeichert (storage/invoices/)
- [ ] PDF kann heruntergeladen werden
- [ ] Bereits erstellte Rechnungen koennen erneut heruntergeladen werden
- [ ] Rechnungsliste im Admin-Bereich

## Technische Details
\`\`\`bash
composer require barryvdh/laravel-dompdf
php artisan make:controller Admin/RechnungController
\`\`\`

## PDF-Template
- Format: DIN A4, Hochformat
- Schrift: Arial/Helvetica
- Sprache: Deutsch
- Waehrung: EUR

## Zeitschaetzung
4 Stunden"

# ============================================================
# EPIC 8: UI/UX DASHBOARDS
# ============================================================

gh issue create `
  --title "[UI/UX] Admin-Dashboard entwickeln mit Bootstrap 5" `
  --label "Epic: UI/UX" `
  --body "## User Story
Als Admin moechte ich ein uebersichtliches Dashboard haben, das mir auf einen Blick alle wichtigen Informationen anzeigt und einfachen Zugang zu allen Verwaltungsfunktionen bietet.

## Hintergrund
Das Admin-Dashboard ist die zentrale Anlaufstelle fuer den Administrator. Es zeigt wichtige Kennzahlen und bietet schnellen Zugang zu den wichtigsten Funktionen.

## Akzeptanzkriterien
- [ ] Responsives Layout mit Bootstrap 5 (Sidebar + Hauptbereich)
- [ ] Navigation mit allen Admin-Bereichen
- [ ] Dashboard-Kacheln (KPI-Karten):
  - [ ] Anzahl aktiver Mitarbeiter
  - [ ] Anzahl aktiver Auftraggeber
  - [ ] Offene (nicht freigegebene) Zeiteintraege
  - [ ] Rechnungen diesen Monat
- [ ] Tabelle: Neueste offene Zeiteintraege (letzte 10)
- [ ] Tabelle: Zuletzt erstellte Rechnungen
- [ ] Schnellzugriff-Buttons (Mitarbeiter anlegen, Rechnung erstellen)
- [ ] Flash-Nachrichten (Erfolg/Fehler) sichtbar
- [ ] Logout-Button

## Layout-Struktur
\`\`\`
+------------------+--------------------------------+
|   SIDEBAR        |   HEADER (Breadcrumb + User)   |
|   - Dashboard    +--------------------------------+
|   - Mitarbeiter  |                                |
|   - Auftraggeber |   HAUPTINHALT                  |
|   - Zeitfreigabe |   (KPI-Karten + Tabellen)      |
|   - Rechnungen   |                                |
|   - Logout       |                                |
+------------------+--------------------------------+
\`\`\`

## Zeitschaetzung
4 Stunden"

# ============================================================

gh issue create `
  --title "[UI/UX] Mitarbeiter-Dashboard entwickeln mit Bootstrap 5" `
  --label "Epic: UI/UX" `
  --body "## User Story
Als Mitarbeiter moechte ich ein einfaches, uebersichtliches Dashboard haben, das mir meine Zeiteintraege anzeigt und eine schnelle Erfassung neuer Stunden ermoeglichst.

## Hintergrund
Das Mitarbeiter-Dashboard ist bewusst einfach gehalten. Der Mitarbeiter hat nur Zugriff auf die eigene Zeiterfassung. Alle anderen Verwaltungsfunktionen sind ausgeblendet.

## Akzeptanzkriterien
- [ ] Einfaches, aufgeraeumtes Layout (keine Sidebar mit Admin-Funktionen)
- [ ] Begruessung mit Mitarbeitername
- [ ] Monatsansicht: aktuelle Monatsstunden (Gesamt)
- [ ] Status-Uebersicht: Offene / Freigegebene / Abgelehnte Eintraege
- [ ] Tabelle: Zeiteintraege des aktuellen Monats
- [ ] Button: Neuen Zeiteintrag erfassen
- [ ] Hinweis bei abgelehnten Eintraegen (mit Begruendung)
- [ ] Monatsfilter (vergangene Monate anzeigen)
- [ ] Logout-Button

## Zeitschaetzung
3 Stunden"

# ============================================================
# EPIC 9: TESTING
# ============================================================

gh issue create `
  --title "[Testing] Testfaelle erstellen und Funktionstests durchfuehren" `
  --label "Epic: Testing" `
  --body "## User Story
Als Entwickler moechte ich alle Kernfunktionen systematisch testen, damit die Anwendung zuverlaessig funktioniert und Fehler vor der Abgabe identifiziert und behoben werden.

## Hintergrund
Die Testphase stellt sicher, dass alle implementierten Funktionen korrekt arbeiten. Fehler werden dokumentiert und behoben. Fuer die IHK-Pruefung muss die Testdurchfuehrung dokumentiert werden.

## Akzeptanzkriterien

### Authentifizierung
- [ ] Login mit gueltigen Zugangsdaten -> Weiterleitung zum Dashboard
- [ ] Login mit ungueltigen Zugangsdaten -> Fehlermeldung
- [ ] Admin kann nicht auf Mitarbeiter-Bereich zugreifen und umgekehrt
- [ ] Logout funktioniert korrekt

### Mitarbeiterverwaltung
- [ ] Neuen Mitarbeiter anlegen (gueltiger Datensatz)
- [ ] Mitarbeiter anlegen mit fehlenden Pflichtfeldern -> Validierungsfehler
- [ ] Mitarbeiter bearbeiten -> Aenderungen gespeichert
- [ ] Mitarbeiter deaktivieren -> Login nicht mehr moeglich

### Auftraggeberverwaltung
- [ ] Auftraggeber anlegen, bearbeiten
- [ ] Duplikate werden verhindert

### Zeiterfassung
- [ ] Mitarbeiter erfasst Stunden -> Eintrag erscheint mit Status 'offen'
- [ ] Doppelter Eintrag (gleicher Tag + Auftraggeber) -> Fehlermeldung
- [ ] Freigabe durch Admin -> Status aendert sich auf 'freigegeben'
- [ ] Freigegebener Eintrag kann nicht mehr geaendert werden

### Abrechnung & PDF
- [ ] Abrechnungsberechnung korrekt (Stunden x Stundensatz + MwSt)
- [ ] PDF wird generiert und kann heruntergeladen werden
- [ ] Rechnungsnummer ist eindeutig

## Testprotokoll
Alle Tests werden in einer Tabelle dokumentiert:
| Test-ID | Beschreibung | Erwartetes Ergebnis | Tatsaechliches Ergebnis | Status |

## Zeitschaetzung
7 Stunden (3h Testfaelle + 4h Durchfuehrung und Fehlerbehebung)"

# ============================================================
# EPIC 10: DOKUMENTATION
# ============================================================

gh issue create `
  --title "[Dokumentation] Technische Dokumentation und Projektdokumentation (IHK) erstellen" `
  --label "Epic: Dokumentation" `
  --body "## User Story
Als Pruefungsteilnehmer moechte ich eine vollstaendige technische und fachliche Projektdokumentation erstellen, die alle Anforderungen des IHK-Pruefungsausschusses erfuellt.

## Hintergrund
Gemaess Pruefungsverordnung muss eine Projektdokumentation eingereicht werden (max. 15 DIN-A4-Seiten, Arial 11pt, einzeilig, als PDF). Diese bildet 50% der Bewertung.

## Akzeptanzkriterien
- [ ] Deckblatt (Name, Geburtsdatum, Ausbildungsberuf, Ausbildungsbetrieb, Projekttitel)
- [ ] Inhaltsverzeichnis
- [ ] 1. Projektbeschreibung und Ausgangssituation (Ist-Analyse)
- [ ] 2. Zielsetzung und Soll-Konzept (inkl. verwendete Technologien)
- [ ] 3. Projektplanung (Projektstrukturplan, Zeitplan)
- [ ] 4. Implementierung (Architektur, Datenbankmodell, wichtige Code-Ausschnitte)
- [ ] 5. Testdokumentation (Testfaelle und Ergebnisse)
- [ ] 6. Fazit und Ausblick
- [ ] Anhang: ERD-Diagramm, Screenshots, Codeauszuege
- [ ] Quellenverzeichnis
- [ ] Maximale Seitenzahl eingehalten (15 Seiten ohne Deckblatt/Inhaltsverzeichnis)
- [ ] Format: Arial 11pt, einzeilig, Seitenraender gemaess Vorgabe
- [ ] Als PDF exportiert (max. 5 MB)

## Formatvorgaben (IHK)
- Schriftgroesse: Arial 11 Punkt
- Zeilenabstand: einzeilig
- Seitenraender: oben 3,3cm | links 2,5cm | unten 2,5cm | rechts 2,5cm
- Seiten fortlaufend nummeriert

## Zeitschaetzung
8 Stunden"

# ============================================================

gh issue create `
  --title "[Dokumentation] Anwenderdokumentation (Benutzerhandbuch) erstellen" `
  --label "Epic: Dokumentation" `
  --body "## User Story
Als Endbenutzer (Admin und Mitarbeiter) moechte ich eine verstaendliche Anleitung zur Nutzung des Systems haben, damit ich alle Funktionen ohne technisches Vorwissen bedienen kann.

## Hintergrund
Die Anwenderdokumentation richtet sich an die tatsaechlichen Benutzer des Systems (Admin und Mitarbeitende der Personaldienstleistungsfirma), nicht an Entwickler. Sie wird als Anhang der IHK-Dokumentation beigefuegt.

## Akzeptanzkriterien
- [ ] Anleitung fuer Admin:
  - [ ] Login und Passwortverwaltung
  - [ ] Mitarbeiter anlegen und verwalten
  - [ ] Auftraggeber anlegen und verwalten
  - [ ] Zeiteintraege pruefen und freigeben
  - [ ] Rechnung erstellen und herunterladen
- [ ] Anleitung fuer Mitarbeiter:
  - [ ] Login
  - [ ] Neuen Zeiteintrag erfassen
  - [ ] Zeiteintraege einsehen und Status pruefen
- [ ] Screenshots der wichtigsten Seiten
- [ ] Verstaendliche Sprache (kein Fachjargon)
- [ ] Als Teil des PDF-Anhangs

## Zeitschaetzung
3 Stunden"

Write-Host ""
Write-Host "Alle Issues wurden erfolgreich erstellt!" -ForegroundColor Green
Write-Host "Oeffne GitHub Issues:" -ForegroundColor Cyan
gh issue list
