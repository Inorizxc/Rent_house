<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Model\User;
class UserCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        if(!auth()->check()){
            return redirect()->intended(route('map'));
        }

        
        if ($request->user()->user_id != $request->route('id')) {
            
            return redirect()->intended(route('map'));
        }

        return $next($request);
    }
}
