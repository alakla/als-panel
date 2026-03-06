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
                {{-- Hauptnavigation: rollenabhaengige Links --}}
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
                            <a class="nav-link {{ request()->routeIs('admin.zeitfreigabe.*') ? 'active' : '' }}"
                               href="{{ route('admin.zeitfreigabe.index') }}">Zeitfreigabe</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.rechnungen.*') ? 'active' : '' }}"
                               href="{{ route('admin.rechnungen.index') }}">Rechnungen</a>
                        </li>
                    @else
                        {{-- Mitarbeiter-Navigation --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mitarbeiter.dashboard') ? 'active' : '' }}"
                               href="{{ route('mitarbeiter.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mitarbeiter.zeiterfassung.*') ? 'active' : '' }}"
                               href="{{ route('mitarbeiter.zeiterfassung.index') }}">Zeiterfassung</a>
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

    {{-- Flash Messages --}}
    <div class="container mt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Page Content --}}
    <main class="container mt-4 mb-5">
        {{ $slot }}
    </main>

</body>
</html>
