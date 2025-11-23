<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $sename = '';
    public string $patronymic = '';
    public string $birth_date = '';
    public string $phone = '';
    public string $email = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name ?? '';
        $this->sename = $user->sename ?? '';
        $this->patronymic = $user->patronymic ?? '';
        $this->birth_date = $user->birth_date ? $user->birth_date->format('Y-m-d') : '';
        $this->phone = $user->phone ?? '';
        $this->email = $user->email ?? '';
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sename' => ['required', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->user_id, 'user_id')
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }
}; ?>

<div class="settings-section-card">
    <div class="settings-section-title">Личные данные</div>
    <form wire:submit="updateProfileInformation" class="settings-form">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <flux:input wire:model="name" :label="__('Имя')" type="text" required autofocus autocomplete="given-name" />
            <flux:input wire:model="sename" :label="__('Фамилия')" type="text" required autocomplete="family-name" />
        </div>
        
        <flux:input wire:model="patronymic" :label="__('Отчество')" type="text" autocomplete="additional-name" />
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <flux:input wire:model="birth_date" :label="__('Дата рождения')" type="date" />
            <flux:input wire:model="phone" :label="__('Номер телефона')" type="tel" autocomplete="tel" />
        </div>
        
        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

        <div class="settings-form-actions">
            <button type="submit" class="settings-save-button">
                {{ __('Сохранить') }}
            </button>
            <x-action-message class="settings-action-message" on="profile-updated">
                {{ __('Сохранено.') }}
            </x-action-message>
        </div>
    </form>
</div>

