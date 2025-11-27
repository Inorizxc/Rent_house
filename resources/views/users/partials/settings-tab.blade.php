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
    <div class="settings-section">
        <div class="settings-section-card settings-card-enhanced settings-card-verification">
            <div class="settings-section-header">
                <div class="settings-icon-wrapper settings-icon-verification">
                    <svg class="settings-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="settings-section-title">Верификация</div>
            </div>
            <div class="settings-verification-content">
                <p class="settings-section-text">
                    Подайте заявку на верификацию аккаунта для повышения доверия к вашему профилю. Верифицированные пользователи получают специальный значок и повышенное доверие со стороны других участников платформы.
                </p>
                <div class="settings-verification-benefits">
                    <div class="settings-benefit-item">
                        <svg class="settings-benefit-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Повышенное доверие</span>
                    </div>
                    <div class="settings-benefit-item">
                        <svg class="settings-benefit-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Специальный значок</span>
                    </div>
                    <div class="settings-benefit-item">
                        <svg class="settings-benefit-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Приоритетная поддержка</span>
                    </div>
                </div>
            </div>
            <div class="settings-form-actions">
                <button 
                    type="button" 
                    class="settings-save-button settings-button-verification"
                    onclick="alert('Функция верификации будет доступна в ближайшее время.')"
                >
                    <svg class="settings-button-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Подать на верификацию
                </button>
            </div>
        </div>
    </div>
</div>

