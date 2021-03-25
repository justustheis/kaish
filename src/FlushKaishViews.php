<?php

namespace JustusTheis\Kaish;

use Cache;

class FlushKaishViews
{
    /**
     * This middleware flushes the kaish on every request it is loaded.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(\Illuminate\Http\Request $request, \Closure $next)
    {
        Cache::tags('views')->flush();

        return $next($request);
    }
}
