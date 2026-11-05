<x-layouts::auth :title="__('Crear cuenta')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Crea tu cuenta')"
            :description="__('Completa tus datos para comenzar a gestionar tu inventario.')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input name="name" :label="__('Nombre completo')" :value="old('name')" type="text" required
                autofocus autocomplete="name" :placeholder="__('Ej. Ana Torres')" />

            <flux:input name="email" :label="__('Correo electrónico')" :value="old('email')" type="email" required
                autocomplete="email" placeholder="email@empresa.com" />

            <flux:input name="password" :label="__('Contraseña')" type="password" required autocomplete="new-password"
                :placeholder="__('Mínimo 8 caracteres')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable />

            <flux:input name="password_confirmation" :label="__('Confirmar contraseña')" type="password" required
                autocomplete="new-password" :placeholder="__('Repite tu contraseña')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Crear cuenta') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-center text-sm text-zinc-600 rtl:space-x-reverse dark:text-zinc-400">
            <span>{{ __('¿Ya tienes una cuenta?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Iniciar sesión') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
