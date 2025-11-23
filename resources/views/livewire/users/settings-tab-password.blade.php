<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('password-updated');
    }
}; ?>

<div class="settings-section-card">
    <div class="settings-section-title">Безопасность</div>
    <form method="POST" wire:submit="updatePassword" class="settings-form">
        <flux:input
            wire:model="current_password"
            :label="__('Текущий пароль')"
            type="password"
            required
            autocomplete="current-password"
        />
        <flux:input
            wire:model="password"
            :label="__('Новый пароль')"
            type="password"
            required
            autocomplete="new-password"
        />
        <flux:input
            wire:model="password_confirmation"
            :label="__('Подтвердите пароль')"
            type="password"
            required
            autocomplete="new-password"
        />

        <div class="settings-form-actions">
            <button type="submit" class="settings-save-button">
                {{ __('Сохранить') }}
            </button>
            <x-action-message class="settings-action-message" on="password-updated">
                {{ __('Сохранено.') }}
            </x-action-message>
        </div>
    </form>
</div>

