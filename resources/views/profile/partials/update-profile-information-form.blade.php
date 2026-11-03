<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
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

        @php
            $sidebarThemes = [
                'navy' => ['label' => 'Azul noche', 'colors' => ['#10213f', '#111b32']],
                'blue' => ['label' => 'Azul', 'colors' => ['#123d78', '#0b2549']],
                'violet' => ['label' => 'Violeta', 'colors' => ['#4c1d95', '#261245']],
                'emerald' => ['label' => 'Esmeralda', 'colors' => ['#065f46', '#063b30']],
                'burgundy' => ['label' => 'Vino', 'colors' => ['#7f1d3a', '#451225']],
                'slate' => ['label' => 'Grafito', 'colors' => ['#334155', '#172033']],
            ];
            $selectedTheme = old('sidebar_theme', $user->sidebar_theme ?? 'navy');
        @endphp

        <div>
            <x-input-label :value="__('Color de la barra lateral')" />
            <p class="mt-1 text-sm text-gray-600">Elige el color que verás en el menú principal.</p>
            <div class="sidebar-theme-picker mt-3">
                @foreach($sidebarThemes as $key => $theme)
                    <label class="sidebar-theme-option">
                        <input type="radio" name="sidebar_theme" value="{{ $key }}" @checked($selectedTheme === $key) data-sidebar-theme="{{ $key }}">
                        <span class="sidebar-theme-card">
                            <span class="sidebar-theme-swatch" style="background: linear-gradient(135deg, {{ $theme['colors'][0] }}, {{ $theme['colors'][1] }});"><i class="fas fa-check"></i></span>
                            <span>{{ $theme['label'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('sidebar_theme')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

<script>
    document.querySelectorAll('[data-sidebar-theme]').forEach((option) => {
        option.addEventListener('change', () => {
            document.body.className = document.body.className.replace(/theme-sidebar-[a-z]+/g, '').trim();
            document.body.classList.add(`theme-sidebar-${option.value}`);
        });
    });
</script>
