<x-layouts::auth :title="__('Restablecer contraseña')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Restablece tu contraseña')"
            :description="__('Ingresa y confirma tu nueva contraseña.')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf

            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <flux:input name="email" value="{{ request('email') }}" :label="__('Correo electrónico')" type="email"
                required autocomplete="email" />

            <flux:input name="password" :label="__('Nueva contraseña')" type="password" required
                autocomplete="new-password" :placeholder="__('Mínimo 8 caracteres')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable />

            <flux:input name="password_confirmation" :label="__('Confirmar contraseña')" type="password" required
                autocomplete="new-password" :placeholder="__('Repite tu contraseña')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    {{ __('Restablecer contraseña') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
