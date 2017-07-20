<?php

namespace Cloty\Entrust\Middleware;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Cloty\Entrust
 */

use Closure;
use Illuminate\Contracts\Auth\Guard;

class EntrustPermission
{
    const DELIMITER = '|';

    protected $auth;

    /**
     * Creates a new instance of the middleware.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @param  $permissions
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions)
    {
        $start = microtime(true);
        $start_memory = round(memory_get_usage(true) / 1024.0 / 1024.0, 2);

        if (!is_array($permissions)) {
            $permissions = explode(self::DELIMITER, $permissions);
        }

        if ($this->auth->guest() || !$request->user()->canDo($permissions)) {
            abort(403);
        }

        $end = microtime(true);
        $peek_memory = round(memory_get_peak_usage(true) / 1024.0 / 1024.0, 2);
        $grow_memory = $peek_memory - $start_memory;

        $time = ($end - $start) * 1000;

        return $next($request);
    }
}
