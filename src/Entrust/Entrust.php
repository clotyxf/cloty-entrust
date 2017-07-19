<?php

namespace Cloty\Entrust;

use Cloty\Entrust\Contracts\Entrust as EntrustContract;
use Cloty\Entrust\Contracts\Repositories\PermissionRepository;
use Cloty\Entrust\Contracts\Repositories\RoleRepository;
use Illuminate\Contracts\Foundation\Application;

/**
 * This class is the main entry point of entrust. Usually the interaction
 * with this class will be done through the Entrust Facade
 *
 * @license MIT
 * @package Zizaco\Entrust
 */

class Entrust implements EntrustContract
{
    /**
     * Laravel application
     *
     * @var \Illuminate\Foundation\Application
     */
    public $app;

    /**
     * The RoleRepository implementation.
     *
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * The PermissionRepository implementation.
     *
     * @var PermissionRepository
     */
    protected $permissionRepository;

    /**
     * Create a new confide instance.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    public function __construct(Application $app, RoleRepository $roleRepository, PermissionRepository $permissionRepository)
    {
        $this->app = $app;
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Checks if the current user has a role by its name
     *
     * @param string $name Role name.
     *
     * @return bool
     */
    public function hasRole($role, $requireAll = false)
    {
        if ($user = $this->user()) {
            return $user->hasRole($role, $requireAll);
        }

        return false;
    }

    /**
     * Check if the current user has a permission by its name
     *
     * @param string $permission Permission string.
     *
     * @return bool
     */
    public function canDo($permission, $requireAll = false)
    {
        if ($user = $this->user()) {
            return $user->canDo($permission, $requireAll);
        }

        return false;
    }

    /**
     * Check if the current user has a role or permission by its name
     *
     * @param array|string $roles            The role(s) needed.
     * @param array|string $permissions      The permission(s) needed.
     * @param array $options                 The Options.
     *
     * @return bool
     */
    public function ability($roles, $permissions, $options = [])
    {
        if ($user = $this->user()) {
            return $user->ability($roles, $permissions, $options);
        }

        return false;
    }

    /**
     * Get the currently authenticated user or null.
     *
     * @return Illuminate\Auth\UserInterface|null
     */
    public function user()
    {
        return $this->app->auth->user();
    }

    /**
     * Filters a route for a role or set of roles.
     *
     * If the third parameter is null then abort with status code 403.
     * Otherwise the $result is returned.
     *
     * @param string       $route      Route pattern. i.e: "admin/*"
     * @param array|string $roles      The role(s) needed
     * @param mixed        $result     i.e: Redirect::to('/')
     * @param bool         $requireAll User must have all roles
     *
     * @return mixed
     */
    public function routeNeedsRole($route, $roles, $result = null, $requireAll = true)
    {
        $filterName = is_array($roles) ? implode('_', $roles) : $roles;
        $filterName .= '_' . substr(md5($route), 0, 6);

        $closure = function () use ($roles, $result, $requireAll) {
            $hasRole = $this->hasRole($roles, $requireAll);

            if (!$hasRole) {
                return empty($result) ? $this->app->abort(403) : $result;
            }
        };

        // Same as Route::filter, registers a new filter
        $this->app->router->filter($filterName, $closure);

        // Same as Route::when, assigns a route pattern to the
        // previously created filter.
        $this->app->router->when($route, $filterName);
    }

    /**
     * Filters a route for a permission or set of permissions.
     *
     * If the third parameter is null then abort with status code 403.
     * Otherwise the $result is returned.
     *
     * @param string       $route       Route pattern. i.e: "admin/*"
     * @param array|string $permissions The permission(s) needed
     * @param mixed        $result      i.e: Redirect::to('/')
     * @param bool         $requireAll  User must have all permissions
     *
     * @return mixed
     */
    public function routeNeedsPermission($route, $permissions, $result = null, $requireAll = true)
    {
        $filterName = is_array($permissions) ? implode('_', $permissions) : $permissions;
        $filterName .= '_' . substr(md5($route), 0, 6);

        $closure = function () use ($permissions, $result, $requireAll) {
            $hasPerm = $this->canDo($permissions, $requireAll);

            if (!$hasPerm) {
                return empty($result) ? $this->app->abort(403) : $result;
            }
        };

        // Same as Route::filter, registers a new filter
        $this->app->router->filter($filterName, $closure);

        // Same as Route::when, assigns a route pattern to the
        // previously created filter.
        $this->app->router->when($route, $filterName);
    }

