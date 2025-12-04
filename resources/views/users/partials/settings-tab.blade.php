<div class="settings-tab-content">
    @if (session('success'))
        <div class="settings-action-message" style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center;">
            <svg style="width: 20px; height: 20px; margin-right: 8px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="settings-action-message" style="background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center;">
            <svg style="width: 20px; height: 20px; margin-right: 8px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

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
                @php
                    $currentUser = auth()->user();
                    $canRequestVerification = false;
                    $verificationStatus = '';
                    $deniedUntil = null;
                    
                    if ($currentUser) {
                        // Проверяем, может ли пользователь подать заявку
                        if ($currentUser->need_verification) {
                            $verificationStatus = 'pending';
                        } elseif ($currentUser->verification_denied_until) {
                            $deniedUntil = \Carbon\Carbon::parse($currentUser->verification_denied_until);
                            $rejectReason = $currentUser->verified_deny_reason;
                            if ($deniedUntil->isFuture()) {
                                $verificationStatus = 'denied';
                            } else {
                                $canRequestVerification = true;
                            }
                        } elseif ($currentUser->isRentDealer() || $currentUser->isAdmin()) {
                            $verificationStatus = 'verified';
                        } else {
                            $canRequestVerification = true;
                        }
                    }
                @endphp
                
                @if($verificationStatus === 'pending')
                    <div class="settings-action-message" style="background: #dbeafe; border-color: #3b82f6; color: #1e40af;">
                        <svg style="width: 20px; height: 20px; margin-right: 8px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
                        </svg>
                        Ваша заявка на верификацию находится на рассмотрении
                    </div>
                @elseif($verificationStatus === 'verified')
                    <div class="settings-action-message" style="background: #d1fae5; border-color: #10b981; color: #065f46;">
                        <svg style="width: 20px; height: 20px; margin-right: 8px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Ваш аккаунт верифицирован
                    </div>
                @elseif($verificationStatus === 'denied' && $deniedUntil)
                    <div class="settings-action-message" style="background: #fee2e2; border-color: #ef4444; color: #991b1b;">
                        <svg style="width: 20px; height: 20px; margin-right: 8px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
                        </svg>
                        Вы сможете подать заявку на верификацию после {{ $deniedUntil->format('d.m.Y') }}. Причина отказа: {{ $rejectReason }}
                    </div>
                @elseif($canRequestVerification)
                    <form method="POST" action="{{ route('verification.request') }}" id="verification-form">
                        @csrf
                        <button 
                            type="submit" 
                            class="settings-save-button settings-button-verification"
                            id="verification-submit-btn"
                        >
                            <svg class="settings-button-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span id="verification-btn-text">Подать на верификацию</span>
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const verificationForm = document.getElementById('verification-form');
    const verificationBtn = document.getElementById('verification-submit-btn');
    const verificationBtnText = document.getElementById('verification-btn-text');
    
    if (verificationForm && verificationBtn) {
        verificationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Блокируем кнопку
            verificationBtn.disabled = true;
            if (verificationBtnText) {
                verificationBtnText.textContent = 'Отправка...';
            }
            
            try {
                const formData = new FormData(verificationForm);
                const response = await fetch(verificationForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Меняем кнопку на "Заявка подана"
                    if (verificationBtnText) {
                        verificationBtnText.textContent = 'Заявка подана';
                    }
                    verificationBtn.style.background = '#dbeafe';
                    verificationBtn.style.borderColor = '#3b82f6';
                    verificationBtn.style.color = '#1e40af';
                    verificationBtn.style.cursor = 'not-allowed';
                    
                    // Показываем сообщение об успехе
                    const successMessage = document.createElement('div');
                    successMessage.className = 'settings-action-message';
                    successMessage.style.cssText = 'background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center;';
                    successMessage.innerHTML = `
                        <svg style="width: 20px; height: 20px; margin-right: 8px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        ${data.message || 'Заявка на верификацию успешно подана. Мы рассмотрим её в ближайшее время.'}
                    `;
                    verificationForm.parentElement.insertBefore(successMessage, verificationForm);
                    
                    // Обновляем статус через небольшую задержку, чтобы показать новое состояние
                    setTimeout(() => {
                        // Заменяем форму на сообщение о статусе
                        const statusMessage = document.createElement('div');
                        statusMessage.className = 'settings-action-message';
                        statusMessage.style.cssText = 'background: #dbeafe; border: 1px solid #3b82f6; color: #1e40af; padding: 12px 16px; border-radius: 8px; display: flex; align-items: center;';
                        statusMessage.innerHTML = `
                            <svg style="width: 20px; height: 20px; margin-right: 8px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
                            </svg>
                            Ваша заявка на верификацию находится на рассмотрении
                        `;
                        verificationForm.parentElement.replaceChild(statusMessage, verificationForm);
                    }, 2000);
                } else {
                    // Показываем ошибку
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'settings-action-message';
                    errorMessage.style.cssText = 'background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center;';
                    errorMessage.innerHTML = `
                        <svg style="width: 20px; height: 20px; margin-right: 8px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
                        </svg>
                        ${data.message || 'Произошла ошибка при отправке заявки'}
                    `;
                    verificationForm.parentElement.insertBefore(errorMessage, verificationForm);
                    
                    // Разблокируем кнопку
                    verificationBtn.disabled = false;
                    if (verificationBtnText) {
                        verificationBtnText.textContent = 'Подать на верификацию';
                    }
                    
                    // Удаляем сообщение об ошибке через 5 секунд
                    setTimeout(() => {
                        errorMessage.remove();
                    }, 5000);
                }
            } catch (error) {
                console.error('Ошибка при отправке заявки:', error);
                
                // Показываем ошибку
                const errorMessage = document.createElement('div');
                errorMessage.className = 'settings-action-message';
                errorMessage.style.cssText = 'background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center;';
                errorMessage.innerHTML = `
                    <svg style="width: 20px; height: 20px; margin-right: 8px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
                    </svg>
                    Произошла ошибка при отправке заявки. Попробуйте обновить страницу.
                `;
                verificationForm.parentElement.insertBefore(errorMessage, verificationForm);
                
                // Разблокируем кнопку
                verificationBtn.disabled = false;
                if (verificationBtnText) {
                    verificationBtnText.textContent = 'Подать на верификацию';
                }
                
                // Удаляем сообщение об ошибке через 5 секунд
                setTimeout(() => {
                    errorMessage.remove();
                }, 5000);
            }
        });
    }
});
</script>

