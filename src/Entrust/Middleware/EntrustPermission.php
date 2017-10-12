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
use Illuminate\Http\JsonResponse;

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

            $isAjax = $request->ajax();

            if ($isAjax) {
                return new JsonResponse(['status' => 0, 'msg' => '权限不被允许']);
            }

            if (config('entrust.cfc') == 1) {
                abort(403);
            }

            return back()->with('error', '权限不被允许');
        }

        return $next($request);
    }
}
