<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ALS Panel') }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">ALS Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                {{-- Hauptnavigation: rollenabhängige Links --}}
                <ul class="navbar-nav me-auto">
                    @if(Auth::user()->isAdmin())
                        {{-- Admin-Navigation --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                               href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.mitarbeiter.*') ? 'active' : '' }}"
                               href="{{ route('admin.mitarbeiter.index') }}">Mitarbeiter</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.auftraggeber.*') ? 'active' : '' }}"
                               href="{{ route('admin.auftraggeber.index') }}">Auftraggeber</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.rechnungen.*') ? 'active' : '' }}"
                               href="{{ route('admin.rechnungen.index') }}">Rechnungen</a>
                        </li>
                        {{-- Aufträge: Admin weist Arbeitsaufträge zu --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.auftraege.*') ? 'active' : '' }}"
                               href="{{ route('admin.auftraege.index') }}">Aufträge</a>
                        </li>
                        {{-- Einstellung-Dropdown: Tätigkeiten + Rechnungeinstellungen --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.taetigkeiten.*', 'admin.einstellungen.*') ? 'active' : '' }}"
                               href="#"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                Einstellung
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('admin.taetigkeiten.*') ? 'active' : '' }}"
                                       href="{{ route('admin.taetigkeiten.index') }}">
                                        Tätigkeiten
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('admin.einstellungen.*') ? 'active' : '' }}"
                                       href="{{ route('admin.einstellungen.edit') }}">
                                        Rechnungeinstellungen
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @else
                        {{-- Mitarbeiter-Navigation --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mitarbeiter.dashboard') ? 'active' : '' }}"
                               href="{{ route('mitarbeiter.dashboard') }}">Dashboard</a>
                        </li>
                        {{-- Aufträge: Mitarbeitender sieht seine zugewiesenen Einsätze --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mitarbeiter.auftraege.*') ? 'active' : '' }}"
                               href="{{ route('mitarbeiter.auftraege.index') }}">Aufträge</a>
                        </li>
                    @endif
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Abmelden</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Flash Messages: verschwinden automatisch (Erfolg: 4 Sek., Fehler: 5 Sek.) --}}
    <div class="container mt-3" id="flashMessages">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" id="flash-success" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <script>
                setTimeout(function () {
                    var el = document.getElementById('flash-success');
                    if (el) { el.classList.remove('show'); setTimeout(function () { el.remove(); }, 1500); }
                }, 4000);
            </script>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" id="flash-error" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <script>
                setTimeout(function () {
                    var el = document.getElementById('flash-error');
                    if (el) { el.classList.remove('show'); setTimeout(function () { el.remove(); }, 1500); }
                }, 5000);
            </script>
        @endif
    </div>
    <style>
        /* Langsames Ausblenden für alle Meldungen (1.5 Sekunden statt Standard 0.15s) */
        .alert.fade { transition: opacity 1.5s ease !important; }
        /* Einheitliche Breite für alle Status-Badges */
        .badge-status { display: block; text-align: center; min-width: 90px; }
        /* Orangefarbenes Badge für Status "Ausstehend" (Mitarbeiter) und "Offen" (Admin) */
        .badge-orange { background-color: #fd7e14 !important; color: #fff !important; }
        /* Alle Tabellenspalten: Überschriften und Werte zentriert */
        .table th, .table td { text-align: center; vertical-align: middle; }
        /* Kalender-Picker-Indikator wiederherstellen (von Bootstrap-Reset versteckt) */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="month"]::-webkit-calendar-picker-indicator { display: block !important; opacity: 0.6; cursor: pointer; }
    </style>

    <script>
        // Debounce-Funktion für Live-Suche: verzögert das Abschicken um 350ms nach letzter Eingabe
        var debounceTimer = null;
        function debounceSubmit(formId) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                var input = document.getElementById('sucheInput');
                if (input) {
                    // Cursor-Position und Flag speichern (auch bei leerem Feld)
                    sessionStorage.setItem('suchePos', input.selectionEnd);
                    sessionStorage.setItem('sucheFokus', '1');
                }
                document.getElementById(formId).submit();
            }, 350);
        }

        // Nach Seitenladung: Fokus und Cursor-Position im Suchfeld wiederherstellen
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('sucheInput');
            if (input && sessionStorage.getItem('sucheFokus')) {
                var pos = parseInt(sessionStorage.getItem('suchePos') || '0');
                sessionStorage.removeItem('sucheFokus');
                sessionStorage.removeItem('suchePos');
                input.focus();
                input.setSelectionRange(pos, pos);
            }
        });
    </script>

    {{-- Globales Bestätigungs-Modal (wird per data-confirm ausgelöst) --}}
    <div class="modal fade" id="globalConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="globalConfirmTitle">Bestätigung</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-3">
                    <p class="mb-0" id="globalConfirmText"></p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-sm" id="globalConfirmBtn">Bestätigen</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Globaler Bestätigungs-Handler für data-confirm Attribute
        // Verwendung: <form data-confirm="Nachricht" data-confirm-btn="danger|warning|success">
        //         oder <button data-confirm="Nachricht" data-confirm-btn="danger">
        (function () {
            var pendingAction = null;
            var modal = null;

            document.addEventListener('click', function (e) {
                // Button oder Link mit data-confirm
                var el = e.target.closest('[data-confirm]');
                if (!el) return;

                // Nur wenn kein eigenes form-submit-handling benötigt wird
                var form = el.closest('form');
                if (form && el.tagName !== 'BUTTON') return;

                e.preventDefault();
                e.stopPropagation();

                var msg     = el.dataset.confirm || 'Wirklich fortfahren?';
                var btnType = el.dataset.confirmBtn || 'primary';

                document.getElementById('globalConfirmText').textContent = msg;
                var confirmBtn = document.getElementById('globalConfirmBtn');
                confirmBtn.className = 'btn btn-sm btn-' + btnType;

                pendingAction = function () {
                    if (form) {
                        form.submit();
                    } else if (el.href) {
                        window.location = el.href;
                    }
                };

                if (!modal) modal = new bootstrap.Modal(document.getElementById('globalConfirmModal'));
                modal.show();
            });

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form.dataset.confirm) return;
                e.preventDefault();

                var msg     = form.dataset.confirm || 'Wirklich fortfahren?';
                var btnType = form.dataset.confirmBtn || 'primary';

                document.getElementById('globalConfirmText').textContent = msg;
                var confirmBtn = document.getElementById('globalConfirmBtn');
                confirmBtn.className = 'btn btn-sm btn-' + btnType;

                pendingAction = function () { form.submit(); };

                if (!modal) modal = new bootstrap.Modal(document.getElementById('globalConfirmModal'));
                modal.show();
            }, true);

            document.getElementById('globalConfirmBtn').addEventListener('click', function () {
                if (modal) modal.hide();
                if (pendingAction) { pendingAction(); pendingAction = null; }
            });
        })();
    </script>

    {{-- Page Content --}}
    <main class="container mt-4 mb-5">
        {{ $slot }}
    </main>

</body>
</html>
