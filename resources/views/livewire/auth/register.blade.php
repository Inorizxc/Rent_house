<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $sename = '';
    public string $patronymic = '';
    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sename' => ['required', 'string', 'max:255'],
            'patronymic' => ['string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            
        ]);
        $hashedPassword = Hash::make($validated['password']);
        event(new Registered(($user = User::create([
            'name' =>$validated["name"],
            'sename' =>$validated["sename"],
            'patronymic' =>$validated["patronymic"],
            "email" => $validated['email'],
            "password" => $hashedPassword,
            "role_id" => "3",
        ]
    
    ))));

        Auth::login($user);

        Session::regenerate();

        $this->redirectIntended(route('map', absolute: false), navigate: false);

    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Имя')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Имя')"
        />

        <flux:input
            wire:model="sename"
            :label="__('Фамилия')"
            type="text"
            required
            autofocus
            autocomplete="sename"
            :placeholder="__('Фамилия')"
        />

        <flux:input
            wire:model="patronymic"
            :label="__('Отчество')"
            type="text"
            required
            autofocus
            autocomplete="patronymic"
            :placeholder="__('Отчество')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Электронная почта')"
            type="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Пароль')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Пароль')"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Подтверждение пароля')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Подтверждение пароля')"
            viewable
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
