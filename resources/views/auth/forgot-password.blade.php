<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">¿Olvidaste tu contraseña?</h2>
        <div class="text-sm text-gray-600 leading-relaxed">
            No te preocupes, es normal. Ingresa tu dirección de correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
        </div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-gray-700 font-medium" />
            <x-text-input 
                id="email" 
                class="block mt-2 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                type="email" 
                name="email" 
                :value="old('email')" 
                placeholder="Ingresa tu correo electrónico"
                required 
                autofocus 
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col space-y-4">
            <x-primary-button class="w-full py-3 bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 transition duration-200 text-white font-medium rounded-lg">
                {{ __('Enviar Enlace de Recuperación') }}
            </x-primary-button>
            
            <div class="text-center">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline transition duration-200">
                    ← Volver al inicio de sesión
                </a>
            </div>
        </div>
    </form>

    <!-- Información adicional -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div class="text-sm text-blue-800">
                <strong>Nota:</strong> Revisa tu bandeja de entrada y la carpeta de spam. El enlace será válido por 60 minutos.
            </div>
        </div>
    </div>
</x-guest-layout>
