<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user) {
            if ($user->banned_until) {
                $banDate = $user->banned_until instanceof \Carbon\Carbon 
                    ? $user->banned_until->setTimezone('Europe/Moscow')
                    : \Carbon\Carbon::parse($user->banned_until, 'Europe/Moscow');
                
                if ($banDate->isPast()) {
                    $user->unban();
                    return $next($request);
                }
            }

            if ($user->isBanned()) {
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

