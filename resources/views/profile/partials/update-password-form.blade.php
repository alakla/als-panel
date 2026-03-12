<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Passwort ändern
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Verwenden Sie ein langes, zufälliges Passwort für mehr Sicherheit.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Aktuelles Passwort" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Neues Passwort" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Passwort bestätigen" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="d-flex align-items-center gap-3 mt-2">
            <button type="submit" class="btn btn-primary">Passwort ändern</button>

            @if (session('status') === 'password-updated')
                <span class="text-success small">Gespeichert.</span>
            @endif
        </div>
    </form>
</section>
