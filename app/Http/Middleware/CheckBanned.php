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
            if ($user->isBanned()) {
                
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
                return back();
                
            }
        }
        
        return $next($request);
    }
}

