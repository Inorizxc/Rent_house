<div class="settings-tab-content">
    {{-- Профиль --}}
    <div class="settings-section">
        <livewire:users.settings-tab-profile />
    </div>

    {{-- Смена пароля --}}
    <div class="settings-section">
        <livewire:users.settings-tab-password />
    </div>

    {{-- Верификация --}}
    <div class="settings-section settings-verification">
        <div class="settings-section-title">Верификация</div>
        <p class="settings-section-text">
            Подайте заявку на верификацию аккаунта для повышения доверия к вашему профилю.
        </p>
        <button 
            type="button" 
            class="verification-button"
            onclick="alert('Функция верификации будет доступна в ближайшее время.')"
        >
            Подать на верификацию
        </button>
    </div>
</div>

