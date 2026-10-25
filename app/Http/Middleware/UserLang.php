<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class UserLang
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {

    if ($request->header('lang') || $request->has('lang')) {
      $lang = $request->has('lang') ? $request->lang : $request->header('lang');
      $supportedLangs = config('backend.languageCodes');
      if (!in_array($lang, $supportedLangs)) {
        $lang = 'en';
      }
      \App::setLocale($lang);

    } elseif ($request->hasSession() && $request->session()->has('lan')) {
      \App::setLocale($request->session()->get('lan'));
    } else {
      \App::setLocale(config('app.locale', 'en'));
    }

    if ($request->hasSession()) {
      $request->session()->put('lan', \App::getLocale());
    }
    return $next($request);
  }
}
