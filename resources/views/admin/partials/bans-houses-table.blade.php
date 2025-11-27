@if ($houses->isEmpty())
    <div class="empty-state">
        <p>Нет домов для отображения.</p>
    </div>
@else
    <div style="overflow-x: auto;">
        <table class="verification-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: #f9fafb;">
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">ID</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">Дом</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">Владелец</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">Адрес</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">Статус</th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e5e5;">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($houses as $house)
                    @php
                        $isBanned = $house->isBanned();
                        $isDeleted = $house->is_deleted ?? false;
                        $firstPhoto = $house->photo->first();
                    @endphp
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px;"><strong>#{{ $house->house_id }}</strong></td>
                        <td style="padding: 12px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                @if($firstPhoto)
                                    <img src="{{ asset('storage/' . $firstPhoto->path) }}" alt="Дом" class="house-image">
                                @else
                                    <div style="width: 60px; height: 60px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #9ca3af;">Нет фото</div>
                                @endif
                                <div>
                                    <div style="font-weight: 600; color: #1f2937;">Дом #{{ $house->house_id }}</div>
                                    <div style="font-size: 12px; color: #6b7280;">{{ \Illuminate\Support\Str::limit($house->adress, 40) }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px;">
                            @if($house->user)
                                <div style="font-size: 13px; color: #6b7280;">
                                    {{ trim(($house->user->name ?? '') . ' ' . ($house->user->sename ?? '')) ?: 'Пользователь #' . $house->user->user_id }}
                                </div>
                            @else
                                <span style="color: #9ca3af;">—</span>
                            @endif
                        </td>
                        <td style="padding: 12px;">{{ \Illuminate\Support\Str::limit($house->adress, 30) }}</td>
                        <td style="padding: 12px;">
                            @if($isDeleted)
                                <span class="ban-status deleted">Удален</span>
                            @elseif($isBanned)
                                <span class="ban-status banned">
                                    @if($house->is_banned_permanently)
                                        Забанен навсегда
                                    @elseif($house->banned_until)
                                        Забанен до {{ \Carbon\Carbon::parse($house->banned_until)->format('d.m.Y') }}
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
                                @if($isDeleted)
                                    <form method="POST" action="{{ route('admin.bans.house.restore', $house->house_id) }}" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите восстановить этот дом?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Восстановить</button>
                                    </form>
                                @elseif($isBanned)
                                    <form method="POST" action="{{ route('admin.bans.house.unban', $house->house_id) }}" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите разбанить этот дом?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Разбанить</button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-danger" onclick="openBanModal('house', {{ $house->house_id }}, 'Дом #{{ $house->house_id }}')">Забанить</button>
                                @endif
                                @if(!$isDeleted)
                                    <form method="POST" action="{{ route('admin.bans.house.delete', $house->house_id) }}" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить этот дом?');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Удалить</button>
                                    </form>
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

