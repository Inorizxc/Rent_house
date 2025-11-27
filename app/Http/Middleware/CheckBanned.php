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
        if ($user && $user->isBanned()) {
            // Для POST/PUT/DELETE запросов блокируем доступ
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                $banUntil = $user->getBanUntilDate();
                $message = $user->is_banned_permanently 
                    ? 'Ваш аккаунт заблокирован навсегда. Вы не можете выполнять действия на сайте.'
                    : "Ваш аккаунт заблокирован до {$banUntil->format('d.m.Y H:i')}. Вы не можете выполнять действия на сайте до этой даты.";
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $message
                    ], 403);
                }
                
                return back()->with('error', $message)->withInput();
            }
        }
        
        return $next($request);
    }
}

