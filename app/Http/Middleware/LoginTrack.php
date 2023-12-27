<?php

namespace App\Http\Middleware;

use App\Models\LoginActivity;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginTrack
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //DB::enableQueryLog();
        LoginActivity::where([
            ['user_id', '=', Auth::user()->id],
            ['session_id', '=', session()->getId()],
        ])
            ->whereNull('logged_out_at')
            ->update(['last_activity_time' => Carbon::now()]);
        //dd(DB::getQueryLog());die;

        return $next($request);
    }
}
