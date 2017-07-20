<?php

namespace Cloty\Entrust\Contracts;

interface Entrust
{
    /**
     * Checks if the current user has a role by its name
     *
     * @param string $name Role name.
     *
     * @return bool
     */
    public function hasRole($role, $requireAll = false);

    /**
     * Check if the current user has a permission by its name
     *
     * @param string $permission Permission string.
     *
     * @return bool
     */
    public function canDo($permission, $requireAll = false);

    /**
     * Check if the current user has a role or permission by its name
     *
     * @param array|string $roles            The role(s) needed.
     * @param array|string $permissions      The permission(s) needed.
     * @param array $options                 The Options.
     *
     * @return bool
     */
    public function ability($roles, $permissions, $options = []);

    /**
     * Get the currently authenticated user or null.
     *
     * @return Illuminate\Auth\UserInterface|null
     */
    public function user();
}
