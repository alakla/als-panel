<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Profilinformationen
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Name und E-Mail-Adresse aktualisieren.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if($user->mitarbeiter)
        <div>
            <x-input-label for="telefon" value="Telefonnummer" />
            <x-text-input id="telefon" name="telefon" type="tel" class="mt-1 block w-full"
                :value="old('telefon', $user->mitarbeiter->telefon)"
                placeholder="z.B. 0176 12345678" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('telefon')" />
        </div>
        @endif

        <div class="d-flex align-items-center gap-3 mt-2">
            <button type="submit" class="btn btn-primary">Speichern</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small">Gespeichert.</span>
            @endif
        </div>
    </form>
</section>
