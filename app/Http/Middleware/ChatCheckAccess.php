<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatCheckAccess
{

    public function handle(Request $request, Closure $next): Response
    {
        if(!auth()->check()){
            return redirect()->intended(route('map'));
        }


        $id = $request->route("id");
        $user1_id = Chat::where("chat_id",$id)->value("user_id");
        $user2_id = Chat::where("chat_id",$id)->value("rent_dealer_id");
        if ($request->user()->user_id != $user1_id || $request->user()->user_id != $user2_id) {
            
            return redirect()->intended(route('dashboard'));
        }

        return $next($request);
    }
}
