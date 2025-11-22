<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
class HouseOwnerCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!auth()->check()){
            return redirect()->intended(route('map'));
        }

        $id = $request->route("id");
        $house_owner_id = Houses::where("user_id",$id)->value("user_id");
        if ($request->user()->user_id != $house_owner_id) {
            
            return redirect()->intended(route('map'));
        }
        
        return $next($request);
    }
}
