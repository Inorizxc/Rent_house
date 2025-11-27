<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Не блокируем доступ к страницам, но пользователь увидит баннер
        // Блокируем только действия (POST, PUT, DELETE запросы)
        if ($user) {
            // Проверяем и автоматически разбаниваем, если срок истек
            // Метод isBanned() уже делает это автоматически, но проверяем явно для надежности
            if ($user->banned_until) {
                $banDate = $user->banned_until instanceof \Carbon\Carbon 
                    ? $user->banned_until->setTimezone('Europe/Moscow')
                    : \Carbon\Carbon::parse($user->banned_until, 'Europe/Moscow');
                
                if ($banDate->isPast()) {
                    $user->unban();
                    // После разбана продолжаем выполнение запроса
                    return $next($request);
                }
            }
            
            // Проверяем, забанен ли пользователь (после проверки истекших банов)
            if ($user->isBanned()) {
                // Для POST/PUT/DELETE запросов блокируем доступ
                if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                    $banUntil = $user->getBanUntilDate();
                    $banReason = $user->ban_reason ? "\n\nПричина: {$user->ban_reason}" : '';
                    $message = $user->isBannedPermanently() 
                        ? 'Ваш аккаунт заблокирован навсегда. Вы не можете выполнять действия на сайте.' . $banReason
                        : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете выполнять действия на сайте до этой даты." . $banReason;
                    
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message
                    ], 403);
                    }
                    
                    return back()->with('error', $message)->withInput();
                }
            }
        }
        
        return $next($request);
    }
}

