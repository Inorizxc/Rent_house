@if ($users->isEmpty())
    <div class="empty-state">
        <p>Нет пользователей для отображения.</p>
    </div>
@else
    <div style="overflow-x: auto;">
        <table class="verification-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: #f9fafb;">
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">ID</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">Пользователь</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">Роль</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">Email</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">Статус</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    @php
                        $userInitials = 'U';
                        $name = trim(($user->name ?? '') . ' ' . ($user->sename ?? ''));
                        if ($name) {
                            $words = explode(' ', $name);
                            $userInitials = '';
                            foreach ($words as $word) {
                                if (!empty($word)) {
                                    $userInitials .= mb_substr($word, 0, 1, 'UTF-8');
                                    if (mb_strlen($userInitials, 'UTF-8') >= 2) break;
                                }
                            }
                            if (empty($userInitials)) $userInitials = mb_substr($name, 0, 1, 'UTF-8');
                        }
                        $userInitials = mb_strtoupper($userInitials, 'UTF-8');
                        $isBanned = $user->isBanned();
                    @endphp
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px;"><strong>#{{ $user->user_id }}</strong></td>
                        <td style="padding: 12px;">
                            <div class="user-info" style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-weight: 600; font-size: 16px;">{{ $userInitials }}</div>
                                <div class="user-details">
                                    <div class="user-name" style="font-weight: 600; color: #1f2937; font-size: 15px; margin-bottom: 2px;">{{ $name ?: 'Пользователь' }}</div>
                                    <div class="user-email" style="font-size: 12px; color: #6b7280;">{{ $user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px;">
                            <span style="font-size: 13px; color: #6b7280;">
                                {{ $user->roles ? $user->roles->name : 'Не указана' }}
                            </span>
                        </td>
                        <td style="padding: 12px;">{{ $user->email ?? '—' }}</td>
                        <td style="padding: 12px;">
                            @if($isBanned)
                                <span class="ban-status banned">
                                    @if($user->isBannedPermanently())
                                        Забанен навсегда
                                    @elseif($user->banned_until)
                                        Забанен до {{ \Carbon\Carbon::parse($user->banned_until)->format('d.m.Y') }}
                                    @else
                                        Забанен
                                    @endif
                                </span>
                            @else
                                <span class="ban-status not-banned">Активен</span>
                            @endif
                        </td>
                        <td style="padding: 12px;">
                            <div class="ban-actions">
                                @if($isBanned)
                                    <form method="POST" action="{{ route('admin.bans.user.unban', $user->user_id) }}" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите разбанить этого пользователя?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Разбанить</button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-danger" onclick="openBanModal('user', {{ $user->user_id }}, '{{ $name ?: 'Пользователь' }}')">Забанить</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination')
@endif