    /**
     * Filters a route for role(s) and/or permission(s).
     *
     * If the third parameter is null then abort with status code 403.
     * Otherwise the $result is returned.
     *
     * @param string       $route       Route pattern. i.e: "admin/*"
     * @param array|string $roles       The role(s) needed
     * @param array|string $permissions The permission(s) needed
     * @param mixed        $result      i.e: Redirect::to('/')
     * @param bool         $requireAll  User must have all roles and permissions
     *
     * @return void
     */
    public function routeNeedsRoleOrPermission($route, $roles, $permissions, $result = null, $requireAll = false)
    {
        $filterName = is_array($roles) ? implode('_', $roles) : $roles;
        $filterName .= '_' . (is_array($permissions) ? implode('_', $permissions) : $permissions);
        $filterName .= '_' . substr(md5($route), 0, 6);

        $closure = function () use ($roles, $permissions, $result, $requireAll) {
            $hasRole = $this->hasRole($roles, $requireAll);
            $hasPerms = $this->canDo($permissions, $requireAll);

            if ($requireAll) {
                $hasRolePerm = $hasRole && $hasPerms;
            } else {
                $hasRolePerm = $hasRole || $hasPerms;
            }

            if (!$hasRolePerm) {
                return empty($result) ? $this->app->abort(403) : $result;
            }
        };

        // Same as Route::filter, registers a new filter
        $this->app->router->filter($filterName, $closure);

        // Same as Route::when, assigns a route pattern to the
        // previously created filter.
        $this->app->router->when($route, $filterName);
    }

    /**
     * Check if a role with the given name exists.
     *
     * @param string $roleName
     *
     * @return bool
     */
    public function roleExists($roleName)
    {
        return $this->roleRepository->findByName($roleName) !== null;
    }

    /**
     * Create a new role.
     * Uses a repository to actually create the role.
     *
     * @param array $attributes
     *
     * @return \Cloty\Entrust\Role
     */
    public function createRole(array $attributes = [])
    {
        return $this->roleRepository->create($attributes);
    }

    /**
     * Get the role with the given name.
     *
     * @param string $roleName
     *
     * @return \Cloty\Entrust\Role|null
     */
    public function findRole($roleName)
    {
        return $this->roleRepository->findByName($roleName);
    }

    /**
     * * Find a role by its id.
     *
     * @param int $roleId
     *
     * @return mixed
     */
    public function findRoleById($roleId)
    {
        return $this->roleRepository->findById($roleId);
    }

    /**
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function rolesList($columns = ['*'])
    {
        return $this->roleRepository->getList($columns);
    }

    /**
     * @param array $attributes
     *
     * @return Permission
     */
    public function createPermission(array $attributes = [])
    {
        return $this->permissionRepository->create($attributes);
    }

    /**
     * Check if a permission with the given name exists.
     *
     * @param string $permissionName
     *
     * @return bool
     */
    public function permissionExists($permissionName)
    {
        return $this->permissionRepository->findByName($permissionName) !== null;
    }

    /**
     * Get the permission with the given name.
     *
     * @param string $permissionName
     *
     * @return \Cloty\Entrust\Permission|null
     */
    public function findPermission($permissionName)
    {
        return $this->permissionRepository->findByName($permissionName);
    }

    /**
     * Find a permission by its id.
     *
     * @param int $permissionId
     *
     * @return \Cloty\Entrust\Permission|null
     */
    public function findPermissionById($permissionId)
    {
        return $this->permissionRepository->findById($permissionId);
    }

    /**
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function permissionsList($columns = ['*'])
    {
        return $this->permissionRepository->getList($columns);
    }
}
